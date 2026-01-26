import { useEffect, useRef, useState } from 'react';
import './App.css';

type Resource = {
    title: string;
    description: string;
    href: string;
};

type ActivityEvent = {
    id: string;
    message: string;
    meta: string;
};

type NotificationState = {
    visible: boolean;
    message: string;
};

type PusherConnection = {
    bind?: (event: string, callback: () => void) => void;
    unbind?: (event: string, callback: () => void) => void;
} | null;

type EchoChannel = {
    listen?: (event: string, callback: (payload: unknown) => void) => void;
    stopListening?: (event: string) => void;
} | null;

type EchoInstance = {
    connector?: {
        pusher?: {
            connection?: PusherConnection;
        };
    };
    channel?: (name: string) => EchoChannel;
};

const resources: Resource[] = [
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

function App() {
    const [notification, setNotification] = useState<NotificationState>({ visible: false, message: '' });
    const [recentEvents, setRecentEvents] = useState<ActivityEvent[]>([]);

    const hideTimerRef = useRef<number | null>(null);
    const connectionRef = useRef<PusherConnection>(null);
    const channelRef = useRef<EchoChannel>(null);

    const showNotification = (message: string) => {
        setNotification({ visible: true, message });
        if (hideTimerRef.current) {
            clearTimeout(hideTimerRef.current);
        }
        hideTimerRef.current = window.setTimeout(() => {
            setNotification((prev) => ({ ...prev, visible: false }));
        }, 3200);
    };

    const pushEvent = (payload: { message: string }) => {
        setRecentEvents((prev) => [
            {
                id: crypto.randomUUID(),
                message: payload.message,
                meta: new Date().toLocaleString(),
            },
            ...prev,
        ].slice(0, 5));
    };

    const handleConnected = () => {
        console.log('✅ Đã kết nối tới Pusher Cloud');
        showNotification('Đã kết nối realtime tới Pusher Cloud');
    };

    const handleUserSessionEvent = (event: any) => {
        const username = event?.message?.username ?? 'Ai đó';
        const type = event?.type ?? 'thay đổi trạng thái';
        const message = `${username} vừa ${type}`;

        pushEvent({ message });
        showNotification(message);
    };

    useEffect(() => {
        const echo = (window as any).Echo as EchoInstance | undefined;

        if (echo?.channel) {
            const connection = echo.connector?.pusher?.connection;
            connectionRef.current = connection ?? null;
            connection?.bind?.('connected', handleConnected);

            const channel = echo.channel('notifications');
            channelRef.current = channel ?? null;
            channel?.listen?.('.UserSessionChange', handleUserSessionEvent);
        } else {
            console.warn('Laravel Echo chưa được khởi tạo.');
        }

        return () => {
            if (hideTimerRef.current) {
                clearTimeout(hideTimerRef.current);
            }
            connectionRef.current?.unbind?.('connected', handleConnected);
            channelRef.current?.stopListening?.('.UserSessionChange');
        };
    }, []);

    return (
        <div className="app-shell">
            <div className="container">
                <header className="app-header">
                    <div>
                        <p className="eyebrow">Laravel + React</p>
                        <h1 className="heading">Let's get started</h1>
                        <p className="lede">
                            Bạn đang ở chế độ Single Page Application. Frontend được dựng bằng React và nhận JSON từ backend
                            Laravel qua Sanctum token, sẵn sàng cho realtime với Pusher/Echo.
                        </p>
                    </div>
                    <div className="app-actions">
                        <a href="/login" className="btn btn-outline">
                            Đăng nhập
                        </a>
                        <a href="/register" className="btn btn-solid">
                            Tạo tài khoản
                        </a>
                    </div>
                </header>

                <main className="grid-layout">
                    <section className="card">
                        <h2>Resources</h2>
                        <p className="muted">Những tài nguyên nên xem đầu tiên.</p>
                        <ul className="resources-list">
                            {resources.map((item) => (
                                <li key={item.href} className="resource-item">
                                    <a href={item.href} target="_blank" rel="noreferrer" className="resource-link">
                                        <div>
                                            <p className="resource-title">{item.title}</p>
                                            <p className="resource-description">{item.description}</p>
                                        </div>
                                        <span className="resource-icon" aria-hidden>
                                            ↗
                                        </span>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </section>

                    <section className="card">
                        <h2>Realtime activity</h2>
                        <p className="muted">
                            Các sự kiện được gửi trên channel <code className="inline-code">notifications</code>
                        </p>
                        <div className="events">
                            {recentEvents.length === 0 ? (
                                <p className="muted">
                                    Chưa có event nào — thử phát sự kiện <code className="inline-code">UserSessionChange</code>
                                </p>
                            ) : (
                                recentEvents.map((event) => (
                                    <div key={event.id} className="event-item">
                                        <p className="event-message">{event.message}</p>
                                        <p className="event-meta">{event.meta}</p>
                                    </div>
                                ))
                            )}
                        </div>
                    </section>
                </main>
            </div>

            <div className={`notification-toast ${notification.visible ? 'visible' : ''}`} role="status" aria-live="polite">
                {notification.message}
            </div>
        </div>
    );
}

export default App;
