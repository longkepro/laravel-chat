import { http } from './http';

export interface User {
  id: number
  username: string
  email?: string
  name?: string | null
  avatar?: string
  google_id?: string | null
  facebook_id?: string | null
  created_at?: string
  updated_at?: string
}

export interface AuthResponse {
  token: string
  token_type: 'Bearer'
  user: User
  status?: string
}

export interface LogoutResponse {
  message: string
}

export interface OauthCallbackSuccess {
  status: 'success'
  token: string
  user: User
}

export interface OauthCallbackError {
  status: 'error'
  message: string
}

export type OauthCallbackResponse = OauthCallbackError | OauthCallbackSuccess;

export interface ConversationUser {
  id: number
  username: string
  avatar: string | null
}

export interface MessageResponse {
  id: number
  conversation_id: number
  sender_id: number
  receiver_id?: number
  message: string
  attachment?: string
  created_at: string
  updated_at: string
}

export interface ConversationItem {
  conversation_id: number
  user1_id: number
  user2_id: number
  last_message_id1: number | null
  last_message_id2: number | null
  receiver: ConversationUser | null
  last_message: MessageResponse | null
  last_read_message_id: number | null
  updated_at: string
}

export interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  first_page_url: string
  last_page: number
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  per_page: number
  total: number
}

export type GetOlderMessagesResponse = MessageResponse[];
export type GetNewerMessagesResponse = MessageResponse[];

export interface ContextMessage extends MessageResponse {
  sender: User
  receiver: User
}

export interface FetchSearchedMessagesResponse {
  anchor_id: number
  messages: ContextMessage[]
  has_older: boolean
  has_newer: boolean
}

export type SearchMessagesResponse = PaginatedResponse<MessageResponse>;

export interface SendMessageResponse {
  status: string
  message: MessageResponse
}

export interface SearchUserItem {
  id: number
  username: string
  name: string | null
  avatar: string | null
}

export interface SearchUsersResponse {
  results: SearchUserItem[]
}

export interface EditProfileSuccess {
  status: string
  user: User
}

export interface EditProfileError {
  error: string
}

export type EditProfileResponse = EditProfileSuccess | EditProfileError;

export interface UpdatePasswordSuccess {
  status: string
}

export interface UpdatePasswordError {
  error: string
}

export type UpdatePasswordResponse = UpdatePasswordSuccess | UpdatePasswordError;

export interface CreateConversationResponse {
  status: string
  message: MessageResponse
}

export interface MarkReadResponse {
  status: string
  conversation_id: number
  last_message_id1: number | null
  last_message_id2: number | null
}

const api = {
  register(data: { username: string; email: string; password: string; password_confirmation: string }) {
    return http.post<AuthResponse>('/api/auth/register', data);
  },
  login(data: { username: string; password: string }) {
    return http.post<AuthResponse>('/api/auth/login', data);
  },
  me() {
    return http.get<User>('/api/auth/me');
  },
  profile(userID: number) {
    return http.get<User>(`/api/profile/${userID}`);
  },
  selfProfile() {
    return http.get<User>('/api/profile/authUser');
  },
  logout() {
    return http.post<LogoutResponse>('/api/auth/logout');
  },

  editProfile(data: { profile_name?: string; avatar?: File | null }) {
    const formData = new FormData();
    if (typeof data.profile_name === 'string') {
      formData.append('profile_name', data.profile_name);
    }
    if (data.avatar) {
      formData.append('avatar', data.avatar);
    }

    return http.post<EditProfileResponse>('/api/profile/editProfile', formData);
  },
  updatePassword(data: { current_password: string; new_password: string; new_password_confirmation: string }) {
    return http.post<UpdatePasswordResponse>('/api/profile/updatePassword', data);
  },

  getConversations() {
    return http.get<PaginatedResponse<ConversationItem>>('/api/conversations');
  },
  getOlderMessages(conversationID: number, MessageID: number) {
    return http.get<GetOlderMessagesResponse>(`/api/conversations/${conversationID}/olderMessages/${MessageID}`);
  },
  getLatestMessages(conversationID: number) {
    return http.get<GetOlderMessagesResponse>(`/api/conversations/${conversationID}/latestMessages`);
  },
  getNewerMessages(conversationID: number, MessageID: number) {
    return http.get<GetNewerMessagesResponse>(`/api/conversations/${conversationID}/newerMessages/${MessageID}`);
  },
  sendMessage(data: { conversation_id: number; message: string; attachment?: string }) {
    return http.post<SendMessageResponse>('/api/conversations/sendmessages', data);
  },
  createConversation(data: { message: string; receiver_id: number }) {
    return http.post<CreateConversationResponse>('/api/conversations/create', data);
  },
  markConversationRead(conversationID: number, messageId: number) {
    return http.post<MarkReadResponse>(`/api/conversations/${conversationID}/read`, {
      message_id: messageId,
    });
  },

  searchUsers(query: string) {
    return http.get<SearchUsersResponse>('/api/search/users', { params: { q: query } });
  },
  fetchSearchedMessages(conversationID: number, MessageID: number) {
    return http.get<FetchSearchedMessagesResponse>(`/api/conversations/${conversationID}/fetchSearchMessages/${MessageID}`);
  },
  searchMessages(query: string) {
    return http.get<SearchMessagesResponse>('/api/search/messages', { params: { q: query } });
  },
};

export default api;
