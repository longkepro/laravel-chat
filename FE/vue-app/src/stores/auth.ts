import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import api, { type OauthCallbackResponse, type User } from '@/api/api';
import { clearAuthToken, getAuthToken, hasAuthToken, setAuthToken } from '@/lib/auth-token';
import { useToast } from 'vue-toastification';
import router from '@/router';

const toast = useToast();

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);

  const isAuthenticated = computed(() => user.value !== null);
  const UserAvatar = computed(() => user.value?.avatar);
  const UserProfileName = computed(() => user.value?.name ?? user.value?.username);
  const Useremail = computed(() => user.value?.email);

  function applyAuthenticatedUser(nextUser: User, token?: string) {
    if (token) {
      setAuthToken(token);
    }

    user.value = nextUser;
  }

  async function fetchUser() {
    if (!hasAuthToken()) {
      user.value = null;
      return;
    }

    try {
      const response = await api.me();
      user.value = response.data;
    } catch (error) {
      clearAuthToken();
      user.value = null;
    }
  }

  async function fetchUserIfNeeded() {
    if (user.value || !hasAuthToken()) return;
    await fetchUser();
  }

  async function login(payload: { username: string; password: string }) {
    const response = await api.login(payload);
    applyAuthenticatedUser(response.data.user, response.data.token);
  }

  async function Oauth(authProvider: string) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL;
    const oauthUrls: Record<string, string> = {
      google: `${baseUrl}/api/auth/google/redirect`,
      facebook: `${baseUrl}/api/auth/facebook/redirect`,
    };

    const url = oauthUrls[authProvider];
    if (!url) return;

    window.removeEventListener('message', handlePopupMessage);

    const popup = window.open(url, `Login ${authProvider}`, 'width=500,height=600');
    if (!popup) {
      toast.error('Popup bị chặn. Vui lòng cho phép popup và thử lại.');
      return;
    }

    window.addEventListener('message', handlePopupMessage);
  }

  async function handlePopupMessage(event: MessageEvent<OauthCallbackResponse>) {
    const allowedOrigin = new URL(import.meta.env.VITE_API_BASE_URL).origin;
    if (event.origin !== allowedOrigin) return;

    const payload = event.data;
    if (!payload) return;

    if (payload.status === 'success' && payload.token && payload.user) {
      applyAuthenticatedUser(payload.user, payload.token);
      await fetchUser();
      toast.success('Đăng nhập thành công!');
      router.push({ name: 'home' });
    } else if (payload.status === 'error') {
      toast.error(payload.message || 'OAuth login failed.');
      console.error('[OAuth] Error from BE:', payload);
    }

    window.removeEventListener('message', handlePopupMessage);
  }

  async function register(payload: { username: string; email: string; password: string; password_confirmation: string }) {
    const response = await api.register(payload);
    applyAuthenticatedUser(response.data.user, response.data.token);
  }

  async function logout() {
    try {
      if (getAuthToken()) {
        await api.logout();
      }
    } catch (error) {
      throw error;
    } finally {
      clearAuthToken();
      user.value = null;
      const { useChatStore } = await import('@/stores/chat');
      useChatStore().reset();
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
  };
});
