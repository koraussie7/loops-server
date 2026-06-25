<template>
  <div class="min-h-screen bg-white dark:bg-black">
    <DTubeTopBar />
    <DTubeSidebar />
    <main class="dtube-content">
      <router-view v-slot="{ Component }">
        <Suspense>
          <template #default>
            <component :is="Component" />
          </template>
          <template #fallback>
            <div class="flex items-center justify-center py-20">
              <div class="w-8 h-8 border-4 border-dtube-accent border-t-transparent rounded-full animate-spin"></div>
            </div>
          </template>
        </Suspense>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import DTubeTopBar from "@/components/dtube/DTubeTopBar.vue";
import DTubeSidebar from "@/components/dtube/DTubeSidebar.vue";
</script>
