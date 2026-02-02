import axios from 'axios';
import Cookies from 'js-cookie';
import { ref } from 'vue';

//đằng ký biến http để giao tiếp API
const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 1000,
  withCredentials: true
})
//tạo đánh chặn cho http request gửi đi
http.interceptors.request.use(config => {
  const token = Cookies.get('token');

  config.headers.Authorization = `Bearer ${token}`;

  const isFormData = typeof FormData !== 'undefined' && config.data instanceof FormData
  if(!isFormData && !config.headers['Content-Type']) {
    config.headers['Content-Type'] = 'application/json';
  }
  return config;
})
//global loading state
const globalLoading = ref(0);
//đăng ký đánh chặn request và response để set global loading state
http.interceptors.request.use(config => { //config = configuration request
  globalLoading.value++;
  return config;
}, error => {
  globalLoading.value = Math.max(0, globalLoading.value --);
  return Promise.reject(error);
})

http.interceptors.response.use(res => { //res = response
  globalLoading.value = Math.max(0, globalLoading.value --);
  return res;
}, error => {
  globalLoading.value = Math.max(0, globalLoading.value --);
  return Promise.reject(error);
})

export { http, globalLoading };


