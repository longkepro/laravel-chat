<script setup lang="ts">
import { onMounted } from 'vue'

const props = withDefaults(
  defineProps<{
    userId?: number | string | null
  }>(),
  {
    userId: null,
  }
)

onMounted(() => {
  // Expose userId for scripts that still expect window.Laravel.userId
  if (typeof window !== 'undefined') {
    window.Laravel = {
      ...(window.Laravel || {}),
      userId: props.userId ?? null,
    }
  }
})
</script>

<template>
  <div class="relative min-h-screen bg-gradient-to-br from-emerald-50 via-white to-sky-50 py-6 px-4 sm:px-8 overflow-hidden">
    <video
      class="absolute inset-0 w-full h-full object-cover"
      src="/background-video/background-video.mp4"
      autoplay
      muted
      loop
      playsinline
    ></video>

    <div class="absolute inset-0 bg-white/60 backdrop-blur-sm -z-10"></div>

    <div
      id="notification"
      class="fixed top-5 right-5 max-w-sm px-4 py-3 rounded-2xl shadow-lg border bg-green-500 text-white text-sm font-medium transition-all duration-500 ease-in-out invisible opacity-0 z-20"
    ></div>

    <div class="relative z-10 max-w-6xl mx-auto">
      <slot />
    </div>
  </div>
</template>
