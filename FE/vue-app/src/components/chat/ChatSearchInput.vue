<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    placeholder?: string
    modelValue?: string
  }>(),
  {
    placeholder: 'Search',
    modelValue: '',
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'search', value: string): void
}>()

const onInput = (event: Event) => {
  const value = (event.target as HTMLInputElement).value
  emit('update:modelValue', value)
}

const onEnter = (event: KeyboardEvent) => {
  if (event.key === 'Enter') {
    emit('search', props.modelValue ?? '')
  }
}
</script>

<template>
  <div class="px-4 py-4 border-b bg-gradient-to-b from-white to-gray-50/60">
    <div class="relative">
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75L19.5 19.5M4.5 10.5a6 6 0 1112 0 6 6 0 01-12 0z" />
        </svg>
      </span>
      <input
        :value="props.modelValue"
        type="text"
        :placeholder="props.placeholder"
        class="w-full py-2.5 pl-10 pr-3 rounded-xl border border-gray-200 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-300 transition text-sm"
        @input="onInput"
        @keyup.enter="onEnter"
      />
    </div>
  </div>
</template>
