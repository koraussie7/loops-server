<template>
  <div class="relative bg-black rounded-lg overflow-hidden" :style="{ aspectRatio: '16/9' }">
    <media-player
      ref="playerRef"
      :src="src"
      :poster="poster || undefined"
      controls
      aspect-ratio="16/9"
      :hls-library="HlsWithP2P"
      @provider-setup="onProviderSetup"
      @error="onVidstackError"
      @ended="emit('ended')"
      @time-update="onTimeUpdate"
      @can-play="onCanPlay"
    >
      <media-outlet>
        <media-poster v-if="poster" :src="poster" alt="Video poster" />
      </media-outlet>
      <media-community-skin />
    </media-player>

    <div
      v-if="error"
      class="absolute inset-0 flex items-center justify-center bg-black/80 text-white pointer-events-none"
    >
      <div class="text-center">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <p class="text-sm text-gray-400 mb-2">Video unavailable</p>
        <p class="text-xs text-gray-500">{{ error }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import Hls from 'hls.js'
import { HlsJsP2PEngine } from 'p2p-media-loader-hlsjs'

const HlsWithP2P = HlsJsP2PEngine.injectMixin(Hls)

import 'vidstack/styles/base.css'
import 'vidstack/styles/ui/buffering.css'
import 'vidstack/styles/ui/buttons.css'
import 'vidstack/styles/ui/captions.css'
import 'vidstack/styles/ui/live.css'
import 'vidstack/styles/ui/menus.css'
import 'vidstack/styles/ui/sliders.css'
import 'vidstack/styles/ui/tooltips.css'
import 'vidstack/styles/community-skin/video.css'

import 'vidstack/define/media-player'
import 'vidstack/define/media-outlet'
import 'vidstack/define/media-poster'
import 'vidstack/define/media-community-skin'

const props = defineProps({
  src: { type: String, required: true },
  poster: { type: String, default: '' },
})

const emit = defineEmits(['loaded', 'error', 'timeupdate', 'ended'])

const error = ref(null)

function onProviderSetup(event) {
  const provider = event.detail
  if (!provider) return

  if (provider.type === 'hls' && provider.instance) {
    const hls = provider.instance
    hls.config.maxBufferLength = 30
    hls.config.maxMaxBufferLength = 60
  }
}

function onCanPlay() {
  error.value = null
  emit('loaded')
}

function onVidstackError(event) {
  const detail = event.detail
  error.value = detail?.message || 'Playback failed'
  emit('error', error.value)
}

function onTimeUpdate(event) {
  const player = event.target
  if (player) {
    emit('timeupdate', player.currentTime)
  }
}
</script>
