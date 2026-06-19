import { ref, onUnmounted } from 'vue'
import axios from '~/plugins/axios'

/**
 * Video watch reward composable.
 * Tracks video watch time and claims DADA AI token rewards via Minima blockchain.
 */
export const useVideoRewards = () => {
    const sessionKey = ref(null)
    const accumulatedSeconds = ref(0)
    const earnedTokens = ref(0)
    const isTracking = ref(false)
    const dailyRemaining = ref(0)
    const dailyLimit = ref(1000)
    const dailyEarned = ref(0)
    let watchStartTime = 0
    let updateInterval = null

    const axiosInstance = axios.getAxiosInstance()

    /**
     * Start watch tracking for a video.
     */
    const startWatching = async (videoId, videoTitle = '') => {
        if (!videoId) return

        try {
            const { data } = await axiosInstance.post('/api/v1/rewards/watch/start', {
                video_id: videoId,
                video_title: videoTitle
            })

            if (data.session_key) {
                sessionKey.value = data.session_key
                watchStartTime = Date.now()
                accumulatedSeconds.value = 0
                earnedTokens.value = 0
                isTracking.value = true

                // Start periodic updates (every 15 seconds)
                startPeriodicUpdates()
            }
        } catch (error) {
            // Silent fail — rewards are optional
            if (error.response?.status !== 401) {
                console.debug('Reward tracking start skipped:', error.message)
            }
        }
    }

    /**
     * Update watch progress periodically.
     */
    const startPeriodicUpdates = () => {
        if (updateInterval) clearInterval(updateInterval)

        updateInterval = setInterval(async () => {
            if (!sessionKey.value || !isTracking.value) return

            const elapsed = Math.floor((Date.now() - watchStartTime) / 1000)
            accumulatedSeconds.value = elapsed

            try {
                const { data } = await axiosInstance.post('/api/v1/rewards/watch/update', {
                    session_key: sessionKey.value,
                    watched_seconds: elapsed
                })

                if (data.earned_tokens !== undefined) {
                    earnedTokens.value = data.earned_tokens
                }
            } catch (error) {
                // Silent
            }
        }, 15000)
    }

    /**
     * End watch session and claim reward.
     */
    const stopWatching = async (walletAddress = null) => {
        if (!sessionKey.value) return null

        isTracking.value = false
        if (updateInterval) {
            clearInterval(updateInterval)
            updateInterval = null
        }

        try {
            const { data } = await axiosInstance.post('/api/v1/rewards/watch/end', {
                session_key: sessionKey.value,
                wallet_address: walletAddress
            })

            if (data.reward !== undefined) {
                earnedTokens.value = data.reward
            }

            sessionKey.value = null
            return data
        } catch (error) {
            sessionKey.value = null
            return null
        }
    }

    /**
     * Get reward status for the current user.
     */
    const fetchRewardStatus = async () => {
        try {
            const { data } = await axiosInstance.get('/api/v1/rewards/status')
            dailyEarned.value = data.today_earned || 0
            dailyLimit.value = data.daily_limit || 1000
            dailyRemaining.value = data.daily_remaining || 0
        } catch (error) {
            // Silent
        }
    }

    // Cleanup on unmount
    onUnmounted(() => {
        if (updateInterval) {
            clearInterval(updateInterval)
        }
    })

    return {
        sessionKey,
        accumulatedSeconds,
        earnedTokens,
        isTracking,
        dailyRemaining,
        dailyLimit,
        dailyEarned,
        startWatching,
        stopWatching,
        fetchRewardStatus
    }
}
