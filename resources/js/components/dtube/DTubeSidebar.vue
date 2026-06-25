<template>
  <aside class="dtube-sidebar">
    <nav class="py-4">
      <router-link
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        class="dtube-nav-item"
        :class="{ active: isActive(item.path) }"
      >
        <i :class="item.icon" class="text-xl w-6 text-center"></i>
        <span>{{ item.label }}</span>
      </router-link>
    </nav>
  </aside>
</template>

<script setup>
import { useRoute } from "vue-router";

const route = useRoute();

const navItems = [
  { path: "/dtube",           label: "Home",         icon: "bx bx-home" },
  { path: "/dtube/upload",    label: "Upload",       icon: "bx bx-upload" },
  { path: "/dtube/feed",      label: "My Feed",      icon: "bx bx-rss" },
  { path: "/dtube/hot",       label: "Hot Videos",   icon: "bx bx-fire" },
  { path: "/dtube/trending",  label: "Trending",     icon: "bx bx-trophy" },
  { path: "/dtube/new",       label: "New Videos",   icon: "bx bx-hourglass" },
  { path: "/dtube/watch-later", label: "Watch Later", icon: "bx bx-time" },
  { path: "/dtube/history",   label: "History",      icon: "bx bx-history" },
  { path: "/dtube/login",     label: "Sign In",      icon: "bx bx-log-in" },
  { path: "/dtube/election",  label: "Elections",    icon: "bx bx-group" },
  { path: "/dtube/coin",      label: "DADA Coin",    icon: "bx bx-coin-stack" },
  { path: "/dtube/settings",  label: "Settings",     icon: "bx bx-cog" },
];

function isActive(path) {
  if (path === "/dtube") return route.path === "/dtube";
  return route.path.startsWith(path);
}
</script>
