import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/api'
import type { ConversationItem, MessageResponse, SearchUserItem, MarkReadResponse } from '@/api/api'
import echo from '@/services/echo'
import { useAuthStore } from '@/stores/auth'

export const useChatStore = defineStore('chat', () => {
  const conversations = ref<ConversationItem[]>([])
  const messages = ref<MessageResponse[]>([])
  const activeConversation = ref<ConversationItem | null>(null)

  const loadingConversations = ref(false)
  const loadingMessages = ref(false)
  const sendingMessage = ref(false)

  // Có thêm tin nhắn cũ hơn để load không
  const hasOlderMessages = ref(false)

  // Tìm kiếm người dùng để mở chat mới
  const userSearchResults = ref<SearchUserItem[]>([])
  const searchingUsers = ref(false)
  const showUserSearch = ref(false)

  // Tập hợp ID các user đang online (từ presence channel 'online')
  const onlineUserIds = ref<Set<number>>(new Set())

  type ReadReceiptPayload = {
    conversation_id: number
    reader_id?: number
    last_message_id1: number | null
    last_message_id2: number | null
  }

  // ID channel đang lắng nghe, dùng để leave khi chuyển conversation
  let listeningConversationId: number | null = null

  // 1. Tải danh sách cuộc trò chuyện
  async function loadConversations() {
    loadingConversations.value = true
    try {
      const res = await api.getConversations()
      conversations.value = res.data.data
    } catch (e) {
      console.error('[Chat] loadConversations error:', e)
    } finally {
      loadingConversations.value = false
    }
  }

  // 2. Chọn conversation và tải 20 tin nhắn đầu tiên
  async function selectConversation(conversation: ConversationItem) {
    // Rời channel cũ trước khi join cái mới
    leaveCurrentChannel()

    activeConversation.value = conversation
    messages.value = []
    hasOlderMessages.value = false

    // Đánh dấu đã đọc khi mở conversation
    markAsRead(conversation.conversation_id)

    await loadInitialMessages(conversation.conversation_id)
    listenToConversation(conversation.conversation_id)
  }

  // 3. Tải tin nhắn ban đầu (20 tin mới nhất)
  async function loadInitialMessages(conversationId: number) {
    loadingMessages.value = true
    try {
      // Dùng getOlderMessages với MessageID=999999999 để lấy 20 tin nhắn mới nhất
      const res = await api.getLatestMessages(conversationId)
      messages.value = res.data
      hasOlderMessages.value = res.data.length === 20
    } catch (e) {
      console.error('[Chat] loadInitialMessages error:', e)
    } finally {
      loadingMessages.value = false
    }
  }

  // 4. Load thêm tin nhắn cũ hơn (kéo lên trên)
  async function loadOlderMessages() {
    if (!activeConversation.value || !hasOlderMessages.value || messages.value.length === 0) return

    const oldestMessage = messages.value[0]
    if (!oldestMessage) return
    const oldestId = oldestMessage.id
    loadingMessages.value = true
    try {
      const res = await api.getOlderMessages(activeConversation.value.conversation_id, oldestId)
      messages.value = [...res.data, ...messages.value]
      hasOlderMessages.value = res.data.length === 20
    } catch (e) {
      console.error('[Chat] loadOlderMessages error:', e)
    } finally {
      loadingMessages.value = false
    }
  }

  // 5. Gửi tin nhắn
  async function sendMessage(text: string, attachment?: string) {
    const authStore = useAuthStore()
    if (!activeConversation.value || !authStore.user) return

    sendingMessage.value = true
    try {
      const res = await api.sendMessage({
        sender_id: authStore.user.id,
        conversation_id: activeConversation.value.conversation_id,
        message: text,
        ...(attachment ? { attachment } : {}),
      })

      // Thêm tin nhắn vừa gửi vào cuối danh sách ngay lập tức (optimistic update)
      messages.value.push(res.data.message)

      // Cập nhật last_message của conversation trong danh sách
      updateLastMessage(activeConversation.value.conversation_id, res.data.message)
      // Vừa gửi → đã đọc đến tin mới nhất
      markAsRead(activeConversation.value.conversation_id)
    } catch (e) {
      console.error('[Chat] sendMessage error:', e)
      throw e
    } finally {
      sendingMessage.value = false
    }
  }

  // 6. Tạo conversation mới và gửi tin nhắn đầu tiên
  async function createConversation(receiverId: number, message: string) {
    const authStore = useAuthStore()
    if (!authStore.user) return

    try {
      await api.createConversation({
        sender_id: authStore.user.id,
        receiver_id: receiverId,
        message,
      })
      // Reload danh sách sau khi tạo xong
      await loadConversations()
    } catch (e) {
      console.error('[Chat] createConversation error:', e)
      throw e
    }
  }

  // 7. Lắng nghe real-time qua Laravel Echo (Presence Channel)
  function listenToConversation(conversationId: number) {
    listeningConversationId = conversationId

    echo
      .join(`chat.${conversationId}`)
      .listen('.MessageSent', (e: { message: MessageResponse }) => {
        const authStore = useAuthStore()
        // Tránh duplicate nếu chính mình gửi (đã được thêm từ optimistic update)
        if (e.message.sender_id === authStore.user?.id) return

        messages.value.push(e.message)
        updateLastMessage(conversationId, e.message)
        // Đang xem conversation này → đánh dấu đã đọc luôn
        markAsRead(conversationId)
      })
      .listen('.MessageRead', (e: ReadReceiptPayload) => {
        applyReadReceipt(e)
      })
  }

  // 8. Rời channel đang lắng nghe
  function leaveCurrentChannel() {
    if (listeningConversationId !== null) {
      echo.leave(`chat.${listeningConversationId}`)
      listeningConversationId = null
    }
  }

  // Tìm kiếm người dùng theo username
  let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null
  async function searchUsers(query: string) {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
    if (!query.trim()) {
      userSearchResults.value = []
      return
    }
    searchDebounceTimer = setTimeout(async () => {
      searchingUsers.value = true
      try {
        const res = await api.searchUsers(query)
        userSearchResults.value = res.data.results ?? []
      } catch (e) {
        console.error('[Chat] searchUsers error:', e)
        userSearchResults.value = []
      } finally {
        searchingUsers.value = false
      }
    }, 350)
  }

  // Mở chat mới với user từ kết quả tìm kiếm
  async function startNewChat(targetUser: SearchUserItem) {
    const authStore = useAuthStore()
    if (!authStore.user) return

    // Kiểm tra xem đã có conversation với user này chưa
    const existing = conversations.value.find(
      (c) => c.receiver?.id === targetUser.id,
    )
    if (existing) {
      showUserSearch.value = false
      userSearchResults.value = []
      await selectConversation(existing)
      return
    }

    // Tạo conversation mới
    try {
      await api.createConversation({
        sender_id: authStore.user.id,
        receiver_id: targetUser.id,
        message: '👋',
      })
      await loadConversations()
      // Chọn conversation vừa tạo
      const newConv = conversations.value.find((c) => c.receiver?.id === targetUser.id)
      if (newConv) await selectConversation(newConv)
    } catch (e) {
      console.error('[Chat] startNewChat error:', e)
      throw e
    } finally {
      showUserSearch.value = false
      userSearchResults.value = []
    }
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────

  // Đánh dấu conversation đã đọc (cập nhật local, bỏ chấm unread)
  function markAsRead(conversationId: number) {
    const conv = conversations.value.find((c) => c.conversation_id === conversationId)
    if (conv && conv.last_message) {
      conv.last_read_message_id = conv.last_message.id
    }
  }

  function updateLastMessage(conversationId: number, message: MessageResponse) {
    const conv = conversations.value.find((c) => c.conversation_id === conversationId)
    if (conv) {
      conv.last_message = message
    }
  }

  function applyReadReceipt(payload: ReadReceiptPayload) {
    const conv = conversations.value.find((c) => c.conversation_id === payload.conversation_id)
    if (conv) {
      conv.last_message_id1 = payload.last_message_id1
      conv.last_message_id2 = payload.last_message_id2
    }

    if (activeConversation.value?.conversation_id === payload.conversation_id) {
      activeConversation.value.last_message_id1 = payload.last_message_id1
      activeConversation.value.last_message_id2 = payload.last_message_id2
    }
  }

  // Nhận tin nhắn đến từ MessageNotification (khi không ở trong conversation đó)
  function injectIncomingMessage(message: MessageResponse) {
    const isActiveConv = activeConversation.value?.conversation_id === message.conversation_id

    // Nếu đang mở đúng conversation này → thêm vào danh sách tin nhắn
    if (isActiveConv) {
      const duplicate = messages.value.find((m) => m.id === message.id)
      if (!duplicate) messages.value.push(message)
    }

    // Cập nhật last_message trong sidebar
    const conv = conversations.value.find((c) => c.conversation_id === message.conversation_id)
    if (conv) {
      conv.last_message = message
      if (isActiveConv) {
        // Đang xem conversation → đánh dấu đã đọc ngay
        conv.last_read_message_id = message.id
      }
      // Nếu không đang xem → giữ nguyên last_read_message_id → dot sẽ hiện
    }
  }

  async function markMessageRead(conversationId: number, messageId: number) {
    const authStore = useAuthStore()
    if (!authStore.user) return

    const conv = conversations.value.find((c) => c.conversation_id === conversationId)
      ?? activeConversation.value
    if (!conv) return

    const currentRead = conv.user1_id === authStore.user.id
      ? conv.last_message_id1
      : conv.last_message_id2

    if (currentRead && messageId <= currentRead) return

    try {
      const res = await api.markConversationRead(conversationId, messageId)
      applyReadReceipt(res.data as MarkReadResponse)
    } catch (e) {
      console.error('[Chat] markMessageRead error:', e)
    }
  }

  // ─── Cleanup ──────────────────────────────────────────────────────────────
  function reset() {
    leaveCurrentChannel()
    conversations.value = []
    messages.value = []
    activeConversation.value = null
    hasOlderMessages.value = false
    userSearchResults.value = []
    showUserSearch.value = false
    onlineUserIds.value = new Set()
  }

  return {
    // state
    conversations,
    messages,
    activeConversation,
    loadingConversations,
    loadingMessages,
    sendingMessage,
    hasOlderMessages,
    userSearchResults,
    searchingUsers,
    showUserSearch,
    onlineUserIds,
    // actions
    loadConversations,
    selectConversation,
    loadOlderMessages,
    sendMessage,
    createConversation,
    searchUsers,
    startNewChat,
    injectIncomingMessage,
    markMessageRead,
    leaveCurrentChannel,
    reset,
  }
})
