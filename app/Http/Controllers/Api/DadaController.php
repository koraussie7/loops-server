<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DadaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get authenticated user's DADA balance and stats.
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalEarned = DB::table('dada_rewards')
            ->where('user_id', $user->id)
            ->sum('amount');

        $todayEarned = DB::table('dada_rewards')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('amount');

        $totalTxs = DB::table('dada_rewards')
            ->where('user_id', $user->id)
            ->count();

        $minimaAddress = DB::table('user_minima_addresses')
            ->where('user_id', $user->id)
            ->value('miniaddress');

        return response()->json([
            'balance' => (int) $totalEarned,
            'today_earned' => (int) $todayEarned,
            'total_transactions' => $totalTxs,
            'minima_address' => $minimaAddress ?? null,
            'daily_max' => 1000,
        ]);
    }

    /**
     * Get DADA reward history for the user.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $rewards = DB::table('dada_rewards')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $rewards,
        ]);
    }
}
