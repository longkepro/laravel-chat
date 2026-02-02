<script setup lang="ts">
import { computed, useAttrs } from 'vue'

const props = withDefaults(
  defineProps<{ type?: 'button' | 'submit' | 'reset' }>(),
  { type: 'button' }
)

const attrs = useAttrs()

const classes = computed(() => [
  'w-4/5 h-11 bg-green-600 text-white rounded hover:bg-green-700 cursor-pointer transition',
  attrs.class,
])
</script>

<template>
  <button :type="props.type" v-bind="attrs" :class="classes">
    <slot />
  </button>
</template>
