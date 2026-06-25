<template>
  <div class="dtube-content">
    <!-- Header with banner -->
    <div v-if="!loading" class="relative mb-6">
      <div class="h-36 bg-gradient-to-r from-dtube-primary to-blue-900 rounded-xl" />
      <div class="flex items-end gap-4 px-6 -mt-10">
        <div class="w-20 h-20 rounded-full bg-dtube-accent flex items-center justify-center text-white font-bold text-2xl border-4 border-white dark:border-gray-900 shrink-0">
          {{ authorName.charAt(0).toUpperCase() }}
        </div>
        <div class="pb-2">
          <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ authorName }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ videos.length }} videos</p>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="animate-pulse space-y-4">
      <div class="h-36 bg-gray-200 dark:bg-gray-700 rounded-xl" />
      <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-48" />
      <div class="space-y-2">
        <div v-for="i in 3" :key="i" class="flex gap-3">
          <div class="w-[200px] aspect-video bg-gray-200 dark:bg-gray-700 rounded-lg" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4" />
            <div class="h-3 bg-gray-100 dark:bg-gray-600 rounded w-1/2" />
          </div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-20">
      <p class="text-gray-500 dark:text-gray-400">{{ error }}</p>
    </div>

    <!-- Tabs -->
    <div v-else>
      <div class="flex gap-6 border-b border-gray-200 dark:border-gray-700 mb-6">
        <button
          v-for="tab in tabs" :key="tab.key"
          @click="activeTab = tab.key"
          :class="['pb-3 text-sm font-medium transition-colors border-b-2 -mb-px', activeTab === tab.key ? 'text-dtube-accent border-dtube-accent' : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300']"
        >{{ tab.label }}</button>
      </div>

      <div v-if="activeTab === videos">
        <div v-if="videos.length === 0" class="text-center py-12 text-sm text-gray-400">No videos yet.</div>
        <div v-else class="space-y-2">
          <DTubeVideoCard v-for="video in videos" :key="video.id" :video="video" />
        </div>
      </div>

      <div v-else-if="activeTab === about" class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl">
        <p v-if="authorBio" class="whitespace-pre-wrap">{{ authorBio }}</p>
        <p v-else class="text-gray-400 italic">No description provided.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useRoute } from "vue-router"
import { useUtils } from "@/composables/useUtils"
import DTubeVideoCard from "@/components/dtube/DTubeVideoCard.vue"

const { formatCount } = useUtils()

function timeAgo(dateStr) {
  if (!dateStr) return ""
  const now = new Date()
  const d = new Date(dateStr + (dateStr.includes("Z") ? "" : "Z"))
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
const authorName = computed(() => route.params.author || "Unknown")
const loading = ref(true)
const error = ref(null)
const videos = ref([])
const authorBio = ref("")
const activeTab = ref("videos")
const tabs = [{ key: "videos", label: "Videos" }, { key: "about", label: "About" }]

async function loadChannel() {
  const author = route.params.author
  if (!author) { error.value = "Invalid channel"; loading.value = false; return }

  loading.value = true
  error.value = null

  try {
    // Fetch account info
    const res = await fetch("https://api.hive.blog", {
      method: "POST", headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ jsonrpc: "2.0", id: 1, method: "condenser_api.get_accounts", params: [[author]] }),
    })
    const body = await res.json()
    const account = body.result?.[0]
    if (account) {
      try {
        const meta = JSON.parse(account.posting_json_metadata || "{}")
        authorBio.value = meta?.profile?.about || ""
      } catch { try { const meta = JSON.parse(account.json_metadata || "{}"); authorBio.value = meta?.profile?.about || "" } catch {} }
    }

    // Fetch author posts
    const postsRes = await fetch("https://api.hive.blog", {
      method: "POST", headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ jsonrpc: "2.0", id: 1, method: "condenser_api.get_discussions_by_blog", params: [{ tag: author, limit: 50 }] }),
    })
    const postsBody = await postsRes.json()
    const allPosts = postsBody.result || []

    const dtubePosts = allPosts.filter(function(p) {
      try { var m = JSON.parse(p.json_metadata); return m && m.tags && m.tags.indexOf("dtube") >= 0 } catch(e) { return false }
    })

    videos.value = dtubePosts.map(function(post) {
      var meta = {}
      try { meta = JSON.parse(post.json_metadata) } catch(e) {}
      var vc = (meta && meta.video && meta.video.content) || {}
      var vi = (meta && meta.video && meta.video.info) || {}
      return {
        id: post.author + "/" + post.permlink,
        permlink: post.permlink,
        author: post.author,
        title: post.title || vi.title || "Untitled",
        thumbnail: vc.thumb || vi.thumb || "https://picsum.photos/seed/" + post.permlink + "/320/180",
        videoSrc: vc.videohash ? "https://ipfs-3speak.b-cdn.net/ipfs/" + vc.videohash : null,
        net_votes: post.net_votes || 0,
        children: post.children || 0,
        created: post.created,
        description: post.body ? post.body.slice(0, 200) : "",
        tags: meta && Array.isArray(meta.tags) ? meta.tags : [],
      }
    })
  } catch (e) {
    error.value = "Could not load channel"
    console.error("ChannelPage error:", e)
  } finally {
    loading.value = false
  }
}

onMounted(loadChannel)
</script>
