<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { hasAuthToken } from '@/lib/auth-token'

const authStore = useAuthStore();

onMounted(async () => {
  if (hasAuthToken()) {
    await authStore.fetchUserIfNeeded();
  }
});
</script>

<template>
  <div class="min-h-screen bg-stone-50 text-stone-900 font-[\'Instrument_Sans\']">
    <AppHeader />
    <RouterView />
  </div>
</template>

<style scoped>
@import url('https://fonts.bunny.net/css?family=instrument-sans:400,500,600');
</style>
