import type { A } from 'vue-router/dist/router-CWoNjPRp.mjs';
import { http } from '../api/http';

//user auth interface
export interface User {
  id: number
  username: string
  email?: string
  name?: string | null
  avatar?: string
  google_id?: string | null
  facebook_id?: string | null
  created_at: string
  updated_at: string
}

//response interface
export interface AuthUser {
  id: number
  username: string
  name: string | null
}

export interface LoginResponse {
  user: AuthUser
}

export interface registerResponse {
  status: string;
}
export interface logoutResponse {
  status: string;
}

export interface OauthCallbackSuccess {
  status: 'success';
  token: string;
  user: User;
}
export interface OauthCallbackError {
  status: 'error';
  message: string;
}
export type OauthCallbackResponse = OauthCallbackError | OauthCallbackSuccess;

export interface ConversationUser {
  id: number
  username: string
  avatar: string | null
}
export interface MessageResponse {
  id: number;
  conversation_id: number;
  sender_id: number;
  receiver_id?: number; 
  message: string;
  attachment?: string;
  created_at: string;
  updated_at: string;
}
export interface ConversationItem {
  conversation_id: number
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
  sender: User;
  receiver: User;
}
//Kết quả tìm kiếm tin nhắn với ngữ cảnh
export interface FetchSearchedMessagesResponse {
  anchor_id: number;         // ID của tin nhắn mốc
  messages: ContextMessage[]; // Mảng tin nhắn liền mạch (Older -> Anchor -> Newer)
  has_older: boolean;
  has_newer: boolean;
}

export type SearchMessagesResponse = PaginatedResponse<MessageResponse>;

export interface SendMessageResponse {
  status: string;
  message: MessageResponse;
}

export interface SearchUsersResponse {
  id: number;
  username: string;
  avatar: string | null; 
}

// export interface SearchUsersResponse {
//   results: User[];
// }

export interface EditProfileSuccess {
  status: string;        
  avatar: string | null; 
}
export interface EditProfileError {
  error: string;
}

export type EditProfileResponse = EditProfileSuccess | EditProfileError;

export interface UpdatePasswordSuccess {
  status: string;
}

export interface UpdatePasswordError {
  error: string;
}

export type UpdatePasswordResponse = UpdatePasswordSuccess | UpdatePasswordError;

export interface CreateConversationResponse {
  status: string; 
  message: MessageResponse;
}

//-----------api object-----------
const api = {
  //đăng ký, đăng nhập, lấy thông tin user, đăng xuất
  register(data: { username: string; email: string; password: string; password_confirmation: string }) {
    return http.post<registerResponse>('/api/auth/register', data);
  },
  login(data: { username: string; password: string }) {
    return http.post<LoginResponse>('/api/auth/login', data);
  },
  profile(userID: number) {
    return http.get<User>(`/api/profile/${userID}`);
  },
  selfProfile() {
    return http.get<User>(`/api/profile/authUser`);
  },
  logout() {
    return http.post<logoutResponse>('/api/auth/logout');
  },

  // edit profile
  editProfile(data: { username: string; avatar: string }) {
    const formData = new FormData();
    formData.append('username', data.username);
    formData.append('avatar', data.avatar);
  
    return http.post<EditProfileResponse>('/api/profile/editProfile', formData);
  },
  updatePassword(data: { current_password: string; new_password: string; new_password_confirmation: string }) {
    return http.post<UpdatePasswordResponse>('/api/profile/updatePassword', data);
  },

  //chat api
  getConversations(){
    return http.get<<PaginatedRespone<ConversationItem>>('/api/conversations');
  },
  getOlderMessages(conversationID: number, MessageID: number) {
    return http.get<GetOlderMessagesResponse>(`/api/conversations/${conversationID}/olderMessages/${MessageID}`);
  },
  getNewerMessages(conversationID: number, MessageID: number) {
    return http.get<GetNewerMessagesResponse>(`/api/conversations/${conversationID}/newerMessages/${MessageID}`);
  },   
  sendMessage(data: { sender_id: number, conversation_id: number; message: string }) {
    return http.post<SendMessageResponse>('/api/conversations/sendmessages', data);
  },
  createConversation(data: { sender_id: number; message: string; receiver_id: number }) {
    return http.post<CreateConversationResponse>('/api/conversations/create', data);
  },

  //search
  searchUsers(query: string) {
    return http.get<SearchUsersResponse>('/api/search/users', { params: { q: query } });
  },
  searchMessages(query: string) {
    return http.get<SearchMessagesResponse>(`/api/search/messages`, { params: { q: query } });
  },

  //lấy cookie CSRF từ Laravel Sanctum
  getCsrfCookie() {
    // Route này thường nằm ở root, KHÔNG có prefix /api
    return http.get('/sanctum/csrf-cookie');
  },
}

export default api;