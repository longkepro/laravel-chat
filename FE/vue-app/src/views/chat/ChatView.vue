<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'
import ChatLayout from '@/components/chat/ChatLayout.vue'
import ChatHeader from '@/components/chat/ChatHeader.vue'
import ChatSearchInput from '@/components/chat/ChatSearchInput.vue'
import ChatUserCard from '@/components/chat/ChatUserCard.vue'
import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '@/stores/chat'
import type { ConversationItem } from '@/api/api'

const authStore = useAuthStore()
const chatStore = useChatStore()

const search = ref('')
const userSearchInput = ref('')
const messageInput = ref('')
const showEmojiPicker = ref(false)
const messagesContainer = ref<HTMLElement | null>(null)
const lastReportedReadId = ref<number | null>(null)

const selfReadMessageId = computed(() => {
  const conv = chatStore.activeConversation
  const userId = authStore.user?.id
  if (!conv || !userId) return null

  if (conv.user1_id === userId) return conv.last_message_id1 ?? null
  if (conv.user2_id === userId) return conv.last_message_id2 ?? null
  return null
})

const otherReadMessageId = computed(() => {
  const conv = chatStore.activeConversation
  const userId = authStore.user?.id
  if (!conv || !userId) return null

  if (conv.user1_id === userId) return conv.last_message_id2 ?? null
  if (conv.user2_id === userId) return conv.last_message_id1 ?? null
  return null
})

const otherUserAvatar = computed(() => chatStore.activeConversation?.receiver?.avatar ?? null)
const otherUserInitial = computed(
  () => chatStore.activeConversation?.receiver?.username?.slice(0, 1).toUpperCase() ?? '?',
)

// Conversations hiển thị (filter theo search) 
const filteredConversations = computed(() =>
  chatStore.conversations.filter((c) =>
    c.receiver?.username.toLowerCase().includes(search.value.toLowerCase()),
  ),
)

// Xử lý gõ tìm kiếm người dùng
function onUserSearchInput(val: string) {
  userSearchInput.value = val
  chatStore.searchUsers(val)
}

// Mở/đóng panel tìm người dùng
function toggleUserSearch() {
  chatStore.showUserSearch = !chatStore.showUserSearch
  if (!chatStore.showUserSearch) {
    userSearchInput.value = ''
    chatStore.userSearchResults = []
  }
}

// Chọn conversation
async function selectConversation(conv: ConversationItem) {
  await chatStore.selectConversation(conv)
  await scrollToBottom()
  await reportVisibleRead()
}

// Gửi tin nhắn
async function sendMessage() {
  if (!messageInput.value.trim()) return
  try {
    await chatStore.sendMessage(messageInput.value.trim())
    messageInput.value = ''
    showEmojiPicker.value = false
    await scrollToBottom()
  } catch {
    // lỗi đã được log trong store
  }
}

// Emoji
function addEmoji(emoji: { i?: string }) {
  if (emoji?.i) messageInput.value += emoji.i
}

// Scroll
async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
  await reportVisibleRead()
}

function getMaxVisibleMessageId() {
  const container = messagesContainer.value
  if (!container) return null

  const containerRect = container.getBoundingClientRect()
  const nodes = container.querySelectorAll<HTMLElement>('[data-message-id]')

  let maxId: number | null = null
  nodes.forEach((node) => {
    const rect = node.getBoundingClientRect()
    const isVisible = rect.bottom > containerRect.top && rect.top < containerRect.bottom
    if (!isVisible) return

    const id = Number(node.dataset.messageId)
    if (!Number.isFinite(id)) return
    maxId = maxId === null ? id : Math.max(maxId, id)
  })

  return maxId
}

