<?php

namespace App\Http\Controllers\Api;

use App\Services\DadaRewardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RewardController extends Controller
{
    protected DadaRewardService $rewardService;

    public function __construct(DadaRewardService $rewardService)
    {
        $this->rewardService = $rewardService;
        $this->middleware('auth:web,api')->except(['health']);
    }

    /**
     * Health check for reward system.
     *
     * GET /api/v1/rewards/health
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'DADA AI Reward',
            'reward_per_second' => config('minima.reward.per_second', 10),
            'min_watch_seconds' => config('minima.reward.min_watch_seconds', 30),
            'daily_limit' => config('minima.reward.daily_max', 1000),
        ]);
    }

    /**
     * Start a watch session.
     *
     * POST /api/v1/rewards/watch/start
     */
    public function watchStart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video_id' => 'required|string',
            'video_title' => 'nullable|string|max:255',
        ]);

        $profileId = $request->user()->profile_id ?? $request->user()->id;

        $result = $this->rewardService->startWatchSession(
            $profileId,
            $validated['video_id'],
            $validated['video_title'] ?? ''
        );

        return response()->json($result);
    }

    /**
     * Update a watch session.
     *
     * POST /api/v1/rewards/watch/update
     */
    public function watchUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => 'required|string',
            'watched_seconds' => 'required|integer|min:0',
        ]);

        $result = $this->rewardService->updateWatchSession(
            $validated['session_key'],
            $validated['watched_seconds']
        );

        if (!$result) {
            return response()->json(['message' => 'Session not found or already ended'], 404);
        }

        return response()->json($result);
    }

    /**
     * End a watch session and claim reward.
     *
     * POST /api/v1/rewards/watch/end
     */
    public function watchEnd(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => 'required|string',
            'wallet_address' => 'nullable|string', // Mx... Minima address
        ]);

        $result = $this->rewardService->endWatchSession(
            $validated['session_key'],
            $validated['wallet_address'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Get reward history for the authenticated user.
     *
     * GET /api/v1/rewards/history
     */
    public function history(Request $request): JsonResponse
    {
        $profileId = $request->user()->profile_id ?? $request->user()->id;
        $limit = (int) $request->input('limit', 20);

        $result = $this->rewardService->getRewardHistory($profileId, $limit);

        return response()->json($result);
    }

    /**
     * Get current reward status for authenticated user.
     *
     * GET /api/v1/rewards/status
     */
    public function status(Request $request): JsonResponse
    {
        $profileId = $request->user()->profile_id ?? $request->user()->id;

        $dailyEarned = $this->rewardService->getDailyEarned($profileId);
        $dailyLimit = config('minima.reward.daily_max', 1000);

        return response()->json([
            'today_earned' => $dailyEarned,
            'daily_limit' => $dailyLimit,
            'daily_remaining' => max(0, $dailyLimit - $dailyEarned),
            'reward_per_second' => config('minima.reward.per_second', 10),
            'min_watch_seconds' => config('minima.reward.min_watch_seconds', 30),
        ]);
    }
}
