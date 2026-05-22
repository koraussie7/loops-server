<?php

namespace App\Services;

use App\Models\User;
use App\Models\Video;
use App\Models\UserVideoView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DADA Reward Service — handles watch-to-earn logic.
 * 
 * Flow:
 * 1. User watches video → recordImpression called
 * 2. This service checks watch time, calculates DADA reward
 * 3. Sends DADA_AI tokens via Minima blockchain
 * 4. Records transaction in database
 */
class DadaRewardService
{
    protected MinimaRpcService $minima;

    // Reward rates
    const REWARD_PER_SECOND = 1;       // 1 DADA per second watched
    const BONUS_COMPLETION = 10;       // Bonus for watching full video
    const DAILY_MAX_PER_USER = 1000;   // Max DADA per user per day
    const MIN_WATCH_SECONDS = 10;      // Minimum watch time to earn

    public function __construct()
    {
        $this->minima = app(MinimaRpcService::class);
    }

    /**
     * Process reward for a video view.
     * Called after watch_duration is recorded.
     */
    public function processViewReward(User $user, Video $video, int $watchDuration, bool $completed): array
    {
        $result = [
            'earned' => 0,
            'sent' => false,
            'tx_id' => null,
            'reason' => '',
        ];

        // 1. Minimum watch time check
        if ($watchDuration < self::MIN_WATCH_SECONDS) {
            $result['reason'] = 'Minimum watch time not met';
            return $result;
        }

        // 2. Check daily limit
        $dailyEarned = $this->getDailyEarnings($user->id);
        $remaining = self::DAILY_MAX_PER_USER - $dailyEarned;
        if ($remaining <= 0) {
            $result['reason'] = "Daily limit reached ({$dailyEarned}/" . self::DAILY_MAX_PER_USER . ")";
            return $result;
        }

        // 3. Calculate reward
        $earned = $watchDuration * self::REWARD_PER_SECOND;
        if ($completed) {
            $earned += self::BONUS_COMPLETION;
        }

        // Cap to remaining daily limit
        $earned = min($earned, $remaining);

        if ($earned <= 0) {
            $result['reason'] = 'No DADA to reward';
            return $result;
        }

        // 4. Check DADA balance in Minima wallet
        $dadaBalance = $this->minima->getDadaBalance();
        if ($dadaBalance < $earned) {
            $result['reason'] = "Insufficient DADA balance ($dadaBalance < $earned)";
            Log::warning("DadaReward: Insufficient balance", [
                'video_id' => $video->id,
                'user_id' => $user->id,
                'needed' => $earned,
                'balance' => $dadaBalance,
            ]);
            return $result;
        }

        // 5. Get user's Minima address
        $userAddress = $this->getUserMinimaAddress($user);
        if (!$userAddress) {
            $result['reason'] = 'User has no Minima address';
            return $result;
        }

        // 6. Send tokens via Minima
        try {
            $txResult = $this->minima->sendDada($userAddress, $earned);
            if ($txResult && ($txResult['status'] ?? false)) {
                $txId = $txResult['response']['txpow']['txpowid'] ?? 'unknown';
                
                // 7. Record in database
                $this->recordReward($user->id, $video->id, $earned, $txId, $watchDuration, $completed);
                
                $result['earned'] = $earned;
                $result['sent'] = true;
                $result['tx_id'] = $txId;
                $result['reason'] = 'Success';
            } else {
                $errorMsg = $txResult['error'] ?? 'Unknown Minima error';
                $result['reason'] = "Blockchain send failed: {$errorMsg}";
                Log::error("DadaReward: Send failed", ['error' => $errorMsg, 'result' => $txResult]);
            }
        } catch (\Exception $e) {
            $result['reason'] = "Exception: {$e->getMessage()}";
            Log::error("DadaReward: Exception", ['error' => $e->getMessage()]);
        }

        return $result;
    }

    /**
     * Get total DADA earned by a user today.
     */
    public function getDailyEarnings(int $userId): int
    {
        $today = now()->startOfDay();
        return DB::table('dada_rewards')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $today)
            ->sum('amount');
    }

    /**
     * Get user's Minima blockchain address.
     * Falls back to profile metadata or creates one.
     */
    protected function getUserMinimaAddress(User $user): ?string
    {
        // Check if user already has a Minima address stored
        $address = DB::table('user_minima_addresses')
            ->where('user_id', $user->id)
            ->value('address');

        if ($address) {
            return $address;
        }

        // Create a new address for the user via Minima
        try {
            $result = $this->minima->newAddress();
            if ($result && isset($result['response']['address'])) {
                $address = $result['response']['address'];
                DB::table('user_minima_addresses')->insert([
                    'user_id' => $user->id,
                    'address' => $address,
                    'miniaddress' => $result['response']['miniaddress'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return $address;
            }
        } catch (\Exception $e) {
            Log::error("DadaReward: Failed to create Minima address", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Record reward transaction in database.
     */
    protected function recordReward(int $userId, int $videoId, int $amount, string $txId, int $watchSeconds, bool $completed): void
    {
        DB::table('dada_rewards')->insert([
            'user_id' => $userId,
            'video_id' => $videoId,
            'amount' => $amount,
            'tx_id' => $txId,
            'watch_seconds' => $watchSeconds,
            'completed' => $completed,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get total reward stats for a user.
     */
    public function getUserStats(int $userId): array
    {
        $total = DB::table('dada_rewards')
            ->where('user_id', $userId)
            ->selectRaw('SUM(amount) as total, COUNT(*) as tx_count')
            ->first();

        $today = DB::table('dada_rewards')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('amount');

        return [
            'total_earned' => (int) ($total->total ?? 0),
            'total_transactions' => (int) ($total->tx_count ?? 0),
            'today_earned' => (int) $today,
            'daily_max' => self::DAILY_MAX_PER_USER,
        ];
    }
}
