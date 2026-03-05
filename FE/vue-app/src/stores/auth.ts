import { defineStore } from 'pinia'
import { ref, computed } from 'vue';
import api from '@/api/api';
import type { User } from '@/api/api';
import { useToast } from 'vue-toastification';
import router from '@/router';

const toast = useToast();

export const useAuthStore = defineStore('auth', () =>{
  const user = ref<User | null>(null);

  const isAuthenticated = computed(() => user.value !== null);
  const UserAvatar = computed(() => user.value?.avatar);
  const UserProfileName = computed(() => user.value?.name);
  const Useremail = computed(() => user.value?.email);

  // Luôn fetch user mới nhất từ server (dùng sau login/oauth)
  async function fetchUser(){
    try {
      const response = await api.selfProfile();
      user.value = response.data;
    }
    catch (error) {
        user.value = null;
    } 
  }

  // Chỉ fetch nếu chưa có user (dùng khi khởi động app)
  async function fetchUserIfNeeded(){
    if(user.value) return;
    await fetchUser();
  }

  async function login(payload: {username: string, password: string}){ 
    try {
    await api.getCsrfCookie();
    await api.login(payload);
    await fetchUser();
    } catch (error) {
      throw error;
    } 
  }

  async function Oauth(authProvider: string) {
    try {
      const baseUrl = import.meta.env.VITE_API_BASE_URL;
      const oauthUrls: Record<string, string> = {
        google:   `${baseUrl}/api/auth/google/redirect`,
        facebook: `${baseUrl}/api/auth/facebook/redirect`,
      };

      const url = oauthUrls[authProvider];
      if (!url) return;

      // Xóa listener cũ trước khi thêm mới → tránh duplicate
      window.removeEventListener('message', handlePopupMessage);

      const popup = window.open(url, `Login ${authProvider}`, 'width=500,height=600');

      // Kiểm tra popup bị block bởi browser
      if (!popup) {
        toast.error('Popup bị chặn. Vui lòng cho phép popup và thử lại.');
        return;
      }

      window.addEventListener('message', handlePopupMessage);

    } catch (error) {
      throw error;
    }
  }

  async function handlePopupMessage(event: MessageEvent) {
    // Chỉ xử lý message từ BE, bỏ qua Vite HMR / browser extensions / iframe khác
    const allowedOrigin = import.meta.env.VITE_API_BASE_URL;
    if (event.origin !== allowedOrigin) return;

    const { status } = event.data;

    if (status === 'success') {
      await fetchUser();
      toast.success('Đăng nhập thành công!');
      router.push({ name: 'home' });
    } else if (status === 'error') {
      toast.error('OAuth login failed.');
      console.error('[OAuth] Error from BE:', event.data);
    }

    // Chỉ remove sau khi nhận được message thật từ BE
    window.removeEventListener('message', handlePopupMessage);
  }
  async function register(payload: {username: string, email: string, password: string, password_confirmation: string}){
      try {
        await api.getCsrfCookie();
        await api.register(payload);
      } catch (error) {
        throw error;
      }
  }

  async function logout(){
      try {
        await api.logout();
      } catch (error) {
        throw error;
      } finally {
        user.value = null;
      }
  }

  return {
    user,
    isAuthenticated,
    UserAvatar,
    UserProfileName,
    Useremail,
    fetchUser,
    fetchUserIfNeeded,
    login,
    Oauth,
    register,
    logout,
  }
})  

