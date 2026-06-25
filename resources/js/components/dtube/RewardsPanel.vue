<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">DADA AI Rewards</h3>
      <span
        v-if="mdsAvailable"
        class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs rounded-full"
      >
        <span class="w-1.5 h-1.5 bg-green-500 rounded-full" />
        MDS Online
      </span>
      <span v-else class="text-xs text-gray-400">MDS Offline</span>
    </div>

    <!-- Balance -->
    <div class="text-center py-4">
      <p class="text-3xl font-bold text-dtube-accent">{{ formattedBalance }}</p>
      <p class="text-xs text-gray-400 mt-1">DADAPOINT balance</p>
    </div>

    <!-- Mini info -->
    <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
      <div class="flex justify-between">
        <span>Daily earned</span>
        <span class="font-medium text-gray-700 dark:text-gray-300">{{ dailyEarned }} DADA</span>
      </div>
      <div class="flex justify-between">
        <span>Daily limit</span>
        <span class="font-medium text-gray-700 dark:text-gray-300">{{ dailyLimit }} DADA</span>
      </div>
      <div v-if="walletAddress" class="flex justify-between">
        <span>Wallet</span>
        <span class="font-mono text-xs max-w-[120px] truncate">{{ walletAddress }}</span>
      </div>
    </div>

    <!-- Refresh button -->
    <button
      @click="$emit(refresh)"
      class="mt-3 w-full py-1.5 text-xs text-dtube-accent hover:text-red-700 font-medium border border-dtube-accent/30 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors"
    >
      Refresh Balance
    </button>
  </div>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
  balance: { type: [Number, String], default: 0 },
  dailyEarned: { type: Number, default: 0 },
  dailyLimit: { type: Number, default: 1000 },
  walletAddress: { type: String, default: null },
  mdsAvailable: { type: Boolean, default: false },
})

defineEmits(["refresh"])

const formattedBalance = computed(() => {
  const n = parseInt(props.balance) || 0
  return n.toLocaleString()
})
</script>
