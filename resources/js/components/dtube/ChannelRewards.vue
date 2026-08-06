<template>
  <div>
    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Rewards</h3>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <svg class="animate-spin w-6 h-6 mx-auto text-dtube-accent" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
      </svg>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-8 text-sm text-gray-400">
      <p>{{ error }}</p>
      <button @click="fetchRewards" class="text-dtube-accent hover:underline mt-2 text-xs">Retry</button>
    </div>

    <!-- Rewards table -->
    <div v-else-if="rewards.length === 0" class="text-center py-8 text-sm text-gray-400">
      No rewards yet. Watch videos to earn DADAPOINT tokens!
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700 text-xs text-gray-500 uppercase">
            <th class="text-left pb-2 font-medium">Video</th>
            <th class="text-right pb-2 font-medium">Amount</th>
            <th class="text-right pb-2 font-medium">Date</th>
            <th class="text-right pb-2 font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="reward in rewards"
            :key="reward.id"
            class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50"
          >
            <td class="py-3 pr-4">
              <p class="text-gray-900 dark:text-gray-100 font-medium truncate max-w-[200px]">
                {{ reward.video_title || "Video" }}
              </p>
            </td>
            <td class="py-3 text-right text-dtube-accent font-semibold whitespace-nowrap">
              {{ reward.reward_amount || 0 }} DADA
            </td>
            <td class="py-3 text-right text-gray-400 text-xs whitespace-nowrap">
              {{ formatDate(reward.created_at) }}
            </td>
            <td class="py-3 text-right">
              <span
                :class="[
                  inline-flex px-2 py-0.5 text-xs rounded-full font-medium,
                  reward.status === "claimed"
                    ? "bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300"
                    : reward.status === "pending"
                      ? "bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300"
                      : "bg-gray-100 dark:bg-gray-800 text-gray-500"
                ]"
              >
                {{ reward.status || "pending" }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useApiClient } from "@/composables/useApiClient"

const api = useApiClient()
const rewards = ref([])
const loading = ref(true)
const error = ref(null)

function formatDate(dateStr) {
  if (!dateStr) return ""
  const d = new Date(dateStr)
  return d.toLocaleDateString("en-US", { month: "short", day: "numeric" })
}

async function fetchRewards() {
  loading.value = true
  error.value = null
  try {
    const res = await api.get("/api/v1/rewards/history")
    rewards.value = res.data?.data || res.data?.rewards || []
  } catch (e) {
    error.value = "Could not load rewards"
    console.error("Rewards error:", e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchRewards)
</script>
