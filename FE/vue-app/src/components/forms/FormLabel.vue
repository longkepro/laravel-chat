<script setup lang="ts">
const props = defineProps<{ forId?: string }>()
</script>

<template>
  <label :for="props.forId" class="text-sm font-medium text-gray-700">
    <slot />
  </label>
</template>
