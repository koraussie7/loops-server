<?php

namespace App\Services;

use App\Models\DadaReward;
use App\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DadaRewardService
{
    protected MinimaRpcService $minima;
    protected int $rewardPerSecond;
    protected int $minWatchSeconds;
    protected int $maxPerSession;
    protected int $dailyMax;

    public function __construct(MinimaRpcService $minima)
    {
        $this->minima = $minima;
        $this->rewardPerSecond = config('minima.reward.per_second', 10);
        $this->minWatchSeconds = config('minima.reward.min_watch_seconds', 30);
        $this->maxPerSession = config('minima.reward.max_per_session', 100000);
        $this->dailyMax = config('minima.reward.daily_max', 1000);
    }

    /**
     * Calculate the reward amount for a watched duration.
     */
    public function calculateReward(int $watchedSeconds): int
    {
        if ($watchedSeconds < $this->minWatchSeconds) {
            return 0;
        }

        return min(
            $watchedSeconds * $this->rewardPerSecond,
            $this->maxPerSession
        );
    }

    /**
     * Check if user has reached daily reward limit.
     */
    public function hasReachedDailyLimit(int $profileId): bool
    {
        $today = now()->startOfDay();
        $total = DadaReward::where('profile_id', $profileId)
            ->where('created_at', '>=', $today)
            ->sum('reward_amount');

        return $total >= $this->dailyMax;
    }

    /**
     * Get today's earned rewards for a user.
     */
    public function getDailyEarned(int $profileId): int
    {
        $today = now()->startOfDay();
        return (int) DadaReward::where('profile_id', $profileId)
            ->where('created_at', '>=', $today)
            ->sum('reward_amount');
    }

    /**
     * Start a watch session (server-side tracking).
     */
    public function startWatchSession(int $profileId, string $videoId, string $videoTitle = ''): array
    {
        $sessionKey = "{$profileId}:{$videoId}:" . time();

        // Store in cache/session (60min TTL)
        $sessionData = [
            'profile_id' => $profileId,
            'video_id' => $videoId,
            'video_title' => $videoTitle,
            'start_time' => now()->timestamp,
            'last_update' => now()->timestamp,
            'accumulated_seconds' => 0,
            'status' => 'watching',
        ];

        cache()->put("dada_watch_{$sessionKey}", $sessionData, now()->addMinutes(60));

        return [
            'session_key' => $sessionKey,
            'message' => "Watch session started for video {$videoId}",
        ];
    }

    /**
     * Update a watch session with current watched seconds.
     */
    public function updateWatchSession(string $sessionKey, int $watchedSeconds): ?array
    {
        $session = cache()->get("dada_watch_{$sessionKey}");

        if (!$session) {
            return null;
        }

        if ($session['status'] !== 'watching') {
            return null;
        }

        $session['accumulated_seconds'] = max($session['accumulated_seconds'], $watchedSeconds);
        $session['last_update'] = now()->timestamp;

        cache()->put("dada_watch_{$sessionKey}", $session, now()->addMinutes(60));

        return [
            'accumulated_seconds' => $session['accumulated_seconds'],
            'earned_tokens' => $this->calculateReward($session['accumulated_seconds']),
        ];
    }

    /**
     * End a watch session and process payment.
     */
    public function endWatchSession(string $sessionKey, string $walletAddress = null): array
    {
        $session = cache()->get("dada_watch_{$sessionKey}");

        if (!$session) {
            return [
                'status' => 'error',
                'message' => 'Session not found',
            ];
        }

        if ($session['status'] !== 'watching') {
            return [
                'status' => 'ok',
                'reward' => 0,
                'message' => 'Session already ended',
                'watched_seconds' => $session['accumulated_seconds'],
            ];
        }

        $watchedSeconds = $session['accumulated_seconds'];
        $session['status'] = 'ended';
        cache()->put("dada_watch_{$sessionKey}", $session, now()->addMinutes(60));

        // Calculate reward
        $rewardAmount = $this->calculateReward($watchedSeconds);

        if ($rewardAmount <= 0) {
            return [
                'status' => 'ok',
                'reward' => 0,
                'message' => "Minimum watch time ({$this->minWatchSeconds}s) not met, no reward",
                'watched_seconds' => $watchedSeconds,
            ];
        }

        // Check daily limit
        if ($this->hasReachedDailyLimit($session['profile_id'])) {
            return [
                'status' => 'ok',
                'reward' => 0,
                'message' => 'Daily reward limit reached',
                'watched_seconds' => $watchedSeconds,
            ];
        }

        $txpowid = 'pending';

        // Send DADA tokens if wallet address is provided
        if ($walletAddress) {
            $sendResult = $this->minima->sendDada($walletAddress, $rewardAmount);

            if ($sendResult && ($sendResult['status'] ?? false)) {
                $txpowid = $sendResult['response']['txpowid'] ?? 'pending';
            } else {
                Log::warning('DADA reward send failed', [
                    'profile_id' => $session['profile_id'],
                    'amount' => $rewardAmount,
                    'address' => $walletAddress,
                    'error' => $sendResult['error'] ?? 'unknown',
                ]);
            }
        }

        // Record in database
        try {
            DadaReward::create([
                'profile_id' => $session['profile_id'],
                'video_id' => $session['video_id'],
                'video_title' => $session['video_title'] ?? '',
                'watched_seconds' => $watchedSeconds,
                'reward_amount' => $rewardAmount,
                'wallet_address' => $walletAddress,
                'txpowid' => $txpowid,
                'status' => $walletAddress ? 'paid' : 'recorded',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record DADA reward', [
                'error' => $e->getMessage(),
                'session' => $session,
            ]);
        }

        return [
            'status' => 'ok',
            'reward' => $rewardAmount,
            'token' => 'DADA AI',
            'watched_seconds' => $watchedSeconds,
            'wallet_address' => $walletAddress,
            'txpowid' => $txpowid,
        ];
    }

    /**
     * Get user's reward history.
     */
    public function getRewardHistory(int $profileId, int $limit = 20): array
    {
        $rewards = DadaReward::where('profile_id', $profileId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        $totalEarned = DadaReward::where('profile_id', $profileId)->sum('reward_amount');
        $todayEarned = $this->getDailyEarned($profileId);

        return [
            'rewards' => $rewards,
            'total_earned' => (int) $totalEarned,
            'today_earned' => $todayEarned,
            'daily_limit' => $this->dailyMax,
        ];
    }
}
