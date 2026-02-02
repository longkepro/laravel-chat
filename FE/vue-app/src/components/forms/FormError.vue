<script setup lang="ts">
const props = defineProps<{ message?: string }>()
</script>

<template>
  <p v-if="props.message" class="text-xs text-red-500 font-semibold mt-1">
    {{ props.message }}
  </p>
</template>
