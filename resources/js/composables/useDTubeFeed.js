/**
 * useDTubeFeed — Hive blockchain data fetching for DTube videos.
 *
 * Uses public Hive API JSON-RPC endpoints to fetch DTube-tagged posts
 * sorted by trending, created (new), or hot.
 *
 * Each post"s JSON metadata is parsed for video content (thumbnail, source).
 */

const HIVE_API = "https://api.hive.blog"
const DTB_TAG = "dtube"
const PAGE_SIZE = 50

/**
 * Unified fetch from condenser_api.
 */
async function callHive(method, params) {
  const res = await fetch(HIVE_API, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      jsonrpc: "2.0",
      id: 1,
      method,
      params,
    }),
  })
  const body = await res.json()
  return body.result ?? []
}

/**
 * Parse a Hive post into a DTube video object.
 */
function parseDTubePost(post) {
  let meta = {}
  try {
    meta = JSON.parse(post.json_metadata)
  } catch {}

  // DTube v2 video metadata
  const video = meta?.video?.content ?? {}
  const info = meta?.video?.info ?? {}

  // Legacy DTube v1 format (older posts)
  const legacyVideo = post.json_metadata ? JSON.parse(post.json_metadata || "{}") : {}

  // Build thumbnail URL — prefer explicit thumb, then IPFS gateway, then fallback
  const thumb =
    video.thumb ||
    info.thumb ||
    (video.thumbUrl
      ? `https://ipfs-3speak.b-cdn.net/ipfs/${video.thumbUrl}`
      : null) ||
    (legacyVideo.thumbnail
      ? `https://ipfs-3speak.b-cdn.net/ipfs/${legacyVideo.thumbnail}`
      : null) ||
    `https://picsum.photos/seed/${post.permlink}/320/180`

  // Extract video source URL
  const videoSrc =
    video.videohash
      ? `https://ipfs-3speak.b-cdn.net/ipfs/${video.videohash}`
      : video.src640 || video.src ||
    (legacyVideo.content?.videohash
      ? `https://ipfs-3speak.b-cdn.net/ipfs/${legacyVideo.content.videohash}`
      : null)

  const duration = info.duration || video.duration || legacyVideo.duration || null
  const title = post.title || info.title || "Untitled"
  const description = post.body ? post.body.slice(0, 300) : ""

  return {
    id: `${post.author}/${post.permlink}`,
    permlink: post.permlink,
    author: post.author,
    title,
    description,
    thumbnail: thumb,
    videoSrc,
    duration,
    created: post.created,
    payout: post.total_payout_value || "0.000 HBD",
    net_votes: post.net_votes || 0,
    children: post.children || 0,
    tags: Array.isArray(meta.tags) ? meta.tags : [],
    category: post.category || DTB_TAG,
    pending_payout: post.pending_payout_value || "0.000 HBD",
    author_reputation: post.author_reputation,
  }
}

export function useDTubeFeed() {
  /**
   * Fetch trending DTube videos.
   */
  async function fetchTrending({ limit = PAGE_SIZE, start_author = null, start_permlink = null } = {}) {
    const params = [{ tag: DTB_TAG, limit }]
    if (start_author && start_permlink) {
      params[0].start_author = start_author
      params[0].start_permlink = start_permlink
    }
    const posts = await callHive("condenser_api.get_discussions_by_trending", params)
    return posts.map(parseDTubePost).filter((p) => p.title !== "Untitled" || p.videoSrc)
  }

  /**
   * Fetch newest DTube videos.
   */
  async function fetchNew({ limit = PAGE_SIZE, start_author = null, start_permlink = null } = {}) {
    const params = [{ tag: DTB_TAG, limit }]
    if (start_author && start_permlink) {
      params[0].start_author = start_author
      params[0].start_permlink = start_permlink
    }
    const posts = await callHive("condenser_api.get_discussions_by_created", params)
    return posts.map(parseDTubePost).filter((p) => p.title !== "Untitled" || p.videoSrc)
  }

  /**
   * Fetch hot DTube videos.
   */
  async function fetchHot({ limit = PAGE_SIZE, start_author = null, start_permlink = null } = {}) {
    const params = [{ tag: DTB_TAG, limit }]
    if (start_author && start_permlink) {
      params[0].start_author = start_author
      params[0].start_permlink = start_permlink
    }
    const posts = await callHive("condenser_api.get_discussions_by_hot", params)
    return posts.map(parseDTubePost).filter((p) => p.title !== "Untitled" || p.videoSrc)
  }

  /**
   * Fetch posts by tag (could be dtube + other tags).
   */
  async function fetchByTag(tag, { limit = PAGE_SIZE, sort = "trending" } = {}) {
    const method =
      sort === "new"
        ? "condenser_api.get_discussions_by_created"
        : sort === "hot"
          ? "condenser_api.get_discussions_by_hot"
          : "condenser_api.get_discussions_by_trending"
    const posts = await callHive(method, [{ tag, limit }])
    return posts.map(parseDTubePost).filter((p) => p.title !== "Untitled" || p.videoSrc)
  }

  /**
   * Search DTube videos by query (uses Hive"s search API or a simple client-side filter).
   * For now, fetches trending and filters client-side.
   */
  async function searchVideos(query, { limit = 50 } = {}) {
    if (!query || query.trim().length < 2) return []
    const q = query.toLowerCase()
    const posts = await callHive("condenser_api.get_discussions_by_trending", [{ tag: DTB_TAG, limit }])
    const results = posts.map(parseDTubePost).filter((p) => {
      return (
        p.title.toLowerCase().includes(q) ||
        p.author.toLowerCase().includes(q) ||
        p.tags.some((t) => t.toLowerCase().includes(q)) ||
        p.description.toLowerCase().includes(q)
      )
    })
    return results.slice(0, 30)
  }

  return {
    fetchTrending,
    fetchNew,
    fetchHot,
    fetchByTag,
    searchVideos,
  }
}
