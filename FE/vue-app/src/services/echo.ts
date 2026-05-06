import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import axios from 'axios';
import { getAuthToken } from '@/lib/auth-token';

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo<'pusher'>
    Laravel?: { userId?: number | string | null }
  }
}

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  wsHost: import.meta.env.VITE_PUSHER_HOST || undefined,
  wsPort: import.meta.env.VITE_PUSHER_PORT ? Number(import.meta.env.VITE_PUSHER_PORT) : undefined,
  forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
  authorizer: (channel: { name: string }) => ({
    authorize: (socketId, callback) => {
      const token = getAuthToken();

      axios
        .post(
          `${import.meta.env.VITE_API_BASE_URL}/broadcasting/auth`,
          { socket_id: socketId, channel_name: channel.name },
          {
            headers: {
              Accept: 'application/json',
              ...(token ? { Authorization: `Bearer ${token}` } : {}),
            },
          },
        )
        .then((res) => callback(null, res.data))
        .catch((err) => callback(err, null));
    },
  }),
});

export default echo;
