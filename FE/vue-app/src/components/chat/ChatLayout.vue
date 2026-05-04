<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '@/stores/chat'
import echo from '@/services/echo'
import type { MessageResponse } from '@/api/api'

const authStore = useAuthStore()
const chatStore = useChatStore()

// ─── Toast notification ────────────────────────────────────────────────────
interface Toast {
  id: number
  senderName: string
  text: string
}
const toasts = ref<Toast[]>([])
let toastCounter = 0

function showToast(senderName: string, text: string) {
  const id = ++toastCounter
  toasts.value.push({ id, senderName, text })
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }, 4000)
}

// ─── Echo listeners ───────────────────────────────────────────────────────
function setupEchoListeners() {
  const userId = authStore.user?.id
  if (!userId) return

  // 1. Lắng nghe tin nhắn đến từ server (khi không ở trong conversation đó)
  echo
    .private(`notifications.${userId}`)
    .listen('.MessageNotification', (e: { message: MessageResponse & { sender?: { username: string } } }) => {
      // Inject vào store: thêm vào messages nếu đang mở conv đó, cập nhật last_message + unread
      chatStore.injectIncomingMessage(e.message)

      // Hiện toast thông báo
      const senderName = e.message.sender?.username ?? 'Ai đó'
      const preview = e.message.message.length > 40
        ? e.message.message.slice(0, 40) + '...'
        : e.message.message
      showToast(senderName, preview)
    })

  // 2. Lắng nghe trạng thái đăng nhập / đăng xuất
  echo
    .channel('notifications')
    .listen('.UserSessionChange', (e: { message: string; type: 'login' | 'logout' }) => {
      console.log(`[Session] ${e.type}: ${e.message}`)
    })

  // 3. Presence channel theo dõi ai đang online → lưu vào store
  echo
    .join('online')
    .here((users: { id: number }[]) => {
      chatStore.onlineUserIds = new Set(users.map((u) => u.id))
    })
    .joining((user: { id: number }) => {
      chatStore.onlineUserIds = new Set([...chatStore.onlineUserIds, user.id])
    })
    .leaving((user: { id: number }) => {
      const next = new Set(chatStore.onlineUserIds)
      next.delete(user.id)
      chatStore.onlineUserIds = next
    })
    .error((err: unknown) => {
      console.error('[Echo] online channel error:', err)
    })
}

function teardownEchoListeners() {
  const userId = authStore.user?.id
  if (userId) echo.leave(`notifications.${userId}`)
  echo.leave('notifications')
  echo.leave('online')
}

const props = withDefaults(
  defineProps<{ userId?: number | string | null }>(),
  { userId: null },
)

onMounted(async () => {
  if (typeof window !== 'undefined') {
    window.Laravel = { ...(window.Laravel || {}), userId: props.userId ?? null }
  }
  await authStore.fetchUserIfNeeded()
  setupEchoListeners()
})

onUnmounted(() => {
  teardownEchoListeners()
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

    <!-- Toast notifications -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 pointer-events-none">
      <transition-group name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="flex items-start gap-3 max-w-sm px-4 py-3 rounded-2xl shadow-lg border border-white/20 bg-emerald-500/95 backdrop-blur text-white text-sm pointer-events-auto"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 mt-0.5 opacity-90">
            <path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1 min-w-0">
            <p class="font-semibold leading-tight">{{ toast.senderName }}</p>
            <p class="opacity-90 mt-0.5 line-clamp-2">{{ toast.text }}</p>
          </div>
        </div>
      </transition-group>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}
</style>
