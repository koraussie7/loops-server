/**
 * useMinima — Minima MDS RPC wrapper composable.
 *
 * Communicates with the local Minima MDS (Mini Distributed System)
 * at http://127.0.0.1:9003 for wallet operations, and with the
 * backend API for auth and reward operations.
 */

import { useApiClient } from "@/composables/useApiClient"

const MDS_HOST = "http://127.0.0.1:9003"

/**
 * Send a command to the local Minima MDS daemon.
 */
async function mdsCommand(command, params = {}) {
  const formBody = new URLSearchParams()
  if (Object.keys(params).length > 0) {
    for (const [k, v] of Object.entries(params)) {
      formBody.append(k, String(v))
    }
  }

  const res = await fetch(MDS_HOST + "/mds/cmd/" + command, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: formBody.toString(),
  })

  if (!res.ok) throw new Error("MDS command failed: " + command)
  return res.json()
}

/**
 * Check if MDS is reachable.
 */
async function checkMdsConnection() {
  try {
    const res = await fetch(MDS_HOST + "/mds/cmd/help", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "a=b",
      signal: AbortSignal.timeout(3000),
    })
    return res.ok
  } catch {
    return false
  }
}

/**
 * Generate a new Minima address via MDS.
 */
async function newMdsAddress() {
  const result = await mdsCommand("newaddress")
  return result?.response || null
}

/**
 * Sign data with a Minima address via MDS.
 * Returns the signature.
 */
async function signWithMds(address, data) {
  const result = await mdsCommand("signdata", { data: data, address: address })
  return result?.response?.signature || result?.response || null
}

/**
 * Log in using Minima MDS.
 * Flow: generate address -> sign challenge -> verify with backend
 * Returns the JWT token.
 */
async function loginWithMinima() {
  const api = useApiClient()

  // Step 1: Get a challenge from the backend
  const challengeRes = await api.post("/api/auth/minima/challenge")
  const challenge = challengeRes.data?.challenge
  if (!challenge) throw new Error("Could not get auth challenge")

  // Step 2: Generate a new address or use existing
  const address = await newMdsAddress()
  if (!address) throw new Error("Could not generate Minima address")

  // Step 3: Sign the challenge with the address
  const signature = await signWithMds(address, challenge)
  if (!signature) throw new Error("Could not sign challenge")

  // Step 4: Verify signature with backend
  const authRes = await api.post("/api/auth/minima", {
    address: address,
    signature: signature,
    challenge: challenge,
  })

  if (authRes.data?.token) {
    return authRes.data
  }

  throw new Error("Authentication failed")
}

/**
 * Get wallet balance via MDS.
 */
async function getWalletBalance() {
  return mdsCommand("balance")
}

/**
 * Get DADA token balance from MDS.
 */
const DADA_TOKENID = "0x16FAC6DF9F8F406973A2C0C9AAF66CACEC62E2C3C96BEB6CB85A6D5F8EC557C2"

async function getDadaBalance() {
  try {
    const balance = await mdsCommand("balance")
    const coins = balance?.response || []
    for (const coin of coins) {
      if (coin.tokenid === DADA_TOKENID) {
        return coin
      }
    }
    return { token: "DADA AI", tokenid: DADA_TOKENID, confirmed: 0, sendable: 0 }
  } catch {
    return { token: "DADA AI", tokenid: DADA_TOKENID, confirmed: 0, sendable: 0 }
  }
}

/**
 * Get token info from MDS.
 */
async function getTokenInfo(tokenId) {
  return mdsCommand("token", { id: tokenId })
}

export function useMinima() {
  return {
    mdsCommand,
    checkMdsConnection,
    newMdsAddress,
    signWithMds,
    loginWithMinima,
    getWalletBalance,
    getDadaBalance,
    getTokenInfo,
    DADA_TOKENID,
  }
}
