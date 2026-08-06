import { defineStore } from 'pinia'
import { ref, computed, nextTick } from 'vue'
import axios from '~/plugins/axios'

export const useExploreStore = defineStore('explore', () => {
    const hashtags = ref([])
    const videos = ref([])
    const activeHashtag = ref(null)
    const totalResults = ref(null)
    const loading = ref(false)
    const loadingMore = ref(false)
    const error = ref(null)
    const hasMore = ref(true)
    const cursor = ref(null)
    // 'all' shows latest videos; 'tag' shows hashtag-filtered videos
    const mode = ref(null)

    const currentVideos = computed(() => {
        return videos.value
    })

    const fetchLatest = async () => {
        try {
            loading.value = true
            error.value = null

            const axiosInstance = axios.getAxiosInstance()
            const res = await axiosInstance.get('/api/v1/explore/latest')

            videos.value = res.data.data.filter((v) => v.id && v.account)

            cursor.value = res.data.meta?.next_cursor
            hasMore.value = res.data.meta?.next_cursor != undefined
        } catch (err) {
            error.value = 'Failed to fetch videos'
            console.error('Error fetching latest videos:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchHashtags = async () => {
        try {
            loading.value = true
            error.value = null

            const axiosInstance = axios.getAxiosInstance()
            const res = await axiosInstance.get('/api/v1/explore/tags')

            hashtags.value = res.data.data

            // Always default to 'all' mode to show latest videos
            await setMode('all')
        } catch (err) {
            error.value = 'Failed to fetch hashtags'
            console.error('Error fetching hashtags:', err)
            // Still try to load latest even if hashtags fail
            await fetchLatest()
        } finally {
            loading.value = false
        }
    }

    const fetchVideosByHashtag = async (hashtagName) => {
        try {
            loading.value = true
            error.value = null

            const axiosInstance = axios.getAxiosInstance()
            const res = await axiosInstance.get(`/api/v1/explore/tag-feed/${hashtagName}`)

            videos.value = res.data.data.filter((v) => v.id && v.account)

            cursor.value = res.data.meta?.next_cursor
            hasMore.value = res.data.meta?.next_cursor != undefined
        } catch (err) {
            error.value = 'Failed to fetch videos'
            console.error('Error fetching videos:', err)
        } finally {
            loading.value = false
        }
    }

    const setMode = async (newMode) => {
        if (mode.value === newMode && cursor.value === null) return

        mode.value = newMode
        activeHashtag.value = null
        totalResults.value = null
        loadingMore.value = false
        cursor.value = null
        hasMore.value = true
        videos.value = []

        if (newMode === 'all') {
            await fetchLatest()
        }
        await nextTick()
    }

    const setActiveHashtag = async (hashtag) => {
        if (activeHashtag.value?.id === hashtag.id) return

        mode.value = 'tag'
        activeHashtag.value = hashtag
        totalResults.value = hashtag.count
        loadingMore.value = false
        cursor.value = null
        hasMore.value = true
        await fetchVideosByHashtag(hashtag.name)
        await nextTick()
    }

    const loadMore = async () => {
        if (!hasMore.value || loadingMore.value) {
            return
        }

        try {
            loadingMore.value = true

            const axiosInstance = axios.getAxiosInstance()
            let url
            const params = {
                cursor: cursor.value
            }

            if (mode.value === 'all') {
                url = '/api/v1/explore/latest'
            } else if (activeHashtag.value) {
                url = `/api/v1/explore/tag-feed/${activeHashtag.value.name}`
            } else {
                loadingMore.value = false
                return
            }

            const res = await axiosInstance.get(url, { params })

            videos.value = [...videos.value, ...res.data.data.filter((v) => v.id && v.account)]

            cursor.value = res.data.meta?.next_cursor
            hasMore.value = res.data.meta?.next_cursor != undefined
        } catch (err) {
            console.error('Error loading more videos:', err)
        } finally {
            loadingMore.value = false
        }
    }

    return {
        hashtags,
        videos,
        activeHashtag,
        totalResults,
        loading,
        loadingMore,
        error,
        hasMore,
        cursor,
        mode,
        currentVideos,
        fetchLatest,
        fetchHashtags,
        fetchVideosByHashtag,
        setActiveHashtag,
        setMode,
        loadMore
    }
})
