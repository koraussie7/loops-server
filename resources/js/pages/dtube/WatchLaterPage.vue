<template>
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Watch Later</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Videos saved for later</p>
      </div>
      <button
        v-if="videos.length > 0"
        @click="clearAll"
        class="text-xs text-gray-400 hover:text-dtube-accent transition-colors px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-lg"
      >
        Clear all
      </button>
    </div>

    <div v-if="videos.length === 0" class="text-center py-16">
      <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-sm text-gray-400">No saved videos</p>
      <p class="text-xs text-gray-400 mt-1">Click "Watch Later" on any video to save it here</p>
    </div>

    <div v-else class="space-y-2">
      <div v-for="video in videos" :key="video.id" class="relative group">
        <DTubeVideoCard :video="video" />
        <button
          @click="remove(video.id)"
          class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity p-1.5 bg-black/60 hover:bg-black/80 text-white rounded-lg text-xs"
          title="Remove"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import DTubeVideoCard from "@/components/dtube/DTubeVideoCard.vue"

const STORAGE_KEY = "dtube_watch_later"
const videos = ref([])

function load() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    videos.value = raw ? JSON.parse(raw) : []
  } catch {
    videos.value = []
  }
}

function remove(id) {
  videos.value = videos.value.filter((v) => v.id !== id)
  save()
}

function clearAll() {
  videos.value = []
  save()
}

function save() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(videos.value))
}

// Add video to watch later (exposed globally for cross-component use)
function addToWatchLater(video) {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    const list = raw ? JSON.parse(raw) : []
    if (list.some((v) => v.id === video.id)) return // already saved
    list.push({
      id: video.id,
      permlink: video.permlink,
      author: video.author,
      title: video.title,
      thumbnail: video.thumbnail,
      duration: video.duration,
      created: video.created,
      net_votes: video.net_votes,
      description: video.description,
    })
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list))
  } catch {}
}

function removeFromWatchLater(id) {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    const list = raw ? JSON.parse(raw) : []
    const filtered = list.filter((v) => v.id !== id)
    localStorage.setItem(STORAGE_KEY, JSON.stringify(filtered))
  } catch {}
}

// Expose for external use
if (typeof window !== "undefined") {
  window.__dtubeAddWatchLater = addToWatchLater
  window.__dtubeRemoveWatchLater = removeFromWatchLater
}

onMounted(load)
</script>