async function reportVisibleRead() {
  const conv = chatStore.activeConversation
  if (!conv || !authStore.user) return

  const maxVisibleId = getMaxVisibleMessageId()
  if (!maxVisibleId) return

  const currentRead = selfReadMessageId.value ?? 0
  const lastSent = lastReportedReadId.value ?? 0

  if (maxVisibleId <= currentRead || maxVisibleId <= lastSent) return

  lastReportedReadId.value = maxVisibleId
  await chatStore.markMessageRead(conv.conversation_id, maxVisibleId)
}

// Kéo lên đầu để load tin nhắn cũ hơn
async function onScroll() {
  if (!messagesContainer.value) return
  if (messagesContainer.value.scrollTop === 0 && chatStore.hasOlderMessages) {
    const prevHeight = messagesContainer.value.scrollHeight
    await chatStore.loadOlderMessages()
    await nextTick()
    // Giữ vị trí scroll sau khi prepend tin cũ
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight - prevHeight
  }

  await reportVisibleRead()
}

// Format thời gian từ created_at của BE
function formatTime(dateStr: string) {
  return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

// Auto scroll khi messages thay đổi
watch(
  () => chatStore.messages.length,
  async (newLen, oldLen) => {
    // Scroll xuống khi có tin mới (bất kể ai gửi), không scroll khi load older
    if (newLen > oldLen) {
      await scrollToBottom()
    }
    await reportVisibleRead()
  },
)

watch(
  () => chatStore.activeConversation?.conversation_id,
  () => {
    lastReportedReadId.value = null
  },
)

// Lifecycle 
onMounted(async () => {
  await authStore.fetchUserIfNeeded()
  await chatStore.loadConversations()
  // Tự động chọn conversation đầu tiên nếu có
  const firstConversation = chatStore.conversations[0]
  if (firstConversation) {
    await selectConversation(firstConversation)
  }
})

onUnmounted(() => {
  chatStore.leaveCurrentChannel()
})
</script>

<template>
  <ChatLayout :user-id="authStore.user?.id">
    <div class="bg-white/95 shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
      <ChatHeader title="LongkeChat" :avatar-text="authStore.user?.username?.slice(0, 2).toUpperCase() ?? '?'" />

      <div class="flex flex-row h-[80vh]">
        <!-- Sidebar: danh sách conversations -->
        <aside class="flex flex-col w-[34%] min-w-[280px] border-r border-gray-100 bg-gradient-to-b from-white to-gray-50">

          <!-- Thanh tìm kiếm + nút tạo chat mới -->
          <div class="flex items-center gap-2 px-4 pt-4 pb-2">
            <div class="flex-1">
              <ChatSearchInput v-model="search" placeholder="Tìm kiếm cuộc trò chuyện" />
            </div>
            <button
              type="button"
              class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl transition"
              :class="chatStore.showUserSearch ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-emerald-50 hover:text-emerald-600'"
              title="Chat với người mới"
              @click="toggleUserSearch"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </button>
          </div>

          <!-- Panel tìm kiếm người dùng mới -->
          <div v-if="chatStore.showUserSearch" class="px-4 pb-3 border-b border-gray-100">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75L19.5 19.5M4.5 10.5a6 6 0 1112 0 6 6 0 01-12 0z" />
                </svg>
              </span>
              <input
                :value="userSearchInput"
                type="text"
                placeholder="Tìm người dùng..."
                class="w-full py-2 pl-9 pr-3 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-300 transition"
                autofocus
                @input="onUserSearchInput(($event.target as HTMLInputElement).value)"
              />
            </div>

            <!-- Kết quả tìm kiếm -->
            <div class="mt-2 max-h-48 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-sm">
              <!-- Loading -->
              <div v-if="chatStore.searchingUsers" class="flex items-center justify-center py-4 text-gray-400 text-sm gap-2">
                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Đang tìm...
              </div>

              <!-- Không có kết quả -->
              <div
                v-else-if="userSearchInput && !chatStore.searchingUsers && chatStore.userSearchResults.length === 0"
                class="py-4 text-center text-gray-400 text-sm"
              >
                Không tìm thấy người dùng
              </div>

              <!-- Placeholder khi chưa gõ -->
              <div
                v-else-if="!userSearchInput"
                class="py-4 text-center text-gray-400 text-sm"
              >
                Gõ tên để tìm kiếm
              </div>

              <!-- Danh sách kết quả -->
              <div
                v-for="user in chatStore.userSearchResults"
                :key="user.id"
                class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-emerald-50 transition"
                @click="chatStore.startNewChat(user)"
              >
                <div class="w-9 h-9 rounded-xl overflow-hidden bg-gray-200 flex-shrink-0">
                  <img
                    v-if="user.avatar"
                    :src="user.avatar"
                    class="w-full h-full object-cover"
                    alt=""
                  />
                  <div v-else class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-700 text-sm font-bold">
                    {{ user.username.slice(0, 1).toUpperCase() }}
                  </div>
                </div>
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ user.username }}</p>
                  <p v-if="user.name" class="text-xs text-gray-500">{{ user.name }}</p>
                </div>
                <div class="ml-auto">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4 text-emerald-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Loading conversations -->
          <div v-if="chatStore.loadingConversations" class="flex items-center justify-center py-10 text-gray-400 text-sm">
            Đang tải...
          </div>

          <div v-else class="overflow-y-auto" style="max-height: calc(80vh - 110px)">
            <!-- Không có conversation nào -->
            <div v-if="filteredConversations.length === 0" class="text-center text-gray-400 text-sm py-10 px-4">
              Chưa có cuộc trò chuyện nào
            </div>

            <ChatUserCard
              v-for="conv in filteredConversations"
              :key="conv.conversation_id"
              :user="{
                id: conv.receiver?.id ?? 0,
                username: conv.receiver?.username ?? 'Unknown',
                avatar: conv.receiver?.avatar ?? undefined,
                unread: conv.last_read_message_id !== conv.last_message?.id && conv.last_message !== null,
                lastMessage: conv.last_message,
              }"
              :selected="chatStore.activeConversation?.conversation_id === conv.conversation_id"
              :is-online="chatStore.onlineUserIds.has(conv.receiver?.id ?? 0)"
              @select="selectConversation(conv)"
            />
          </div>
        </aside>

        <!-- Main chat area -->
        <section class="flex-1 flex flex-col bg-white">

          <!-- Header của conversation đang chọn -->
          <div class="px-6 py-4 border-b border-gray-100 flex items-center">
            <div>
              <p class="text-sm text-gray-500">Đang chat với</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ chatStore.activeConversation?.receiver?.username ?? 'Chọn một cuộc trò chuyện' }}
              </p>
            </div>
          </div>

          <!-- Khu vực tin nhắn -->
          <div
            ref="messagesContainer"
            class="flex-1 px-6 py-6 overflow-y-auto space-y-3 bg-gradient-to-b from-white to-gray-50"
            @scroll="onScroll"
          >
            <!-- Đang load tin nhắn cũ hơn -->
            <div v-if="chatStore.loadingMessages" class="text-center text-gray-400 text-xs py-2">
              Đang tải...
            </div>

            <!-- Danh sách tin nhắn -->
