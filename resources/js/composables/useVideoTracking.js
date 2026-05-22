import axios from '~/plugins/axios'

export const useVideoTracking = () => {
    const recordImpression = async (videoId, watchDuration, completed) => {
        if (watchDuration <= 0) {
            return { success: false }
        }
        const axiosInstance = axios.getAxiosInstance()

        try {
            const res = await axiosInstance.post('/api/v0/feed/recommended/impression', {
                video_id: videoId,
                watch_duration: Math.floor(watchDuration),
                completed
            })
            return res.data
        } catch (error) {
            console.error('Failed to record impression:', error)
            return { success: false }
        }
    }

    const recordFeedback = async (videoId, feedbackType) => {
        const axiosInstance = axios.getAxiosInstance()

        try {
            await axiosInstance.post('/api/v0/feed/recommended/feedback', {
                video_id: videoId,
                feedback_type: feedbackType
            })
        } catch (error) {
            console.error('Failed to record feedback:', error)
        }
    }

    return {
        recordImpression,
        recordFeedback
    }
}
