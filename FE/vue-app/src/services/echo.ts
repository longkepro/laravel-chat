import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import axios from 'axios'

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo<'pusher'>
    Laravel?: { userId?: number | string | null }
  }
}

window.Pusher = Pusher

const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  wsHost: import.meta.env.VITE_PUSHER_HOST || undefined,
  wsPort: import.meta.env.VITE_PUSHER_PORT ? Number(import.meta.env.VITE_PUSHER_PORT) : undefined,
  forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],

  // Dùng axios thay XHR mặc định để tự động gửi cookie session + XSRF token
  authorizer: (channel: { name: string }) => ({
    authorize: (socketId, callback) => {
      axios
        .post(
          `${import.meta.env.VITE_API_BASE_URL}/broadcasting/auth`,
          { socket_id: socketId, channel_name: channel.name },
          { withCredentials: true },
        )
        .then((res) => callback(null, res.data))
        .catch((err) => callback(err, null))
    },
  }),
})

export default echo
