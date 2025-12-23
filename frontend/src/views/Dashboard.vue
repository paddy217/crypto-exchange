<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useOrdersStore } from '../stores/orders'
import { initEcho, getEcho, disconnectEcho } from '../services/echo'
import OrderForm from '../components/OrderForm.vue'
import WalletOverview from '../components/WalletOverview.vue'
import Orderbook from '../components/Orderbook.vue'
import OrderHistory from '../components/OrderHistory.vue'
import { storeToRefs } from 'pinia'
import { useThemeStore } from '../stores/theme'

const themeStore = useThemeStore()
const { theme } = storeToRefs(themeStore)

const router = useRouter()
const authStore = useAuthStore()
const ordersStore = useOrdersStore()

const notification = ref(null)
let currentOrderbookChannel = null

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

function setupEchoListeners() {
  const echo = initEcho()

  if (echo && authStore.user) {
    echo.private(`user.${authStore.user.id}`)
      .listen('.order.matched', (data) => {
        console.log('Order matched:', data)

        // Update balance
        authStore.updateBalance(data.user.balance)

        // Refresh profile to get updated assets
        authStore.fetchProfile()

        // Refresh orders
        ordersStore.fetchUserOrders()
        ordersStore.fetchOrderbook()

        // Show notification
        notification.value = {
          type: 'success',
          message: `Order matched! ${data.trade.side === 'buy' ? 'Bought' : 'Sold'} ${data.trade.amount} ${data.trade.symbol} at $${parseFloat(data.trade.price).toLocaleString()}`,
        }

        setTimeout(() => {
          notification.value = null
        }, 5000)
      })

    // Subscribe to global public orderbook channel (backend also broadcasts here)
    echo.channel('orderBook')
      .listen('.orderbook.updated', (payload) => {
        if (payload.isOrderBookUpdated) {
          ordersStore.fetchOrderbook()
        }
      })
  }
}

onMounted(async () => {
  await authStore.fetchProfile()
  await ordersStore.fetchOrderbook()
  await ordersStore.fetchUserOrders()
  setupEchoListeners()
})

onUnmounted(() => {
  const echo = getEcho()
  if (echo && authStore.user) {
    echo.leave(`user.${authStore.user.id}`)
  }
  if (echo) {
    try { echo.leaveChannel('orderBook') } catch (e) { /* ignore */ }
  }
})
</script>

<template>
  <div class="min-h-screen">
    <!-- Navbar -->
    <nav :class="['border-b', theme === 'dark' ? 'bg-gray-800 text-white border-gray-700' : 'bg-gray-200 text-gray-900 border-gray-300']" >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-bold">Crypto Exchange</h1>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="themeStore.toggleTheme()" class="px-3 py-1 rounded border dark:border-gray-700 bg-gray-100 dark:bg-gray-800 text-sm">
              <span v-if="theme === 'dark'">Switch to Light</span>
              <span v-else>Switch to Dark</span>
            </button>

            <span :class="[theme === 'dark' ? 'text-gray-300' : 'text-gray-900']">{{ authStore.user?.name }}</span>
            <button
              @click="handleLogout"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Notification -->
    <div
      v-if="notification"
      class="fixed top-20 right-4 z-50 max-w-sm"
    >
      <div
        :class="[
          'px-4 py-3 rounded-lg shadow-lg',
          notification.type === 'success' ? 'bg-green-600' : 'bg-red-600'
        ]"
      >
        <p class="text-white font-medium">{{ notification.message }}</p>
      </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Order Form + Wallet -->
        <div class="lg:col-span-1 space-y-6">
          <OrderForm :theme="theme" />
          <WalletOverview :theme="theme" />
        </div>

        <!-- Right Column: Orderbook + Order History -->
        <div class="lg:col-span-2 space-y-6">
          <Orderbook :theme="theme" />
          <OrderHistory :theme="theme" />
        </div>
      </div>
    </main>
  </div>
</template>
