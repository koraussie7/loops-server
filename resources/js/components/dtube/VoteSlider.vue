<template>
  <div class="flex items-center gap-1">
    <!-- Upvote -->
    <button
      @click="toggleUpvote"
      :class="[
        'flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
        upvoted ? 'bg-dtube-accent text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
      ]"
    >
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
      </svg>
      <span>{{ displayCount }}</span>
    </button>

    <!-- Downvote -->
    <button
      @click="toggleDownvote"
      :class="[
        'flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
        downvoted ? 'bg-dtube-primary text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
      ]"
    >
      <svg class="w-5 h-5 transform rotate-180" fill="currentColor" viewBox="0 0 24 24">
        <path d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import { useApiClient } from "@/composables/useApiClient"

const props = defineProps({
  videoId: { type: [String, Number], required: true },
  initialVotes: { type: Number, default: 0 },
  initialUpvoted: { type: Boolean, default: false },
})

const emit = defineEmits(["vote"])

const count = ref(props.initialVotes)
const upvoted = ref(props.initialUpvoted)
const downvoted = ref(false)

const displayCount = computed(() => {
  let c = count.value
  if (upvoted.value && !props.initialUpvoted) c++
  if (!upvoted.value && props.initialUpvoted) c--
  return c > 0 ? (c >= 1000 ? (c / 1000).toFixed(1) + "k" : c) : c
})

async function toggleUpvote() {
  const api = useApiClient()
  try {
    if (upvoted.value) {
      await api.post(`/api/v1/video/unlike/${props.videoId}`)
      upvoted.value = false
    } else {
      await api.post(`/api/v1/video/like/${props.videoId}`)
      upvoted.value = true
      downvoted.value = false
    }
    emit("vote", { upvoted: upvoted.value, downvoted: downvoted.value })
  } catch (e) {
    console.error("Vote error:", e)
  }
}

async function toggleDownvote() {
  downvoted.value = !downvoted.value
  if (upvoted.value) {
    upvoted.value = false
    // Also call unlike
    try {
      const api = useApiClient()
      await api.post(`/api/v1/video/unlike/${props.videoId}`)
    } catch {}
  }
  emit("vote", { upvoted: upvoted.value, downvoted: downvoted.value })
}
</script>
