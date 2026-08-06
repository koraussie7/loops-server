<template>
  <div class="dtube-content">
    <div v-if="loading" class="flex items-center justify-center py-20">
      <svg class="animate-spin w-8 h-8 text-dtube-accent" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
      </svg>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <p class="text-gray-500 dark:text-gray-400">{{ error }}</p>
      <button @click="fetchVideo" class="mt-4 px-4 py-2 bg-dtube-accent text-white rounded-lg hover:bg-red-700 transition-colors">
        Retry
      </button>
    </div>

    <div v-else class="flex flex-col lg:flex-row gap-6">
      <!-- Main column -->
      <div class="flex-1 min-w-0">
        <VideoPlayer :src="videoSrc" :poster="video.thumbnail" class="mb-4" />

        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-3">
          {{ video.title }}
        </h1>

        <!-- Action bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
            <span>{{ formatCount(video.net_votes) }} views</span>
            <span>&middot;</span>
            <span>{{ timeAgo(video.created) }}</span>
          </div>
          <div class="flex items-center gap-2">
            <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" /></svg>
              {{ video.net_votes }}
            </button>
            <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
              {{ video.children || 0 }}
            </button>
            <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
              Share
            </button>
          </div>
        </div>

        <!-- Author -->
        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
          <router-link :to="`/dtube/c/${video.author}`" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-full bg-dtube-accent flex items-center justify-center text-white font-bold text-sm">
              {{ video.author.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-dtube-accent transition-colors">
                {{ video.author }}
              </p>
              <p class="text-xs text-gray-400">{{ formatCount(video.net_votes) }} subscribers</p>
            </div>
          </router-link>
        </div>

        <!-- Tags -->
        <div v-if="video.tags && video.tags.length" class="flex flex-wrap gap-2 mb-4">
          <router-link
            v-for="tag in video.tags.slice(0, 8)" :key="tag"
            :to="`/dtube/t/${tag}`"
            class="px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-xs rounded-full text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
          >#{{ tag }}</router-link>
        </div>

        <!-- Description -->
        <div v-if="video.description" class="mb-6">
          <p :class="['text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap', { 'line-clamp-3': !showFullDesc }]">
            {{ video.description }}
          </p>
          <button
            v-if="video.description.length > 200"
            @click="showFullDesc = !showFullDesc"
            class="text-xs text-dtube-accent hover:text-red-700 mt-1 font-medium"
          >{{ showFullDesc ? "Show less" : "Show more" }}</button>
        </div>
      </div>

      <!-- Related -->
      <div class="w-full lg:w-[360px] shrink-0">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Related Videos</h3>
        <div v-if="relatedLoading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="flex gap-2 animate-pulse">
            <div class="w-[168px] aspect-video bg-gray-200 dark:bg-gray-700 rounded-lg shrink-0" />
            <div class="flex-1">
              <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-full mb-1" />
              <div class="h-2.5 bg-gray-100 dark:bg-gray-600 rounded w-2/3" />
            </div>
          </div>
        </div>
        <div v-else class="space-y-3">
          <DTubeVideoCard v-for="related in relatedVideos" :key="related.id" :video="related" class="!flex-row !gap-2" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useRoute } from "vue-router"
import { useUtils } from "@/composables/useUtils"
import { useDTubeFeed } from "@/composables/useDTubeFeed"
import VideoPlayer from "@/components/dtube/VideoPlayer.vue"
import DTubeVideoCard from "@/components/dtube/DTubeVideoCard.vue"

function timeAgo(dateStr) {
  if (!dateStr) return ""
  const now = new Date()
  const d = new Date(dateStr + "Z")
  const sec = Math.floor((now - d) / 1000)
  if (sec < 60) return "just now"
  const min = Math.floor(sec / 60)
  if (min < 60) return min + "m ago"
  const hr = Math.floor(min / 60)
  if (hr < 24) return hr + "h ago"
  const days = Math.floor(hr / 24)
  if (days < 30) return days + "d ago"
  const mo = Math.floor(days / 30)
  if (mo < 12) return mo + "mo ago"
  return Math.floor(mo / 12) + "y ago"
}

const route = useRoute()
const { formatCount } = useUtils()
const { fetchTrending } = useDTubeFeed()

const video = ref({
  id: "", author: "", permlink: "", title: "DTube Video",
  thumbnail: "", videoSrc: "", net_votes: 0, children: 0,
  created: "", description: "", tags: [], pending_payout: "0.000 HBD",
})

const loading = ref(true)
const error = ref(null)
const showFullDesc = ref(false)
const relatedVideos = ref([])
const relatedLoading = ref(true)

const videoSrc = computed(() => {
  return video.value.videoSrc || `https://ipfs-3speak.b-cdn.net/ipfs/${video.value.permlink}`
})

async function fetchVideo() {
  const author = route.params.author
  const id = route.params.id
  if (!author || !id) { error.value = "Invalid video URL"; loading.value = false; return }

  loading.value = true; error.value = null
  try {
    const res = await fetch("https://api.hive.blog", {
      method: "POST", headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ jsonrpc: "2.0", id: 1, method: "condenser_api.get_content", params: [author, id] }),
    })
    const body = await res.json()
    const post = body.result
    if (!post || !post.author) throw new Error("Video not found")

    let meta = {}
    try { meta = JSON.parse(post.json_metadata) } catch {}
    const vc = meta?.video?.content ?? {}
    const vi = meta?.video?.info ?? {}

    video.value = {
      id: `${post.author}/${post.permlink}`, permlink: post.permlink, author: post.author,
      title: post.title || vi.title || "Untitled",
      thumbnail: vc.thumb || vi.thumb || `https://picsum.photos/seed/${post.permlink}/320/180`,
      videoSrc: vc.videohash ? `https://ipfs-3speak.b-cdn.net/ipfs/${vc.videohash}` : (vc.src640 || vc.src || null),
      net_votes: post.net_votes || 0, children: post.children || 0, created: post.created,
      description: post.body ? post.body.slice(0, 500) : "",
      tags: Array.isArray(meta.tags) ? meta.tags : [], pending_payout: post.pending_payout_value || "0.000 HBD",
    }
  } catch (e) {
    error.value = e.message || "Could not load video"
    console.error("WatchPage error:", e)
  } finally { loading.value = false }
}

async function loadRelated() {
  relatedLoading.value = true
  try {
    const results = await fetchTrending({ limit: 10 })
    relatedVideos.value = results.filter(r => r.id !== video.value.id).slice(0, 8)
  } catch {} finally { relatedLoading.value = false }
}

onMounted(async () => { await fetchVideo(); loadRelated() })
</script>
