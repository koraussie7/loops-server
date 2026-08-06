<template>
  <div class="dtube-content">
    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Trending</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Popular DTube videos on the Hive blockchain
      </p>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <svg class="animate-spin w-8 h-8 text-dtube-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
      </svg>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="text-center py-20">
      <p class="text-gray-500 dark:text-gray-400">{{ error }}</p>
      <button
        @click="loadVideos"
        class="mt-4 px-4 py-2 bg-dtube-accent text-white rounded-lg hover:bg-red-700 transition-colors"
      >
        Retry
      </button>
    </div>

    <!-- Category tabs -->
    <div v-else>
      <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
        <button
          v-for="cat in categories"
          :key="cat.key"
          @click="selectCategory(cat.key)"
          :class="[
            'px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
            activeCategory === cat.key
              ? 'bg-dtube-accent text-white'
              : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
          ]"
        >
          {{ cat.label }}
        </button>
      </div>

      <!-- Video grid -->
      <div v-if="videos.length === 0" class="text-center py-20 text-gray-400">
        <p>No videos found in this category.</p>
      </div>
      <div v-else class="space-y-2">
        <DTubeVideoCard v-for="video in videos" :key="video.id" :video="video" />
      </div>

      <!-- Load more -->
      <div v-if="hasMore" class="text-center py-8">
        <button
          @click="loadMore"
          :disabled="loadingMore"
          class="px-6 py-2 bg-dtube-accent text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
        >
          {{ loadingMore ? "Loading..." : "Load more" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useDTubeFeed } from "@/composables/useDTubeFeed"
import DTubeVideoCard from "@/components/dtube/DTubeVideoCard.vue"

function timeAgo(dateStr) {
  if (!dateStr) return ""
  const now = new Date()
  const d = new Date(dateStr + "Z")
  const sec = Math.floor((now - d) / 1000)
  if (sec < 60) return "just now"
  const min = Math.floor(sec / 60)
  if (min < 60) return min + "m ago"
  const hr = Math.floor(min / 60)
  if (hr < 24) return hr + "h ago"
  const days = Math.floor(hr / 24)
  if (days < 30) return days + "d ago"
  const mo = Math.floor(days / 30)
  if (mo < 12) return mo + "mo ago"
  return Math.floor(mo / 12) + "y ago"
}

const { fetchTrending } = useDTubeFeed()

const categories = [
  { key: "all", label: "All" },
  { key: "music", label: "Music" },
  { key: "gaming", label: "Gaming" },
  { key: "news", label: "News" },
  { key: "education", label: "Education" },
  { key: "entertainment", label: "Entertainment" },
  { key: "technology", label: "Technology" },
  { key: "art", label: "Art" },
  { key: "sports", label: "Sports" },
  { key: "travel", label: "Travel" },
  { key: "vlog", label: "Vlog" },
]

const videos = ref([])
const loading = ref(true)
const loadingMore = ref(false)
const error = ref(null)
const activeCategory = ref("all")
const hasMore = ref(true)

async function loadVideos() {
  loading.value = true
  error.value = null
  try {
    const results = await fetchTrending({ limit: 50 })
    videos.value = results
    hasMore.value = results.length === 50
  } catch (e) {
    error.value = "Failed to load trending videos. Please try again."
    console.error("DTube trending error:", e)
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  if (videos.value.length === 0) return
  loadingMore.value = true
  try {
    const last = videos.value[videos.value.length - 1]
    const more = await fetchTrending({
      limit: 20,
      start_author: last.author,
      start_permlink: last.permlink,
    })
    videos.value.push(...more)
    hasMore.value = more.length === 20
  } catch (e) {
    console.error("Load more error:", e)
  } finally {
    loadingMore.value = false
  }
}

function selectCategory(key) {
  activeCategory.value = key
}

onMounted(loadVideos)
</script>
