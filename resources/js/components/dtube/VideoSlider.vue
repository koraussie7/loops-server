<template>
  <section class="mb-8">
    <!-- Section header -->
    <div class="flex items-center justify-between mb-4 px-1">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
        <slot name="icon" />
        {{ title }}
      </h2>
      <router-link
        v-if="seeAllLink"
        :to="seeAllLink"
        class="text-sm text-dtube-accent hover:text-red-700 font-medium transition-colors"
      >
        See all
      </router-link>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="flex gap-3 overflow-hidden">
      <div
        v-for="i in 6"
        :key="i"
        class="shrink-0 w-[200px] animate-pulse"
      >
        <div class="aspect-video bg-gray-200 dark:bg-gray-700 rounded-lg mb-2" />
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-1" />
        <div class="h-2.5 bg-gray-100 dark:bg-gray-600 rounded w-1/2" />
      </div>
    </div>

    <!-- Slider row -->
    <div
      v-else-if="videos.length > 0"
      ref="sliderRef"
      class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide dtube-slider-row"
      @wheel.prevent="handleWheel"
    >
      <DTubeVideoCard
        v-for="video in videos"
        :key="video.id"
        :video="video"
        class="shrink-0"
        :class="cardClass"
      />
    </div>

    <!-- Empty -->
    <div v-else class="text-sm text-gray-400 dark:text-gray-500 py-8 text-center">
      No videos available.
    </div>
  </section>
</template>

<script setup>
import { ref } from "vue"
import DTubeVideoCard from "@/components/dtube/DTubeVideoCard.vue"

defineProps({
  title: { type: String, required: true },
  videos: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  seeAllLink: { type: String, default: null },
  cardClass: { type: String, default: "w-[200px]" },
})

const sliderRef = ref(null)

function handleWheel(e) {
  if (sliderRef.value && Math.abs(e.deltaY) > 5) {
    sliderRef.value.scrollLeft += e.deltaY
  }
}
</script>
