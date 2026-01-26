import './bootstrap.js';
import '../css/app.css';
import { createApp } from 'vue';
import App from './App.vue';

// Mount the Vue SPA so all realtime/UI logic is handled inside Vue components
createApp(App).mount('#app');
