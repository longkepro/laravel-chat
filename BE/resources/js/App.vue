<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100">
        <div class="mx-auto flex min-h-screen w-full max-w-6xl flex-col gap-10 px-6 py-10 lg:py-16">
            <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-widest text-emerald-500">Laravel + Vue 3</p>
                    <h1 class="text-4xl font-semibold lg:text-5xl">Let's get started</h1>
                    <p class="mt-2 max-w-3xl text-base text-slate-500 dark:text-slate-300">
                        Bạn đang ở chế độ Single Page Application. Frontend được dựng bằng Vue 3 + Pinia và nhận JSON
                        từ backend Laravel qua Sanctum token, sẵn sàng cho realtime với Pusher/Echo.
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="/login" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:border-slate-400">
                        Đăng nhập
                    </a>
                    <a href="/register" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                        Tạo tài khoản
                    </a>
                </div>
            </header>

            <main class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur lg:p-8 dark:border-slate-800 dark:bg-slate-900/60">
                    <h2 class="text-xl font-semibold">Resources</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Những tài nguyên nên xem đầu tiên.</p>
                    <ul class="mt-4 space-y-4">
                        <li v-for="item in resources" :key="item.href" class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 hover:border-emerald-200 dark:border-slate-800 dark:bg-slate-800/60">
                            <a :href="item.href" target="_blank" class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">{{ item.title }}</p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ item.description }}</p>
                                </div>
                                <span class="text-sm font-semibold text-emerald-500">↗</span>
                            </a>
                        </li>
                    </ul>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur lg:p-8 dark:border-slate-800 dark:bg-slate-900/60">
                    <h2 class="text-xl font-semibold">Realtime activity</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Các sự kiện được gửi trên channel <code class="rounded bg-slate-100 px-1 text-xs dark:bg-slate-800">notifications</code>
                    </p>
                    <div class="mt-6 space-y-3">
                        <div v-for="event in recentEvents" :key="event.id" class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-800/60">
                            <p class="font-medium">{{ event.message }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ event.meta }}</p>
                        </div>
                        <p v-if="recentEvents.length === 0" class="text-sm text-slate-500 dark:text-slate-400">
                            Chưa có event nào — thử phát sự kiện <code class="rounded bg-slate-100 px-1 text-xs dark:bg-slate-800">UserSessionChange</code>
                        </p>
                    </div>
                </section>
            </main>
        </div>

        <transition name="fade-slide">
            <div
                v-if="notification.visible"
                class="pointer-events-none fixed right-5 top-5 z-50 max-w-sm rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-medium text-emerald-800 shadow-lg dark:border-emerald-700 dark:bg-slate-900 dark:text-emerald-200"
            >
                {{ notification.message }}
            </div>
        </transition>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const resources = [
    {
        title: 'Laravel Documentation',
        description: 'Bắt đầu với routing, broadcasting, queues, ...',
        href: 'https://laravel.com/docs',
    },
    {
        title: 'Laracasts',
        description: 'Video chất lượng cao về toàn bộ hệ sinh thái Laravel.',
        href: 'https://laracasts.com',
    },
    {
        title: 'Vue 3 Guide',
        description: 'Tài liệu Composition API, reactivity, router...',
        href: 'https://vuejs.org/guide/introduction.html',
    },
];

const notification = ref({ visible: false, message: '' });
const recentEvents = ref([]);
let hideTimer = null;
let connection = null;
let channel = null;

const showNotification = (message) => {
    notification.value = { visible: true, message };
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
        notification.value.visible = false;
    }, 3200);
};

const pushEvent = (payload) => {
    recentEvents.value = [
        {
            id: crypto.randomUUID(),
            message: payload.message,
            meta: new Date().toLocaleString(),
        },
        ...recentEvents.value.slice(0, 4),
    ];
};

const handleConnected = () => {
    console.log('✅ Đã kết nối tới Pusher Cloud');
    showNotification('Đã kết nối realtime tới Pusher Cloud');
};

const handleUserSessionEvent = (event) => {
    const username = event?.message?.username ?? 'Ai đó';
    const type = event?.type ?? 'thay đổi trạng thái';
    const message = `${username} vừa ${type}`;

    pushEvent({ message });
    showNotification(message);
};

onMounted(() => {
    if (window?.Echo) {
        connection = window.Echo.connector?.pusher?.connection;
        connection?.bind('connected', handleConnected);

        channel = window.Echo.channel('notifications');
        channel?.listen('.UserSessionChange', handleUserSessionEvent);
    } else {
        console.warn('Laravel Echo chưa được khởi tạo.');
    }
});

onBeforeUnmount(() => {
    clearTimeout(hideTimer);
    connection?.unbind('connected', handleConnected);
    channel?.stopListening('.UserSessionChange');
});
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.25s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
