<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\MinimaRpcService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MinimaAuthController extends Controller
{
    protected MinimaRpcService $minima;

    public function __construct(MinimaRpcService $minima)
    {
        $this->minima = $minima;
    }

    /**
     * Generate a challenge string for Minima wallet signing.
     *
     * POST /api/auth/minima/challenge
     */
    public function challenge(Request $request): JsonResponse
    {
        $challenge = Str::random(64);
        $cacheKey = 'minima_challenge_' . md5($challenge);

        // Store challenge with 5-minute TTL
        Cache::put($cacheKey, $challenge, now()->addMinutes(5));

        return response()->json([
            'challenge' => $challenge,
        ]);
    }

    /**
     * Authenticate a user via Minima wallet signature.
     *
     * POST /api/auth/minima
     */
    public function authenticate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'address' => 'required|string|regex:/^Mx[a-fA-F0-9]{60,}$/',
                'signature' => 'required|string',
                'challenge' => 'required|string|size:64',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $address = $validated['address'];
        $signature = $validated['signature'];
        $challenge = $validated['challenge'];

        // Verify challenge exists in cache (prevent replay)
        $cacheKey = 'minima_challenge_' . md5($challenge);
        $storedChallenge = Cache::pull($cacheKey);

        if (!$storedChallenge || $storedChallenge !== $challenge) {
            return response()->json([
                'message' => 'Invalid or expired challenge',
            ], 422);
        }

        // Verify signature via Minima RPC
        $valid = $this->minima->verifySignature($address, $challenge, $signature);

        if (!$valid) {
            return response()->json([
                'message' => 'Signature verification failed',
            ], 401);
        }

        // Find or create user by Minima address
        $user = User::where('minima_address', $address)->first();

        if (!$user) {
            // Auto-register a lightweight account
            $user = User::create([
                'username' => 'minima_' . substr($address, 2, 12),
                'name' => 'Minima User',
                'minima_address' => $address,
                'minima_address_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
            ]);
        } else {
            $user->update([
                'minima_address_verified_at' => now(),
            ]);
        }

        // Log the user in via session
        Auth::guard('web')->login($user);

        // Generate a Passport token for API access
        $tokenResult = $user->createToken('Minima Auth');
        $token = $tokenResult->accessToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
            ],
            'message' => 'Authentication successful',
        ]);
    }
}
