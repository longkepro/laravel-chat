import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

import Toast, { type PluginOptions, POSITION } from "vue-toastification";
import "vue-toastification/dist/index.css";

import './assets/main.css'

const app = createApp(App)

const toastOptions: PluginOptions = {
    // Vị trí xuất hiện 
    position: POSITION.TOP_RIGHT,
    // Thời gian tự tắt 
    timeout: 3000,
    // Có thanh tiến trình chạy bên dưới không?
    closeOnClick: false,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false
};

app.use(createPinia())
app.use(router)

// 3. Kích hoạt Toast với options
app.use(Toast, toastOptions);

app.mount('#app')