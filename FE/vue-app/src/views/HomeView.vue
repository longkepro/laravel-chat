<script setup lang="ts">
import BrandHero from '@/components/BrandHero.vue'
import GetStartedCard from '@/components/GetStartedCard.vue'
</script>

<template>
  <div class="relative min-h-screen flex items-center justify-center px-4 sm:px-6 overflow-hidden">
    <video
      class="absolute inset-0 w-full h-full object-cover"
      src="/background-video/background-video.mp4"
      autoplay
      muted
      loop
      playsinline
    ></video>
    <div class="absolute inset-0 bg-white/25 backdrop-blur-[0.5px]"></div>
    <main class="relative max-w-5xl w-full grid gap-6 lg:grid-cols-2 items-stretch py-10">
      <GetStartedCard />
      <BrandHero />
    </main>
  </div>
</template>
