<template>
  <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
    <!-- Section header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
        Comments
        <span v-if="totalComments > 0" class="text-sm text-gray-500 dark:text-gray-400 ml-1 font-normal">
          ({{ totalComments }})
        </span>
      </h3>
    </div>

    <!-- Comment form -->
    <div v-if="isAuthenticated" class="flex gap-3 mb-6">
      <div class="w-9 h-9 rounded-full bg-dtube-accent flex items-center justify-center text-white font-bold text-sm shrink-0">
        {{ userInitial }}
      </div>
      <div class="flex-1">
        <textarea
          v-model="newComment"
          :placeholder="Add a comment..."
          rows="2"
          class="w-full bg-transparent border-b border-gray-300 dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:border-dtube-accent resize-none pb-1"
          @keydown.meta.enter="submitComment"
          @keydown.ctrl.enter="submitComment"
        />
        <div class="flex justify-end mt-2 gap-2">
          <button
            v-if="newComment"
            @click="newComment = "
            class="px-3 py-1 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
          >
            Cancel
          </button>
          <button
            @click="submitComment"
            :disabled="!newComment.trim() || submitting"
            class="px-4 py-1.5 text-sm font-medium rounded-full bg-dtube-accent text-white disabled:opacity-40 hover:bg-red-700 transition-colors"
          >
            {{ submitting ? "Posting..." : "Comment" }}
          </button>
        </div>
      </div>
    </div>
    <div v-else class="mb-6 text-sm text-gray-500 dark:text-gray-400">
      <router-link to="/dtube/login" class="text-dtube-accent hover:underline">Sign in</router-link> to comment.
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="flex gap-3 animate-pulse">
        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 shrink-0" />
        <div class="flex-1">
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-2" />
          <div class="h-3 bg-gray-100 dark:bg-gray-600 rounded w-3/4" />
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="comments.length === 0" class="text-center py-8 text-sm text-gray-400">
      No comments yet. Be the first!
    </div>

    <!-- Comment list -->
    <div v-else class="space-y-4">
      <CommentItem
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
        @reply="onReply"
        @deleted="onDeleted"
      />
    </div>

    <!-- Load more -->
    <div v-if="hasMore" class="text-center mt-4">
      <button
        @click="loadMore"
        :disabled="loadingMore"
        class="text-sm text-dtube-accent hover:text-red-700 font-medium"
      >
        {{ loadingMore ? "Loading..." : "Load more replies" }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useApiClient } from "@/composables/useApiClient"
import { useAuthStore } from "@/stores/auth"
import CommentItem from "@/components/dtube/CommentItem.vue"

const props = defineProps({
  videoId: { type: [String, Number], required: true },
  hivePermlink: { type: String, default: null },
})

const api = useApiClient()
const authStore = useAuthStore()

const comments = ref([])
const loading = ref(true)
const loadingMore = ref(false)
const hasMore = ref(false)
const newComment = ref("")
const submitting = ref(false)
const totalComments = ref(0)
const page = ref(1)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const userInitial = computed(() => authStore.user?.username?.charAt(0).toUpperCase() || "?")

async function fetchComments() {
  loading.value = true
  try {
    const res = await api.get(`/api/v1/video/${props.videoId}`)
    // Comments endpoint
    const commentRes = await api.get(`/api/v1/video/comments/${props.videoId}`, {
      params: { page: page.value, per_page: 20 }
    })
    comments.value = commentRes.data?.data || []
    totalComments.value = commentRes.data?.total || 0
    hasMore.value = commentRes.data?.next_page_url != null
  } catch (e) {
    console.error("Fetch comments error:", e)
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  loadingMore.value = true
  page.value++
  try {
    const res = await api.get(`/api/v1/video/comments/${props.videoId}`, {
      params: { page: page.value, per_page: 20 }
    })
    comments.value.push(...(res.data?.data || []))
    hasMore.value = res.data?.next_page_url != null
  } catch {} finally {
    loadingMore.value = false
  }
}

async function submitComment() {
  if (!newComment.value.trim() || submitting.value) return
  submitting.value = true
  try {
    const res = await api.post(`/api/v1/video/comment/${props.videoId}`, {
      body: newComment.value,
    })
    if (res.data?.data) {
      comments.value.unshift(res.data.data)
      totalComments.value++
    }
    newComment.value = ""
  } catch (e) {
    console.error("Submit comment error:", e)
  } finally {
    submitting.value = false
  }
}

function onReply(data) {
  // Find the parent comment and add reply
  const idx = comments.value.findIndex(c => c.id === data.parentId)
  if (idx >= 0) {
    if (!comments.value[idx].replies) comments.value[idx].replies = []
    comments.value[idx].replies.push(data.reply)
    totalComments.value++
  }
}

function onDeleted(commentId) {
  comments.value = comments.value.filter(c => c.id !== commentId)
  totalComments.value = Math.max(0, totalComments.value - 1)
}

onMounted(fetchComments)
</script>