<!-- Danh sách tin nhắn -->
            <div
              v-for="message in chatStore.messages"
              :key="message.id"
              :data-message-id="message.id"
              class="flex flex-col space-y-1"
            >
              <!-- Dòng 1: Khung chứa bong bóng tin nhắn -->
              <div 
                class="flex w-full"
                :class="message.sender_id === authStore.user?.id ? 'justify-end' : 'justify-start'"
              >
                <div class="max-w-md">
                  <div
                    class="px-4 py-2.5 rounded-2xl shadow-sm"
                    :class="message.sender_id === authStore.user?.id
                      ? 'bg-emerald-500 text-white rounded-br-sm'
                      : 'bg-white text-gray-800 border border-gray-100 rounded-bl-sm'"
                  >
                    <!-- Ảnh đính kèm -->
                    <img
                      v-if="message.attachment"
                      :src="message.attachment"
                      class="rounded-xl mb-2 max-w-[200px]"
                      alt="attachment"
                    />
                    <p class="text-sm leading-relaxed">{{ message.message }}</p>
                    <p class="text-[10px] opacity-70 mt-1 text-right">{{ formatTime(message.created_at) }}</p>
                  </div>
                </div>
              </div>

              <!-- Dòng 2: Khung chứa Avatar Read Receipt (Đã tách khỏi max-w-md) -->
              <div
                v-if="otherReadMessageId && message.id === otherReadMessageId"
                class="flex justify-end w-full"
              >
                <div class="w-5 h-5 rounded-full overflow-hidden bg-gray-200 border border-white shadow">
                  <img
                    v-if="otherUserAvatar"
                    :src="otherUserAvatar"
                    class="w-full h-full object-cover"
                    alt=""
                  />
                  <div
                    v-else
                    class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-700 text-[10px] font-bold"
                  >
                    {{ otherUserInitial }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Chưa có tin nhắn -->
            <div v-if="!chatStore.loadingMessages && chatStore.messages.length === 0 && chatStore.activeConversation" class="text-center text-gray-400 text-sm py-10">
              Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!
            </div>

            <!-- Chưa chọn conversation -->
            <div v-if="!chatStore.activeConversation" class="text-center text-gray-400 text-sm py-10">
              Chọn một cuộc trò chuyện để bắt đầu
            </div>
          </div>

          <!-- Form gửi tin nhắn -->
          <form
            class="relative flex items-center gap-3 px-6 py-4 border-t border-gray-100 bg-white"
            @submit.prevent="sendMessage"
          >
            <!-- Emoji button -->
            <button
              type="button"
              class="p-2 rounded-xl bg-gray-100 text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition"
              @click="showEmojiPicker = !showEmojiPicker"
              aria-label="Chọn emoji"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path d="M12 2.25a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5ZM9 10.5a1.125 1.125 0 1 1 0-2.25 1.125 1.125 0 0 1 0 2.25Zm6 0a1.125 1.125 0 1 1 0-2.25 1.125 1.125 0 0 1 0 2.25Zm.506 4.284a4.5 4.5 0 0 1-8.013 0 .75.75 0 0 1 1.314-.732 3 3 0 0 0 5.385 0 .75.75 0 1 1 1.314.732Z" />
              </svg>
            </button>

            <!-- Input -->
            <div class="flex-1 bg-gray-100 rounded-2xl px-4 py-2.5 flex items-center gap-3 focus-within:ring-2 focus-within:ring-emerald-200">
              <input
                v-model="messageInput"
                type="text"
                placeholder="Nhập tin nhắn..."
                class="w-full bg-transparent focus:outline-none text-sm"
                :disabled="!chatStore.activeConversation || chatStore.sendingMessage"
              />
            </div>

            <!-- Emoji Picker -->
            <div
              v-if="showEmojiPicker"
              class="absolute bottom-16 left-6 z-20 bg-white rounded-2xl shadow-xl border border-gray-100"
            >
              <EmojiPicker :native="true" :hide-search="false" @select="addEmoji" />
            </div>

            <!-- Nút Gửi -->
            <button
              type="submit"
              :disabled="!chatStore.activeConversation || chatStore.sendingMessage || !messageInput.trim()"
              class="inline-flex items-center gap-2 bg-emerald-500 text-white px-4 py-2.5 rounded-xl shadow hover:bg-emerald-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span>{{ chatStore.sendingMessage ? 'Đang gửi...' : 'Gửi' }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12l15-8.25L15 12l4.5 8.25L4.5 12z" />
              </svg>
            </button>
          </form>
        </section>
      </div>
    </div>
  </ChatLayout>
</template>
