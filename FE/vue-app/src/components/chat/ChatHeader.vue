<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    title?: string
    avatarText?: string
    modelValue?: string
  }>(),
  {
    title: 'GoingChat',
    avatarText: 'RA',
    modelValue: '',
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'avatar-click'): void
}>()

const onInput = (event: Event) => {
  const value = (event.target as HTMLInputElement).value
  emit('update:modelValue', value)
}

</script>

<template>
  <header class="flex items-center gap-4 px-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-t-2xl shadow-sm">
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center shadow-inner">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-9 4h12.5a2.5 2.5 0 002.5-2.5V7A2.5 2.5 0 0019.5 4H6.5A2.5 2.5 0 004 6.5v11L7 16z" />
        </svg>
      </div>
      <div>
        <p class="text-xs uppercase tracking-[0.2em] text-white/70">Chat</p>
        <p class="text-lg font-semibold">{{ props.title }}</p>
      </div>
    </div>

    <div class="flex-1 flex justify-center">
      <div class="w-full max-w-xl relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/70">
        </span>
      </div>
    </div>

    <button
      type="button"
      class="h-11 w-11 bg-white/20 border border-white/30 rounded-xl text-white font-semibold flex items-center justify-center shadow hover:bg-white/30 transition"
      @click="emit('avatar-click')"
      aria-label="Profile"
    >
      {{ props.avatarText }}
    </button>
  </header>
</template>
