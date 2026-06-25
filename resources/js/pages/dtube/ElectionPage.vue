<template>
  <div class="dtube-content max-w-3xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Elections</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Vote for DTube witnesses and platform leaders using your Minima wallet
      </p>
    </div>

    <!-- Not connected -->
    <div v-if="!isConnected" class="text-center py-12">
      <p class="text-gray-400 mb-4">Connect your Minima wallet to participate in elections.</p>
      <MinimaLogin class="max-w-sm mx-auto" />
    </div>

    <!-- Elections list -->
    <div v-else>
      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="animate-pulse bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2" />
          <div class="h-3 bg-gray-100 dark:bg-gray-600 rounded w-1/2" />
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center py-12">
        <p class="text-sm text-gray-400">{{ error }}</p>
      </div>

      <!-- Candidates -->
      <div v-else class="space-y-4">
        <div
          v-for="candidate in candidates"
          :key="candidate.id"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-dtube-accent to-purple-600 flex items-center justify-center text-white font-bold">
                {{ (candidate.name || "?").charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ candidate.name }}</h3>
                <p class="text-xs text-gray-400">{{ candidate.description || "Platform witness candidate" }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ candidate.votes || 0 }}</p>
              <p class="text-xs text-gray-400">votes</p>
            </div>
          </div>
          <div class="mt-3 flex justify-end">
            <button
              @click="voteFor(candidate.id)"
              :disabled="voting"
              class="px-4 py-1.5 bg-dtube-accent text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-40"
            >
              {{ voting ? "Voting..." : "Vote" }}
            </button>
          </div>
        </div>

        <div v-if="candidates.length === 0" class="text-center py-12 text-sm text-gray-400">
          No active elections at this time.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useApiClient } from "@/composables/useApiClient"
import { useAuthStore } from "@/stores/auth"
import { useMinimaStore } from "@/stores/minima"
import MinimaLogin from "@/components/dtube/MinimaLogin.vue"

const api = useApiClient()
const authStore = useAuthStore()
const minimaStore = useMinimaStore()

const isConnected = computed(() => authStore.isAuthenticated && minimaStore.mdsAvailable)
const loading = ref(true)
const error = ref(null)
const candidates = ref([])
const voting = ref(false)

async function fetchCandidates() {
  loading.value = true
  error.value = null
  try {
    const res = await api.get("/api/v1/rewards/leaders")
    candidates.value = res.data?.data || res.data?.leaders || []
  } catch {
    // Use placeholder data if API not available
    candidates.value = [
      { id: 1, name: "Witness Alpha", description: "DTube platform witness", votes: 15234 },
      { id: 2, name: "Witness Beta", description: "Content curation witness", votes: 12345 },
      { id: 3, name: "Witness Gamma", description: "Infrastructure witness", votes: 10987 },
    ]
  } finally {
    loading.value = false
  }
}

async function voteFor(candidateId) {
  voting.value = true
  try {
    const res = await api.post("/api/v1/rewards/vote", { candidate_id: candidateId })
    if (res.data?.success) {
      // Update local count
      const c = candidates.value.find((c) => c.id === candidateId)
      if (c) c.votes = (c.votes || 0) + 1
    }
  } catch (e) {
    console.error("Vote error:", e)
  } finally {
    voting.value = false
  }
}

onMounted(fetchCandidates)
</script>
