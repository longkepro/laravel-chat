<script setup lang="ts">
import { computed, useAttrs } from 'vue'

type LastMessage = { message?: string | null } | null | undefined

type User = {
  id: string | number
  username: string
  avatar?: string | null
  unread?: boolean
  lastMessage?: LastMessage
  lastMessageText?: string | null
}

const props = withDefaults(
  defineProps<{
    user: User
    selected?: boolean
  }>(),
  {
    selected: false,
  }
)

const emit = defineEmits<{
  (e: 'select', user: User): void
}>()

const attrs = useAttrs()

const lastMessageText = computed(() => {
  if (props.user.lastMessage && 'message' in (props.user.lastMessage ?? {})) {
    return props.user.lastMessage?.message ?? ''
  }
  return props.user.lastMessageText ?? ''
})

const classes = computed(() => [
  'user-card flex flex-row items-center gap-3 px-4 py-3 border-b border-gray-100 hover:bg-emerald-50/60 cursor-pointer transition',
  props.selected ? 'bg-emerald-50' : '',
  attrs.class,
])

const avatarSrc = computed(
  () => props.user.avatar || 'https://source.unsplash.com/_7LbC5J-jw4/600x600'
)
</script>

<template>
  <div :class="classes" @click="emit('select', props.user)">
    <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gray-200">
      <img :src="avatarSrc" class="w-full h-full object-cover" alt="" />
    </div>

    <div class="flex-1 flex justify-between items-center gap-3">
      <div class="space-y-1">
        <div class="text-base font-semibold text-gray-900">{{ props.user.username }}</div>
        <span :class="props.user.unread ? 'font-semibold text-emerald-700' : 'text-gray-500'" class="text-sm line-clamp-1">
          {{ lastMessageText || 'No messages yet' }}
        </span>
      </div>

      <span
        v-if="props.user.unread"
        class="inline-flex w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.18)]"
      ></span>
    </div>
  </div>
</template>
