<template>
  <div class="dtube-content max-w-3xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">DADA AI Coin</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Earn DADAPOINT tokens by watching videos on MuhanTube
      </p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 mb-8">
      <!-- Balance card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-dtube-accent to-purple-600 flex items-center justify-center">
            <span class="text-white font-bold text-sm">D</span>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Your Balance</h3>
            <p class="text-xs text-gray-400">DADAPOINT</p>
          </div>
        </div>
        <p class="text-3xl font-bold text-dtube-accent mb-1">{{ formattedBalance }}</p>
        <p class="text-xs text-gray-400">~ ${{ usdEstimate }} USD</p>
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
          <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>Daily earned today</span>
            <span class="font-medium">{{ dailyEarned }} / {{ dailyLimit }} DADA</span>
          </div>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
            <div class="bg-dtube-accent h-1.5 rounded-full transition-all" :style="progressStyle" />
          </div>
        </div>
      </div>

      <!-- Stats card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Reward Stats</h3>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Reward rate</span>
            <span class="font-medium">{{ rewardPerSecond }} DADA / sec</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Minimum watch time</span>
            <span class="font-medium">{{ minWatchSeconds }} seconds</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Max per session</span>
            <span class="font-medium">{{ maxPerSession }} DADA</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Max daily</span>
            <span class="font-medium">{{ dailyLimit }} DADA</span>
          </div>
        </div>
      </div>
    </div>

    <!-- How it works -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-8">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">How It Works</h3>
      <ol class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
        <li class="flex gap-3">
          <span class="w-6 h-6 rounded-full bg-dtube-accent text-white flex items-center justify-center text-xs font-bold shrink-0">1</span>
          <span>Install <strong>Minima</strong> on your device and enable MDS (Mini Distributed System)</span>
        </li>
        <li class="flex gap-3">
          <span class="w-6 h-6 rounded-full bg-dtube-accent text-white flex items-center justify-center text-xs font-bold shrink-0">2</span>
          <span><strong>Sign in</strong> with your Minima wallet using MDS signature</span>
        </li>
        <li class="flex gap-3">
          <span class="w-6 h-6 rounded-full bg-dtube-accent text-white flex items-center justify-center text-xs font-bold shrink-0">3</span>
          <span><strong>Watch videos</strong> — every second watched earns you DADAPOINT tokens</span>
        </li>
        <li class="flex gap-3">
          <span class="w-6 h-6 rounded-full bg-dtube-accent text-white flex items-center justify-center text-xs font-bold shrink-0">4</span>
          <span><strong>Claim rewards</strong> from your channel page and they will be sent to your Minima wallet</span>
        </li>
      </ol>
    </div>

    <!-- Minima Login / Status -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
      <MinimaLogin @login="onLogin" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useApiClient } from "@/composables/useApiClient"
import { useMinima } from "@/composables/useMinima"
import { useMinimaStore } from "@/stores/minima"
import MinimaLogin from "@/components/dtube/MinimaLogin.vue"

const api = useApiClient()
const minimaStore = useMinimaStore()
const { getDadaBalance } = useMinima()

const balance = ref(0)
const dailyEarned = ref(0)
const dailyLimit = ref(1000)
const rewardPerSecond = ref(10)
const minWatchSeconds = ref(30)
const maxPerSession = ref(100000)

const formattedBalance = computed(() => parseInt(balance.value).toLocaleString())
const usdEstimate = computed(() => (balance.value * 0.001).toFixed(2))
const dailyProgress = computed(() => Math.min(100, (dailyEarned.value / dailyLimit.value) * 100))
const progressStyle = computed(() => ({ width: dailyProgress.value + "%" }))

async function fetchStatus() {
  try {
    const res = await api.get("/api/v1/rewards/status")
    const data = res.data || {}
    dailyEarned.value = data.today_earned || 0
    dailyLimit.value = data.daily_limit || 1000
    rewardPerSecond.value = data.reward_per_second || 10
    minWatchSeconds.value = data.min_watch_seconds || 30
  } catch {}

  try {
    const healthRes = await api.get("/api/v1/rewards/health")
    const h = healthRes.data || {}
    maxPerSession.value = h.max_per_session || 100000
  } catch {}
}

async function fetchBalance() {
  if (minimaStore.mdsAvailable) {
    const d = await getDadaBalance()
    balance.value = parseInt(d?.sendable || d?.confirmed || 0)
  }
}

function onLogin() {
  fetchBalance()
}

onMounted(() => {
  fetchStatus()
  fetchBalance()
})
</script>
