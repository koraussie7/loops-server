<template>
  <div class="max-w-2xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Settings</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">DTube playback preferences</p>
    </div>

    <div class="space-y-6">
      <!-- Autoplay -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Autoplay</h3>
            <p class="text-xs text-gray-400 mt-0.5">Automatically play the next video</p>
          </div>
          <button
            @click="toggle('autoplay')"
            class="relative w-10 h-5 rounded-full transition-colors"
            :class="settings.autoplay ? 'bg-dtube-accent' : 'bg-gray-300 dark:bg-gray-600'"
          >
            <span
              class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform shadow-sm"
              :class="settings.autoplay ? 'translate-x-5' : ''"
            />
          </button>
        </div>
      </div>

      <!-- Default quality -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Default Quality</h3>
        <div class="flex gap-2">
          <button
            v-for="q in qualities"
            :key="q.value"
            @click="settings.quality = q.value; save()"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
            :class="settings.quality === q.value
              ? 'bg-dtube-accent text-white'
              : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
          >
            {{ q.label }}
          </button>
        </div>
      </div>

      <!-- Theme -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Theme</h3>
        <div class="flex gap-2">
          <button
            v-for="t in themes"
            :key="t.value"
            @click="setTheme(t.value)"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
            :class="settings.theme === t.value
              ? 'bg-dtube-accent text-white'
              : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
          >
            {{ t.label }}
          </button>
        </div>
      </div>

      <!-- Minima wallet status -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Minima Wallet</h3>
            <p class="text-xs text-gray-400 mt-0.5">
              {{ minimaStore.isConnected ? "Connected: " + minimaStore.address : "Not connected" }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <span
              class="w-2 h-2 rounded-full"
              :class="minimaStore.isConnected ? 'bg-green-500' : 'bg-gray-400'"
            />
            <router-link
              v-if="!minimaStore.isConnected"
              to="/dtube/login"
              class="text-xs text-dtube-accent hover:underline"
            >
              Connect
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted } from "vue"
import { useMinimaStore } from "@/stores/minima"

const minimaStore = useMinimaStore()

const STORAGE_KEY = "dtube_settings"

const qualities = [
  { label: "Auto", value: "auto" },
  { label: "480p", value: "480" },
  { label: "720p", value: "720" },
  { label: "1080p", value: "1080" },
]

const themes = [
  { label: "System", value: "system" },
  { label: "Light", value: "light" },
  { label: "Dark", value: "dark" },
]

const settings = reactive({
  autoplay: true,
  quality: "auto",
  theme: "system",
})

function load() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw) {
      const saved = JSON.parse(raw)
      Object.assign(settings, saved)
    }
  } catch {}
}

function save() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...settings }))
}

function toggle(key) {
  settings[key] = !settings[key]
  save()
}

function setTheme(value) {
  settings.theme = value
  applyTheme(value)
  save()
}

function applyTheme(theme) {
  if (theme === "dark") {
    document.documentElement.classList.add("dark")
  } else if (theme === "light") {
    document.documentElement.classList.remove("dark")
  } else {
    // system
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches
    document.documentElement.classList.toggle("dark", prefersDark)
  }
}

// Listen for system theme changes
const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)")
mediaQuery.addEventListener("change", () => {
  if (settings.theme === "system") applyTheme("system")
})

onMounted(() => {
  load()
  applyTheme(settings.theme)
})
</script>
