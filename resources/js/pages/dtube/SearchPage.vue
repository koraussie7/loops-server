<template>
  <div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Search</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Find videos on DTube</p>
    </div>

    <!-- Search bar -->
    <div class="relative mb-8">
      <div class="flex gap-2">
        <div class="relative flex-1">
          <input
            v-model="query"
            type="text"
            placeholder="Search DTube videos..."
            class="w-full px-4 py-3 pl-11 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-dtube-accent focus:border-transparent"
            @keyup.enter="doSearch"
          />
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <button
          @click="doSearch"
          :disabled="searching || !query.trim()"
          class="px-6 py-3 bg-dtube-accent text-white text-sm font-medium rounded-xl hover:bg-red-700 transition-colors disabled:opacity-40"
        >
          {{ searching ? "Searching..." : "Search" }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="searching" class="flex justify-center py-16">
      <div class="w-8 h-8 border-4 border-dtube-accent border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-16">
      <p class="text-gray-400 text-sm">{{ error }}</p>
    </div>

    <!-- No query -->
    <div v-else-if="!searched" class="text-center py-16 text-sm text-gray-400">
      Enter a keyword to search DTube videos
    </div>

    <!-- Results -->
    <div v-else-if="results.length === 0" class="text-center py-16 text-sm text-gray-400">
      No results found for "{{ query }}"
    </div>

    <div v-else class="space-y-2">
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ results.length }} result(s) for "{{ query }}"</p>
      <DTubeVideoCard v-for="video in results" :key="video.id" :video="video" />
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue"
import { useDTubeFeed } from "@/composables/useDTubeFeed"
import DTubeVideoCard from "@/components/dtube/DTubeVideoCard.vue"

const { searchVideos } = useDTubeFeed()

const query = ref("")
const results = ref([])
const searching = ref(false)
const error = ref(null)
const searched = ref(false)

async function doSearch() {
  const q = query.value.trim()
  if (!q || q.length < 2) return

  searching.value = true
  error.value = null
  results.value = []

  try {
    const res = await searchVideos(q)
    results.value = res || []
    searched.value = true
  } catch (e) {
    error.value = "Search failed. Please try again."
    console.error("Search error:", e)
  } finally {
    searching.value = false
  }
}
</script>
