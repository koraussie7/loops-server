<template>
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">History</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Videos you have watched</p>
      </div>
      <button
        v-if="history.length > 0"
        @click="clearHistory"
        class="text-xs text-gray-400 hover:text-dtube-accent transition-colors px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-lg"
      >
        Clear all
      </button>
    </div>

    <div v-if="history.length === 0" class="text-center py-16">
      <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-sm text-gray-400">No watch history yet</p>
      <p class="text-xs text-gray-400 mt-1">Videos you watch will appear here</p>
    </div>

    <div v-else class="space-y-2">
      <DTubeVideoCard
        v-for="video in history"
        :key="video.id"
        :video="video"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import DTubeVideoCard from "@/components/dtube/DTubeVideoCard.vue"

const HISTORY_KEY = "dtube_watch_history"
const history = ref([])

function loadHistory() {
  try {
    const raw = localStorage.getItem(HISTORY_KEY)
    history.value = raw ? JSON.parse(raw) : []
  } catch {
    history.value = []
  }
}

function clearHistory() {
  localStorage.removeItem(HISTORY_KEY)
  history.value = []
}

// Utility to add a video to history (callable from other components via window)
function addToHistory(video) {
  try {
    const raw = localStorage.getItem(HISTORY_KEY)
    const list = raw ? JSON.parse(raw) : []
    // Remove duplicate if exists
    const filtered = list.filter((v) => v.id !== video.id)
    // Add to front
    filtered.unshift({
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
    // Keep max 100 entries
    if (filtered.length > 100) filtered.length = 100
    localStorage.setItem(HISTORY_KEY, JSON.stringify(filtered))
  } catch {}
}

// Expose for external use
if (typeof window !== "undefined") window.__dtubeAddHistory = addToHistory

onMounted(loadHistory)
</script>
