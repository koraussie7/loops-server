<template>
  <div class="dtube-content">
    <!-- Hero banner -->
    <div class="bg-gradient-to-r from-dtube-primary to-blue-900 rounded-xl p-6 mb-8 text-white">
      <h1 class="text-2xl font-bold mb-1">MuhanTube</h1>
      <p class="text-sm text-blue-200">Decentralized video platform powered by Hive blockchain</p>
    </div>

    <!-- Main content -->
    <div v-if="loading" class="space-y-8">
      <div v-for="s in 3" :key="s" class="animate-pulse">
        <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-36 mb-4" />
        <div class="flex gap-3">
          <div v-for="i in 4" :key="i" class="shrink-0 w-[200px] aspect-video bg-gray-200 dark:bg-gray-700 rounded-lg" />
        </div>
      </div>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <p class="text-gray-500 dark:text-gray-400 mb-4">{{ error }}</p>
      <button @click="loadAll" class="px-4 py-2 bg-dtube-accent text-white rounded-lg hover:bg-red-700 transition-colors">
        Retry
      </button>
    </div>

    <div v-else class="space-y-2">
      <!-- Trending slider -->
      <VideoSlider
        title="Trending"
        :videos="trending"
        :loading="trendingLoading"
        seeAllLink="/dtube/trending"
      />

      <!-- Hot slider -->
      <VideoSlider
        title="Hot"
        :videos="hot"
        :loading="hotLoading"
        seeAllLink="/dtube/hot"
      />

      <!-- New slider -->
      <VideoSlider
        title="New Videos"
        :videos="newVids"
        :loading="newLoading"
        seeAllLink="/dtube/new"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useDTubeFeed } from "@/composables/useDTubeFeed"
import VideoSlider from "@/components/dtube/VideoSlider.vue"

const { fetchTrending, fetchHot, fetchNew } = useDTubeFeed()

const loading = ref(true)
const error = ref(null)
const trending = ref([])
const trendingLoading = ref(true)
const hot = ref([])
const hotLoading = ref(true)
const newVids = ref([])
const newLoading = ref(true)

async function loadAll() {
  loading.value = true
  error.value = null

  // Load all three sections in parallel
  const results = await Promise.allSettled([
    fetchTrending({ limit: 20 }).then(r => { trending.value = r; trendingLoading.value = false }),
    fetchHot({ limit: 20 }).then(r => { hot.value = r; hotLoading.value = false }),
    fetchNew({ limit: 20 }).then(r => { newVids.value = r; newLoading.value = false }),
  ])

  const allFailed = results.every(r => r.status === "rejected")
  if (allFailed) {
    error.value = "Could not load videos. Please make sure you have internet access and try again."
  }

  loading.value = false
}

onMounted(loadAll)
</script>
