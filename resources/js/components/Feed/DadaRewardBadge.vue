<template>
    <div
        v-if="visible"
        class="dada-reward-badge absolute top-3 left-3 z-20 flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold pointer-events-none select-none"
        :class="badgeClass"
    >
        <span class="text-[10px]">◆</span>
        <span v-if="earnedTokens > 0" class="tabular-nums">
            +{{ formatReward }}
        </span>
        <span v-else class="tabular-nums">
            {{ rewardPerSecond }} DADA/s
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    earnedTokens: { type: Number, default: 0 },
    isTracking: { type: Boolean, default: false },
    rewardPerSecond: { type: Number, default: 10 },
    visible: { type: Boolean, default: true }
})

const formatReward = computed(() => {
    if (props.earnedTokens >= 1000) {
        return (props.earnedTokens / 1000).toFixed(1) + 'k'
    }
    return props.earnedTokens
})

const badgeClass = computed(() => {
    if (props.earnedTokens > 0) {
        return 'bg-gradient-to-r from-amber-500 to-yellow-400 text-white shadow-lg shadow-amber-500/30'
    }
    if (props.isTracking) {
        return 'bg-black/60 text-amber-300 border border-amber-500/40'
    }
    return 'bg-black/40 text-white/60'
})
</script>
