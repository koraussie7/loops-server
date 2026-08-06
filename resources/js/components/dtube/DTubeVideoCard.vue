<template>
  <router-link
    :to="dtubeWatchLink"
    class="group flex gap-3 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer"
  >
    <!-- Thumbnail -->
    <div class="relative shrink-0 w-[200px] aspect-video rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-700">
      <img
        v-if="video.thumbnail"
        :src="video.thumbnail"
        :alt="video.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
        @error="thumbError = true"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M10 8l6 4-6 4V8z"/><path d="M0 0h24v24H0z" fill="none"/></svg>
      </div>
      <!-- Duration badge -->
      <span
        v-if="video.duration"
        class="absolute bottom-1 right-1 bg-black/80 text-white text-xs px-1.5 py-0.5 rounded font-medium"
      >
        {{ video.duration }}
      </span>
    </div>

    <!-- Info -->
    <div class="flex-1 min-w-0 py-0.5">
      <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100 line-clamp-2 leading-snug mb-1">
        {{ video.title }}
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 mb-0.5">
        {{ video.author }}
      </p>
      <p class="text-xs text-gray-400 dark:text-gray-500">
        {{ formatCount(video.net_votes) }} views
        <span v-if="video.created" class="mx-1">&middot;</span>
        <span v-if="video.created">{{ timeAgo(video.created) }}</span>
      </p>
      <p v-if="video.description" class="text-xs text-gray-400 dark:text-gray-500 mt-1 line-clamp-1">
        {{ video.description }}
      </p>
    </div>
  </router-link>
</template>

<script setup>
import { ref, computed } from "vue"
import { useUtils } from "@/composables/useUtils"

const props = defineProps({
  video: { type: Object, required: true },
})

const { formatCount, timeAgo } = useUtils()
const thumbError = ref(false)

const dtubeWatchLink = computed(() => `/dtube/v/${props.video.author}/${props.video.permlink}`)
</script>
