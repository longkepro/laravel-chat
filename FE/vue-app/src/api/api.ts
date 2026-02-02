import { http } from '../api/http';

const api = {
  //đăng ký, đăng nhập, lấy thông tin user, đăng xuất
  register(data: { username: string; email: string; password: string; password_confirmation: string }) {
    return http.post('/register', data);
  },
  login(data: { username: string; password: string }) {
    return http.post('/login', data);
  },
  profile(userID: number) {
    return http.get(`profile/${userID}`);
  },
  logout() {
    return http.post('/logout');
  },

  //Oauth 2.0
  oauthGoogle() {
    return http.get(`/google/redirect`);
  },
  oauthFacebook() {
    return http.get(`/facebook/redirect`);
  },

  //edit profile
  editProfile(data: { username: string; avatar: string }) {
    const formData = new FormData();
    formData.append('username', data.username);
    formData.append('avatar', data.avatar);
  
    return http.post('/profile/editprofile', formData);
  },
  updatePassword(data: { current_password: string; new_password: string; new_password_confirmation: string }) {
    return http.post('/profile/updatepassword', data);
  },

  //chat api
  getConversations(){
    return http.get('/conversations');
  },
  getOlderMessages(conversationID: number, MessageID: number) {
    return http.get(`/conversations/${conversationID}/messages/older/${MessageID}`);
  },
  getNewerMessages(conversationID: number, MessageID: number) {
    return http.get(`/conversations/${conversationID}/messages/newer/${MessageID}`);
  },
  sendMessage(data: { sender_id: number, conversation_id: number; message: string }) {
    return http.post('/conversations/sendmessages', data);
  },
  createConversation(data: { sender_id: number; message: string; receiver_id: number }) {
    return http.post('/conversations/create', data);
  },

  //search
  searchUsers(query: string) {
    return http.get('/search/users', { params: { q: query } });
  },
  searchMessages(query: string) {
    return http.get(`/search/messages`, { params: { q: query } });
  }
}