<template>
  <div class="dtube-content max-w-md mx-auto">
    <div class="mb-6 text-center">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Sign In</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Sign in with your Minima wallet to earn rewards
      </p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
      <MinimaLogin @login="onLogin" @error="onError" />
    </div>

    <div class="mt-4 text-center">
      <p class="text-xs text-gray-400">
        Don't have Minima?
        <a
          href="https://www.minima.global/"
          target="_blank"
          rel="noopener noreferrer"
          class="text-dtube-accent hover:underline"
        >
          Download Minima
        </a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { useRouter, useRoute } from "vue-router"
import MinimaLogin from "@/components/dtube/MinimaLogin.vue"

const router = useRouter()
const route = useRoute()

function onLogin() {
  const redirect = route.query.redirect || "/dtube"
  router.push(redirect)
}

function onError(msg) {
  console.error("Login error:", msg)
}
</script>
