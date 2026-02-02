<script setup lang="ts">
import { computed, useAttrs } from 'vue'

const props = defineProps<{
  url: string
  logo: string
  alt?: string
}>()

const attrs = useAttrs()
const altText = computed(() => props.alt ?? 'Social login')
</script>

<template>
  <a
    :href="props.url"
    v-bind="attrs"
    :class="['flex items-center px-4 py-2 rounded-lg shadow-sm bg-white hover:bg-gray-100', attrs.class]"
  >
    <img :src="props.logo" :alt="altText" class="h-5 w-5 mr-3" />
    <slot />
  </a>
</template>
