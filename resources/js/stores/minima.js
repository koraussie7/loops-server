import { defineStore } from "pinia"
import { useApiClient } from "@/composables/useApiClient"

export const useMinimaStore = defineStore("minima", {
  state: () => ({
    address: null,
    connected: false,
    balance: null,
    dadaBalance: null,
    mdsAvailable: false,
    mdsChecking: false,
    loading: false,
    error: null,
  }),

  getters: {
    isConnected: (state) => state.connected && !!state.address,
    isMdsReady: (state) => state.mdsAvailable,
    formattedDadaBalance: (state) => {
      if (!state.dadaBalance) return "0"
      const amt = parseInt(state.dadaBalance.sendable || state.dadaBalance.confirmed || 0)
      // DADA tokens have 0 decimals (MINI)
      return amt.toLocaleString()
    },
  },

  actions: {
    async checkMdsConnection() {
      this.mdsChecking = true
      try {
        const res = await fetch("http://127.0.0.1:9003/mds/cmd/help", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "a=b",
          signal: AbortSignal.timeout(3000),
        })
        this.mdsAvailable = res.ok
      } catch {
        this.mdsAvailable = false
      } finally {
        this.mdsChecking = false
      }
    },

    async refreshBalance() {
      if (!this.mdsAvailable) return
      try {
        const res = await fetch("http://127.0.0.1:9003/mds/cmd/balance", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
        })
        const data = await res.json()
        this.balance = data.response || null

        const DADA_TOKENID = "0x16FAC6DF9F8F406973A2C0C9AAF66CACEC62E2C3C96BEB6CB85A6D5F8EC557C2"
        const coins = data.response || []
        this.dadaBalance = coins.find((c) => c.tokenid === DADA_TOKENID) || null
      } catch {
        // silently fail
      }
    },

    async login(address, token) {
      this.address = address
      this.connected = true
      // Reload page to pick up new auth state if needed
      if (window.location.pathname.startsWith("/dtube")) {
        window.location.reload()
      }
    },

    async logout() {
      const api = useApiClient()
      try {
        await api.post("/logout")
      } catch {}
      this.address = null
      this.connected = false
      this.balance = null
      this.dadaBalance = null
      window.location.reload()
    },

    setError(msg) {
      this.error = msg
    },

    clearError() {
      this.error = null
    },
  },
})
