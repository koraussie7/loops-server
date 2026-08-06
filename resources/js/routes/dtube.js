/**
 * DTube-style routes — all under /dtube/ prefix
 * These are lazy-loaded and imported into the main router.
 */
export default [
  {
    path: "/dtube",
    component: () => import("@/layouts/DTubeLayout.vue"),
    children: [
      {
        path: "",
        name: "dtube.home",
        component: () => import("@/pages/dtube/HomePage.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "v/:author/:id",
        name: "dtube.watch",
        component: () => import("@/pages/dtube/WatchPage.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "upload",
        name: "dtube.upload",
        component: () => import("@/pages/dtube/UploadPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "c/:author",
        name: "dtube.channel",
        component: () => import("@/pages/dtube/ChannelPage.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "hot",
        name: "dtube.hot",
        component: () => import("@/pages/dtube/HotVideos.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "trending",
        name: "dtube.trending",
        component: () => import("@/pages/dtube/TrendingVideos.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "new",
        name: "dtube.new",
        component: () => import("@/pages/dtube/NewVideos.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "feed",
        name: "dtube.feed",
        component: () => import("@/pages/dtube/FeedPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "watch-later",
        name: "dtube.watchLater",
        component: () => import("@/pages/dtube/WatchLaterPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "history",
        name: "dtube.history",
        component: () => import("@/pages/dtube/HistoryPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "t/:tag",
        name: "dtube.tag",
        component: () => import("@/pages/dtube/TagPage.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "search",
        name: "dtube.search",
        component: () => import("@/pages/dtube/SearchPage.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "login",
        name: "dtube.login",
        component: () => import("@/pages/dtube/LoginPage.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "settings",
        name: "dtube.settings",
        component: () => import("@/pages/dtube/SettingsPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "election",
        name: "dtube.election",
        component: () => import("@/pages/dtube/ElectionPage.vue"),
        meta: { requiresAuth: false },
      },
      {
        path: "coin",
        name: "dtube.coin",
        component: () => import("@/pages/dtube/CoinPage.vue"),
        meta: { requiresAuth: false },
      },
    ],
  },
];
