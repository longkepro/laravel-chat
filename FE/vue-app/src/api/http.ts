import axios from 'axios';
import { ref } from 'vue';
import { clearAuthToken, getAuthToken } from '@/lib/auth-token';

const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000,
  headers: {
    Accept: 'application/json',
  },
});

const globalLoading = ref(0);

http.interceptors.request.use(
  (config) => {
    globalLoading.value++;

    const token = getAuthToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    globalLoading.value = Math.max(0, globalLoading.value - 1);
    return Promise.reject(error);
  },
);

http.interceptors.response.use(
  (response) => {
    globalLoading.value = Math.max(0, globalLoading.value - 1);
    return response;
  },
  (error) => {
    globalLoading.value = Math.max(0, globalLoading.value - 1);

    if (error?.response?.status === 401) {
      clearAuthToken();
    }

    return Promise.reject(error);
  },
);

export { http, globalLoading };
