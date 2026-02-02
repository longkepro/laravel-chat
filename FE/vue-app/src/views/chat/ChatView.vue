<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'
import ChatLayout from '@/components/chat/ChatLayout.vue'
import ChatHeader from '@/components/chat/ChatHeader.vue'
import ChatSearchInput from '@/components/chat/ChatSearchInput.vue'
import ChatUserCard from '@/components/chat/ChatUserCard.vue'

type Message = {
  id: number
  from: number
  to: number
  text: string
  time: string
  imageUrl?: string
}

type User = {
  id: number
  username: string
  avatar?: string
  unread?: boolean
  lastMessageText?: string
}

const users = reactive<User[]>([
  { id: 2, username: 'Alice', unread: true, lastMessageText: 'Hey, are you free?' },
  { id: 3, username: 'Bob', lastMessageText: 'Let\'s catch up tomorrow' },
  { id: 4, username: 'Carol', lastMessageText: 'Meeting at 3 PM' },
])

const activeUserId = ref<number | null>(users[0]?.id ?? null)
const search = ref('')
const messageInput = ref('')
const showEmojiPicker = ref(false)
const attachment = ref<File | null>(null)
const attachmentPreview = ref('')
const messages = reactive<Message[]>([
  { id: 1, from: 2, to: 1, text: 'Hi there!', time: '10:00' },
  { id: 2, from: 1, to: 2, text: 'Hello! How are you?', time: '10:01' },
])

const filteredUsers = computed(() =>
  users.filter((u) => u.username.toLowerCase().includes(search.value.toLowerCase()))
)

const activeMessages = computed(() =>
  messages.filter((m) => m.from === activeUserId.value || m.to === activeUserId.value)
)

const selectUser = (user: User) => {
  activeUserId.value = user.id
  const target = users.find((u) => u.id === user.id)
  if (target) target.unread = false
}

const sendMessage = () => {
  if (!messageInput.value.trim() || activeUserId.value === null) return
  const newMessage: Message = {
    id: messages.length + 1,
    from: 1, // assume current user id = 1
    to: activeUserId.value,
    text: messageInput.value,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    imageUrl: attachmentPreview.value || undefined,
  }
  messages.push(newMessage)
  messageInput.value = ''
  showEmojiPicker.value = false
  attachment.value = null
  attachmentPreview.value = ''
}

const addEmoji = (emoji: { i?: string }) => {
  if (!emoji?.i) return
  messageInput.value = `${messageInput.value}${emoji.i}`
}

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  attachment.value = file
  attachmentPreview.value = URL.createObjectURL(file)
}
</script>

<template>
  <ChatLayout :user-id="1">
    <div class="bg-white/95 shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
      <ChatHeader v-model="search" @search="() => { /* hook search */ }" title="LongkeChat" />

      <div class="flex flex-row h-[80vh]">
        <aside class="flex flex-col w-[34%] min-w-[280px] border-r border-gray-100 bg-gradient-to-b from-white to-gray-50">
          <ChatSearchInput v-model="search" placeholder="Tìm kiếm cuộc trò chuyện" />

          <div class="overflow-y-auto" style="max-height: calc(80vh - 110px)">
            <ChatUserCard
              v-for="user in filteredUsers"
              :key="user.id"
              :user="user"
              :selected="user.id === activeUserId"
              @select="selectUser"
            />
          </div>
        </aside>

        <section class="flex-1 flex flex-col bg-white">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Đang chat với</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ users.find((u) => u.id === activeUserId)?.username || 'Chọn một cuộc trò chuyện' }}
              </p>
            </div>
            <div class="text-xs px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold">
              {{ activeMessages.length }} tin nhắn
            </div>
          </div>

          <div class="flex-1 px-6 py-6 overflow-y-auto space-y-3 bg-gradient-to-b from-white to-gray-50">
            <div
              v-for="message in activeMessages"
              :key="message.id"
              class="flex"
              :class="message.from === 1 ? 'justify-end' : 'justify-start'"
            >
              <div
                class="px-4 py-2.5 rounded-2xl max-w-md shadow-sm"
                :class="message.from === 1 ? 'bg-emerald-500 text-white rounded-br-sm' : 'bg-white text-gray-800 border border-gray-100 rounded-bl-sm'"
              >
                <p class="text-sm leading-relaxed">{{ message.text }}</p>
                <p class="text-[10px] opacity-70 mt-1 text-right">{{ message.time }}</p>
              </div>
            </div>

            <div v-if="!activeMessages.length" class="text-center text-gray-400 text-sm py-10">
              Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!
            </div>
          </div>

          <form class="relative flex items-center gap-3 px-6 py-4 border-t border-gray-100 bg-white" @submit.prevent="sendMessage">
            <div class="flex items-center gap-2">
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
            </div>

            <div class="flex-1 bg-gray-100 rounded-2xl px-4 py-2.5 flex items-center gap-3 focus-within:ring-2 focus-within:ring-emerald-200">
              <input
                v-model="messageInput"
                type="text"
                placeholder="Nhập tin nhắn..."
                class="w-full bg-transparent focus:outline-none text-sm"
              />
            </div>

            <div
              v-if="showEmojiPicker"
              class="absolute bottom-16 left-6 z-20 bg-white rounded-2xl shadow-xl border border-gray-100"
            >
              <EmojiPicker :native="true" :hide-search="false" @select="addEmoji" />
            </div>

            <button
              type="submit"
              class="inline-flex items-center gap-2 bg-emerald-500 text-white px-4 py-2.5 rounded-xl shadow hover:bg-emerald-600 transition"
            >
              <span>Gửi</span>
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
