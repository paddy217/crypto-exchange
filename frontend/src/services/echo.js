import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echo = null

export function initEcho() {
  if (echo) return echo

  echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    },
  })

  return echo
}

export function getEcho() {
  return echo
}

export function disconnectEcho() {
  if (echo) {
    echo.disconnect()
    echo = null
  }
}
