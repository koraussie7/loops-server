<template>
  <div class="space-y-4">
    <!-- Not connected -->
    <div v-if="!mdsConnected" class="text-center py-8">
      <div v-if="checking" class="text-sm text-gray-400 animate-pulse">Checking Minima MDS connection...</div>
      <div v-else>
        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Minima MDS not detected</p>
        <p class="text-xs text-gray-400 mb-3">Please start Minima and enable MDS</p>
        <button @click="connect" class="px-4 py-2 bg-dtube-accent text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
          Connect Minima
        </button>
      </div>
    </div>

    <!-- Connected: login flow -->
    <div v-else-if="!isLoggedIn" class="text-center py-8">
      <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </div>
      <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">Minima MDS Connected</p>
      <p class="text-xs text-gray-400 mb-4">Sign in with your Minima wallet</p>
      <button
        @click="startLogin"
        :disabled="loggingIn"
        class="px-6 py-2 bg-dtube-accent text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-40"
      >
        <span v-if="loggingIn" class="flex items-center gap-2">
          <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
          </svg>
          Signing...
        </span>
        <span v-else>Sign in with Minima</span>
      </button>
      <p v-if="loginError" class="text-xs text-red-500 mt-2">{{ loginError }}</p>
    </div>

    <!-- Logged in -->
    <div v-else class="text-center py-6">
      <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">Wallet Connected</p>
      <p class="text-xs text-gray-400 font-mono break-all">{{ minimaStore.address }}</p>
      <button @click="handleLogout" class="mt-3 text-xs text-dtube-accent hover:text-red-700">
        Disconnect
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useMinima } from "@/composables/useMinima"
import { useMinimaStore } from "@/stores/minima"
import { useAuthStore } from "@/stores/auth"

const emit = defineEmits(["login", "error"])

const { checkMdsConnection, newMdsAddress, signWithMds } = useMinima()
const minimaStore = useMinimaStore()
const authStore = useAuthStore()

const checking = ref(true)
const mdsConnected = ref(false)
const loggingIn = ref(false)
const loginError = ref(null)

const isLoggedIn = computed(() => authStore.isAuthenticated)

async function connect() {
  checking.value = true
  mdsConnected.value = false
  try {
    const ok = await checkMdsConnection()
    mdsConnected.value = ok
    minimaStore.mdsAvailable = ok
  } catch {
    mdsConnected.value = false
  } finally {
    checking.value = false
  }
}

async function startLogin() {
  loggingIn.value = true
  loginError.value = null

  try {
    // Step 1: Generate address
    const address = await newMdsAddress()
    if (!address) throw new Error("Could not generate Minima address")

    // Step 2: Get challenge from backend
    const challengeRes = await fetch("/api/auth/minima/challenge", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
    })
    const challengeData = await challengeRes.json()
    const challenge = challengeData.challenge
    if (!challenge) throw new Error("Could not get auth challenge")

    // Step 3: Sign challenge with MDS
    const formBody = new URLSearchParams()
    formBody.append("data", challenge)
    formBody.append("address", address)

    const sigRes = await fetch("http://127.0.0.1:9003/mds/cmd/signdata", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formBody.toString(),
    })
    const sigData = await sigRes.json()
    const signature = sigData?.response?.signature || sigData?.response
    if (!signature) throw new Error("Could not sign challenge")

    // Step 4: Verify with backend
    const authRes = await fetch("/api/auth/minima", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ address, signature, challenge }),
    })
    const authData = await authRes.json()

    if (authRes.ok && authData.token) {
      await minimaStore.login(address, authData.token)
      emit("login", authData)
    } else {
      throw new Error(authData.message || "Authentication failed")
    }
  } catch (e) {
    loginError.value = e.message
    emit("error", e.message)
  } finally {
    loggingIn.value = false
  }
}

function handleLogout() {
  minimaStore.logout()
}

onMounted(connect)
</script>
