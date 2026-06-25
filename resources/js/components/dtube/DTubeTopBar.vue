<template>
  <header class="dtube-topbar">
    <!-- Logo -->
    <router-link to="/dtube" class="flex items-center gap-2 shrink-0">
      <img src="/nav-logo-80.webp" class="w-8 h-8 rounded-full" alt="Muhantube" />
      <span class="font-bold text-lg hidden sm:inline text-dtube-text dark:text-dtube-night-text">Muhantube</span>
    </router-link>

    <!-- Search -->
    <div class="flex-1 max-w-xl mx-auto">
      <form @submit.prevent="doSearch" class="relative">
        <input
          v-model="query"
          type="text"
          placeholder="Search videos..."
          class="w-full h-9 pl-4 pr-10 rounded-full border border-gray-300 bg-gray-100 text-sm focus:outline-none focus:border-dtube-accent focus:bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:focus:bg-gray-900"
        />
        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-dtube-accent">
          <i class="bx bx-search text-lg"></i>
        </button>
      </form>
    </div>

    <!-- User menu -->
    <div class="flex items-center gap-3 shrink-0">
      <button @click="toggleNightMode" class="text-xl text-gray-600 hover:text-dtube-accent dark:text-gray-300" title="Toggle night mode">
        <i :class="isNight ? 'bx bxs-moon' : 'bx bx-moon'"></i>
      </button>

      <template v-if="authStore.isAuthenticated">
        <router-link to="/notifications" class="text-xl text-gray-600 hover:text-dtube-accent dark:text-gray-300 relative">
          <i class="bx bx-bell"></i>
        </router-link>
        <router-link :to="`/@${authStore.user?.username}`" class="flex items-center gap-2">
          <img
            :src="authStore.user?.avatar || '/storage/avatars/default.jpg'"
            class="w-8 h-8 rounded-full border-2 border-gray-200 object-cover"
            alt=""
          />
          <span class="text-sm font-medium hidden sm:inline text-dtube-text dark:text-dtube-night-text">
            {{ authStore.user?.username }}
          </span>
        </router-link>
      </template>
      <template v-else>
        <router-link to="/dtube/login" class="px-4 py-1.5 bg-dtube-accent text-white text-sm font-medium rounded hover:opacity-90 transition-opacity">
          Sign In
        </router-link>
      </template>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();
const query = ref("");

const isNight = ref(false);

onMounted(() => {
  isNight.value = document.documentElement.classList.contains("dark");
});

function toggleNightMode() {
  isNight.value = !isNight.value;
  if (isNight.value) {
    document.documentElement.classList.add("dark");
    localStorage.theme = "dark";
  } else {
    document.documentElement.classList.remove("dark");
    localStorage.theme = "light";
  }
}

function doSearch() {
  if (query.value.trim()) {
    router.push({ path: "/dtube/search", query: { q: query.value.trim() } });
  }
}
</script>
