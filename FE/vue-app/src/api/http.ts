import axios from 'axios';
import { ref } from 'vue';

// --- 1. CONFIGURATION ---
const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000, 
  
  // Cho phép trình duyệt gửi/nhận Cookie (Laravel Session & XSRF)
  withCredentials: true,
  
  //Tự động xử lý CSRF Token của Laravel
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// ---- LOADING STATE ---
const globalLoading = ref(0);

function getCookie(name: string) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift();
}

// REQUEST INTERCEPTOR
http.interceptors.request.use(config => {
  // Bật loading
  globalLoading.value++;

  const token = getCookie('XSRF-TOKEN');
  if (token) {
    config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
  }
  return config;
}, error => {
  globalLoading.value = Math.max(0, globalLoading.value - 1);
  return Promise.reject(error);
});

// RESPONSE INTERCEPTOR
http.interceptors.response.use(response => {
  // Tắt loading khi thành công
  globalLoading.value = Math.max(0, globalLoading.value - 1);
  return response;
}, error => {
  // Tắt loading khi thất bại
  globalLoading.value = Math.max(0, globalLoading.value - 1);
  return Promise.reject(error);
});


export { http, globalLoading };