<template>
  <div class="dtube-content max-w-2xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Upload Video</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Share your video with the DTube community
      </p>
    </div>

    <!-- Not logged in -->
    <div v-if="!isAuthenticated" class="text-center py-20">
      <p class="text-gray-500 dark:text-gray-400 mb-4">You need to sign in to upload videos.</p>
      <router-link
        to="/dtube/login"
        class="px-6 py-2 bg-dtube-accent text-white rounded-lg hover:bg-red-700 transition-colors inline-block"
      >
        Sign In
      </router-link>
    </div>

    <!-- Upload form -->
    <form v-else @submit.prevent="handleUpload" class="space-y-6">
      <!-- File dropzone -->
      <div
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="handleDrop"
        :class="[
          'border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer',
          dragOver
            ? 'border-dtube-accent bg-red-50 dark:bg-red-900/10'
            : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
        ]"
        @click="fileInput.click()"
      >
        <input
          ref="fileInput"
          type="file"
          accept="video/*"
          class="hidden"
          @change="handleFileSelect"
        />

        <div v-if="!selectedFile">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            <span class="font-medium text-dtube-accent">Click to upload</span> or drag and drop
          </p>
          <p class="text-xs text-gray-400 mt-1">MP4, WebM, or MOV (max 2GB)</p>
        </div>

        <div v-else class="flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <div class="text-left">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ selectedFile.name }}</p>
            <p class="text-xs text-gray-400">{{ formatSize(selectedFile.size) }}</p>
          </div>
          <button type="button" @click="selectedFile = null" class="text-gray-400 hover:text-red-500 ml-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </div>

      <!-- Thumbnail -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thumbnail (optional)</label>
        <input
          type="file"
          accept="image/*"
          @change="handleThumbnail"
          class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-dtube-accent file:text-white hover:file:bg-red-700"
        />
      </div>

      <!-- Title -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
        <input
          v-model="title"
          type="text"
          required
          maxlength="100"
          placeholder="Enter a descriptive video title"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:border-dtube-accent"
        />
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
        <textarea
          v-model="description"
          rows="4"
          placeholder="Tell viewers about your video"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:border-dtube-accent resize-none"
        />
      </div>

      <!-- Tags -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags</label>
        <div class="flex flex-wrap gap-2 mb-2">
          <span
            v-for="(tag, i) in tags"
            :key="i"
            class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 dark:bg-gray-800 text-xs rounded-full"
          >
            #{{ tag }}
            <button type="button" @click="tags.splice(i, 1)" class="text-gray-400 hover:text-red-500">&times;</button>
          </span>
        </div>
        <div class="flex gap-2">
          <input
            v-model="tagInput"
            type="text"
            placeholder="Add a tag"
            maxlength="30"
            class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:border-dtube-accent"
            @keydown.enter.prevent="addTag"
            @keydown.,="addTag"
          />
          <button type="button" @click="addTag" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">
            Add
          </button>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex gap-3 pt-2">
        <button
          type="submit"
          :disabled="uploading || !title.trim() || !selectedFile"
          class="flex items-center gap-2 px-6 py-2.5 bg-dtube-accent text-white rounded-lg font-medium hover:bg-red-700 transition-colors disabled:opacity-40"
        >
          <svg v-if="uploading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
          {{ uploading ? "Uploading..." : "Upload" }}
        </button>
        <router-link
          to="/dtube"
          class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
        >
          Cancel
        </router-link>
      </div>

      <!-- Progress bar -->
      <div v-if="uploadProgress > 0 && uploadProgress < 100" class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
        <div class="bg-dtube-accent h-2 rounded-full transition-all" :style="{ width: uploadProgress + '%' }" />
      </div>

      <!-- Success -->
      <div v-if="uploadSuccess" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-sm text-green-700 dark:text-green-300">
        Video uploaded successfully!
        <router-link v-if="uploadedVideoId" :to="uploadedVideoLink" class="font-medium underline ml-1">View video</router-link>
      </div>

      <!-- Error -->
      <div v-if="uploadError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-sm text-red-600 dark:text-red-300">
        {{ uploadError }}
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import { useApiClient } from "@/composables/useApiClient"
import { useAuthStore } from "@/stores/auth"

const api = useApiClient()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)

// File state
const fileInput = ref(null)
const selectedFile = ref(null)
const thumbnailFile = ref(null)
const dragOver = ref(false)

// Fields
const title = ref("")
const description = ref("")
const tags = ref(["dtube"])
const tagInput = ref("")
const uploadedVideoId = ref(null)

// Upload state
const uploading = ref(false)
const uploadProgress = ref(0)
const uploadSuccess = ref(false)
const uploadError = ref(null)

const uploadedVideoLink = computed(() => {
  return uploadedVideoId.value ? `/v/${uploadedVideoId.value}` : "/dtube"
})

function formatSize(bytes) {
  if (bytes < 1024) return bytes + " B"
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + " KB"
  return (bytes / 1048576).toFixed(1) + " MB"
}

function handleDrop(e) {
  dragOver.value = false
  const file = e.dataTransfer.files[0]
  if (file && file.type.startsWith("video/")) {
    selectedFile.value = file
  }
}

function handleFileSelect(e) {
  const file = e.target.files[0]
  if (file) selectedFile.value = file
}

function handleThumbnail(e) {
  thumbnailFile.value = e.target.files[0] || null
}

function addTag() {
  const t = tagInput.value.trim().toLowerCase().replace(/[^a-z0-9-]/g, "")
  if (t && !tags.value.includes(t) && tags.value.length < 10) {
    tags.value.push(t)
  }
  tagInput.value = ""
}

async function handleUpload() {
  if (!title.value.trim() || !selectedFile.value || uploading.value) return

  uploading.value = true
  uploadProgress.value = 0
  uploadSuccess.value = false
  uploadError.value = null

  const formData = new FormData()
  formData.append("video", selectedFile.value)
  formData.append("title", title.value)
  formData.append("description", description.value)
  formData.append("tags", JSON.stringify(tags.value))
  if (thumbnailFile.value) formData.append("thumbnail", thumbnailFile.value)

  try {
    const res = await api.post("/api/v1/video/upload", formData, {
      headers: { "Content-Type": "multipart/form-data" },
      onUploadProgress: (e) => {
        if (e.total) uploadProgress.value = Math.round((e.loaded / e.total) * 100)
      },
    })
    uploadedVideoId.value = res.data?.data?.id || res.data?.id
    uploadSuccess.value = true
    // Reset form
    selectedFile.value = null
    thumbnailFile.value = null
    title.value = ""
    description.value = ""
    tags.value = ["dtube"]
  } catch (e) {
    uploadError.value = e.response?.data?.message || e.message || "Upload failed. Please try again."
    console.error("Upload error:", e)
  } finally {
    uploading.value = false
  }
}
</script>
