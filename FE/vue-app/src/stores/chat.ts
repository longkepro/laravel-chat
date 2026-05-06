import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/api/api';
import type { ConversationItem, MarkReadResponse, MessageResponse, SearchUserItem } from '@/api/api';
import echo from '@/services/echo';
import { useAuthStore } from '@/stores/auth';

export const useChatStore = defineStore('chat', () => {
  const conversations = ref<ConversationItem[]>([]);
  const messages = ref<MessageResponse[]>([]);
  const activeConversation = ref<ConversationItem | null>(null);

  const loadingConversations = ref(false);
  const loadingMessages = ref(false);
  const sendingMessage = ref(false);
  const hasOlderMessages = ref(false);
  const userSearchResults = ref<SearchUserItem[]>([]);
  const searchingUsers = ref(false);
  const showUserSearch = ref(false);
  const onlineUserIds = ref<Set<number>>(new Set());

  type ReadReceiptPayload = {
    conversation_id: number
    reader_id?: number
    last_message_id1: number | null
    last_message_id2: number | null
  };

  let listeningConversationId: number | null = null;

  async function loadConversations() {
    loadingConversations.value = true;
    try {
      const res = await api.getConversations();
      conversations.value = res.data.data;
    } catch (e) {
      console.error('[Chat] loadConversations error:', e);
    } finally {
      loadingConversations.value = false;
    }
  }

  async function selectConversation(conversation: ConversationItem) {
    leaveCurrentChannel();

    activeConversation.value = conversation;
    messages.value = [];
    hasOlderMessages.value = false;

    markAsRead(conversation.conversation_id);

    await loadInitialMessages(conversation.conversation_id);
    listenToConversation(conversation.conversation_id);
  }

  async function loadInitialMessages(conversationId: number) {
    loadingMessages.value = true;
    try {
      const res = await api.getLatestMessages(conversationId);
      messages.value = res.data;
      hasOlderMessages.value = res.data.length === 20;
    } catch (e) {
      console.error('[Chat] loadInitialMessages error:', e);
    } finally {
      loadingMessages.value = false;
    }
  }

  async function loadOlderMessages() {
    if (!activeConversation.value || !hasOlderMessages.value || messages.value.length === 0) return;

    const oldestMessage = messages.value[0];
    if (!oldestMessage) return;

    loadingMessages.value = true;
    try {
      const res = await api.getOlderMessages(activeConversation.value.conversation_id, oldestMessage.id);
      messages.value = [...res.data, ...messages.value];
      hasOlderMessages.value = res.data.length === 20;
    } catch (e) {
      console.error('[Chat] loadOlderMessages error:', e);
    } finally {
      loadingMessages.value = false;
    }
  }

  async function sendMessage(text: string, attachment?: string) {
    const authStore = useAuthStore();
    if (!activeConversation.value || !authStore.user) return;

    sendingMessage.value = true;
    try {
      const res = await api.sendMessage({
        conversation_id: activeConversation.value.conversation_id,
        message: text,
        ...(attachment ? { attachment } : {}),
      });

      messages.value.push(res.data.message);
      updateLastMessage(activeConversation.value.conversation_id, res.data.message);
      markAsRead(activeConversation.value.conversation_id);
    } catch (e) {
      console.error('[Chat] sendMessage error:', e);
      throw e;
    } finally {
      sendingMessage.value = false;
    }
  }

  async function createConversation(receiverId: number, message: string) {
    const authStore = useAuthStore();
    if (!authStore.user) return;

    try {
      await api.createConversation({
        receiver_id: receiverId,
        message,
      });
      await loadConversations();
    } catch (e) {
      console.error('[Chat] createConversation error:', e);
      throw e;
    }
  }

  function listenToConversation(conversationId: number) {
    listeningConversationId = conversationId;

    echo
      .join(`chat.${conversationId}`)
      .listen('.MessageSent', (e: { message: MessageResponse }) => {
        const authStore = useAuthStore();
        if (e.message.sender_id === authStore.user?.id) return;

        messages.value.push(e.message);
        updateLastMessage(conversationId, e.message);
        markAsRead(conversationId);
      })
      .listen('.MessageRead', (e: ReadReceiptPayload) => {
        applyReadReceipt(e);
      });
  }

  function leaveCurrentChannel() {
    if (listeningConversationId !== null) {
      echo.leave(`chat.${listeningConversationId}`);
      listeningConversationId = null;
    }
  }

  let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

  async function searchUsers(query: string) {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);

    if (!query.trim()) {
      userSearchResults.value = [];
      return;
    }

    searchDebounceTimer = setTimeout(async () => {
      searchingUsers.value = true;
      try {
        const res = await api.searchUsers(query);
        userSearchResults.value = res.data.results ?? [];
      } catch (e) {
        console.error('[Chat] searchUsers error:', e);
        userSearchResults.value = [];
      } finally {
        searchingUsers.value = false;
      }
    }, 350);
  }

  async function startNewChat(targetUser: SearchUserItem) {
    const authStore = useAuthStore();
    if (!authStore.user) return;

    const existing = conversations.value.find((c) => c.receiver?.id === targetUser.id);
    if (existing) {
      showUserSearch.value = false;
      userSearchResults.value = [];
      await selectConversation(existing);
      return;
    }

    try {
      await api.createConversation({
        receiver_id: targetUser.id,
        message: '👋',
      });
      await loadConversations();
      const newConv = conversations.value.find((c) => c.receiver?.id === targetUser.id);
      if (newConv) await selectConversation(newConv);
    } catch (e) {
      console.error('[Chat] startNewChat error:', e);
      throw e;
    } finally {
      showUserSearch.value = false;
      userSearchResults.value = [];
    }
  }

  function markAsRead(conversationId: number) {
    const conv = conversations.value.find((c) => c.conversation_id === conversationId);
    if (conv && conv.last_message) {
      conv.last_read_message_id = conv.last_message.id;
    }
  }

  function updateLastMessage(conversationId: number, message: MessageResponse) {
    const conv = conversations.value.find((c) => c.conversation_id === conversationId);
    if (conv) {
      conv.last_message = message;
    }
  }

  function applyReadReceipt(payload: ReadReceiptPayload) {
    const conv = conversations.value.find((c) => c.conversation_id === payload.conversation_id);
    if (conv) {
      conv.last_message_id1 = payload.last_message_id1;
      conv.last_message_id2 = payload.last_message_id2;
    }

    if (activeConversation.value?.conversation_id === payload.conversation_id) {
      activeConversation.value.last_message_id1 = payload.last_message_id1;
      activeConversation.value.last_message_id2 = payload.last_message_id2;
    }
  }

  function injectIncomingMessage(message: MessageResponse) {
    const isActiveConv = activeConversation.value?.conversation_id === message.conversation_id;

    if (isActiveConv) {
      const duplicate = messages.value.find((m) => m.id === message.id);
      if (!duplicate) messages.value.push(message);
    }

    const conv = conversations.value.find((c) => c.conversation_id === message.conversation_id);
    if (conv) {
      conv.last_message = message;
      if (isActiveConv) {
        conv.last_read_message_id = message.id;
      }
    }
  }

  async function markMessageRead(conversationId: number, messageId: number) {
    const authStore = useAuthStore();
    if (!authStore.user) return;

    const conv = conversations.value.find((c) => c.conversation_id === conversationId)
      ?? activeConversation.value;
    if (!conv) return;

    const currentRead = conv.user1_id === authStore.user.id
      ? conv.last_message_id1
      : conv.last_message_id2;

    if (currentRead && messageId <= currentRead) return;

    try {
      const res = await api.markConversationRead(conversationId, messageId);
      applyReadReceipt(res.data as MarkReadResponse);
    } catch (e) {
      console.error('[Chat] markMessageRead error:', e);
    }
  }

  function reset() {
    leaveCurrentChannel();
    conversations.value = [];
    messages.value = [];
    activeConversation.value = null;
    hasOlderMessages.value = false;
    userSearchResults.value = [];
    showUserSearch.value = false;
    onlineUserIds.value = new Set();
  }

  return {
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
  };
});
