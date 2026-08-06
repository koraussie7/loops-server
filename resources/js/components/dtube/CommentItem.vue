<template>
  <div class="flex gap-3 group">
    <!-- Avatar -->
    <div class="shrink-0">
      <router-link v-if="comment.author_avatar" :to="`/dtube/c/${comment.author}`">
        <img :src="comment.author_avatar" class="w-8 h-8 rounded-full object-cover" alt="" />
      </router-link>
      <div v-else class="w-8 h-8 rounded-full bg-dtube-accent flex items-center justify-center text-white font-bold text-sm">
        {{ comment.author?.charAt(0).toUpperCase() || "?" }}
      </div>
    </div>

    <div class="flex-1 min-w-0">
      <!-- Header -->
      <div class="flex items-center gap-2 text-sm">
        <router-link
          :to="`/dtube/c/${comment.author}`"
          class="font-semibold text-gray-900 dark:text-gray-100 hover:text-dtube-accent"
        >
          {{ comment.author || comment.author_username || "Anonymous" }}
        </router-link>
        <span class="text-xs text-gray-400">{{ timeAgo(comment.created_at || comment.created) }}</span>
      </div>

      <!-- Body -->
      <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5 whitespace-pre-wrap break-words">
        {{ comment.body || comment.text || comment.content }}
      </p>

      <!-- Actions -->
      <div class="flex items-center gap-4 mt-1 text-xs text-gray-400">
        <button
          v-if="showReply"
          @click="toggleReply"
          class="hover:text-gray-600 dark:hover:text-gray-300 font-medium"
        >
          Reply
        </button>
        <button
          v-if="isOwner"
          @click="deleteComment"
          class="hover:text-red-500 font-medium"
        >
          Delete
        </button>
      </div>

      <!-- Reply form -->
      <div v-if="replying" class="mt-2 flex gap-2">
        <input
          v-model="replyText"
          :placeholder="Write a reply..."
          class="flex-1 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-dtube-accent"
          @keydown.enter="submitReply"
        />
        <button
          @click="submitReply"
          :disabled="!replyText.trim()"
          class="px-3 py-1.5 text-sm font-medium rounded-lg bg-dtube-accent text-white disabled:opacity-40"
        >
          Reply
        </button>
      </div>

      <!-- Replies -->
      <div v-if="comment.replies && comment.replies.length" class="mt-2 space-y-3 pl-2 border-l-2 border-gray-200 dark:border-gray-700">
        <CommentItem
          v-for="reply in comment.replies"
          :key="reply.id"
          :comment="reply"
          :show-reply="false"
          @deleted="(id) => $emit(deleted, id)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import { useApiClient } from "@/composables/useApiClient"
import { useAuthStore } from "@/stores/auth"

const props = defineProps({
  comment: { type: Object, required: true },
  showReply: { type: Boolean, default: true },
})

const emit = defineEmits(["reply", "deleted"])

const api = useApiClient()
const authStore = useAuthStore()
const replying = ref(false)
const replyText = ref("")

const isOwner = computed(() => {
  return authStore.user?.username === props.comment.author
    || authStore.user?.id === props.comment.user_id
})

function timeAgo(dateStr) {
  if (!dateStr) return ""
  const now = new Date()
  const d = new Date(dateStr)
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

function toggleReply() {
  replying.value = !replying.value
}

async function submitReply() {
  if (!replyText.value.trim()) return
  try {
    const res = await api.post(`/api/v1/video/comment/${props.comment.video_id || props.comment.id}`, {
      body: replyText.value,
      parent_id: props.comment.id,
    })
    if (res.data?.data) {
      emit("reply", { parentId: props.comment.id, reply: res.data.data })
    }
    replyText.value = ""
    replying.value = false
  } catch (e) {
    console.error("Reply error:", e)
  }
}

async function deleteComment() {
  if (!confirm("Delete this comment?")) return
  try {
    await api.post(`/api/v1/video/comment/${props.comment.id}/delete`)
    emit("deleted", props.comment.id)
  } catch (e) {
    console.error("Delete error:", e)
  }
}
</script>
