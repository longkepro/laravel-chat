<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore();

onMounted(async () => {
  await authStore.fetchUser();
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
