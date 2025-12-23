<script setup>
import { computed, toRef } from 'vue'
import { useOrdersStore } from '../stores/orders'
import { useAuthStore } from '../stores/auth'

const props = defineProps({
  theme: { type: String, default: 'light' },
})
const theme = toRef(props, 'theme')

const ordersStore = useOrdersStore()
const authStore = useAuthStore()

function getStatusLabel(status) {
  switch (status) {
    case 1:
      return 'Open'
    case 2:
      return 'Filled'
    case 3:
      return 'Cancelled'
    default:
      return 'Unknown'
  }
}

function getStatusClass(status) {
  switch (status) {
    case 1:
      return 'bg-blue-500/20 text-blue-400'
    case 2:
      return 'bg-green-500/20 text-green-400'
    case 3:
      return 'bg-gray-500/20 text-gray-400'
    default:
      return 'bg-gray-500/20 text-gray-400'
  }
}

function formatPrice(price) {
  return parseFloat(price).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function formatAmount(amount) {
  return parseFloat(amount).toFixed(8)
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleString()
}

async function cancelOrder(orderId) {
  const result = await ordersStore.cancelOrder(orderId)
  if (result.success) {
    authStore.fetchProfile()
  }
}
</script>

<template>
  <div :class="[ theme === 'dark' ? 'bg-gray-800 text-white' : 'bg-white text-gray-900', 'rounded-lg p-6' ]">
    <h2 class="text-xl font-bold mb-4">Order History</h2>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr :class="[ 'text-left text-xs', theme === 'dark' ? 'text-gray-400 border-b border-gray-700' : 'text-gray-600 border-b border-gray-200' ]">
            <th class="pb-3 font-medium">Date</th>
            <th class="pb-3 font-medium">Symbol</th>
            <th class="pb-3 font-medium">Side</th>
            <th class="pb-3 font-medium">Price</th>
            <th class="pb-3 font-medium">Amount</th>
            <th class="pb-3 font-medium">Status</th>
            <th class="pb-3 font-medium">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="order in ordersStore.orders"
            :key="order.id"
            :class="[ 'border-b', theme === 'dark' ? 'border-gray-700/50 hover:bg-gray-700/30' : 'border-gray-200 hover:bg-gray-100' ]"
          >
            <td :class="[ 'py-3 text-sm', theme === 'dark' ? 'text-gray-300' : 'text-gray-700' ]">
              {{ formatDate(order.created_at) }}
            </td>
            <td :class="[ 'py-3 text-sm font-medium', theme === 'dark' ? 'text-white' : 'text-gray-900' ]">
              {{ order.symbol }}
            </td>
            <td class="py-3">
              <span
                :class="[
                  'px-2 py-1 rounded text-xs font-medium',
                  order.side === 'buy'
                    ? 'bg-green-500/20 text-green-400'
                    : 'bg-red-500/20 text-red-400'
                ]"
              >
                {{ order.side.toUpperCase() }}
              </span>
            </td>
            <td :class="[ 'py-3 text-sm', theme === 'dark' ? 'text-white' : 'text-gray-900' ]">
              ${{ formatPrice(order.price) }}
            </td>
            <td :class="[ 'py-3 text-sm', theme === 'dark' ? 'text-white' : 'text-gray-900' ]">
              {{ formatAmount(order.amount) }}
            </td>
            <td class="py-3">
              <span
                :class="[
                  'px-2 py-1 rounded text-xs font-medium',
                  getStatusClass(order.status)
                ]"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </td>
            <td class="py-3">
              <button
                v-if="order.status === 1"
                @click="cancelOrder(order.id)"
                :disabled="ordersStore.loading"
                :class="[ 'px-3 py-1 text-xs font-medium rounded transition-colors disabled:opacity-50', theme === 'dark' ? 'text-red-400 hover:text-red-300 hover:bg-red-500/10' : 'text-red-600 hover:text-red-500 hover:bg-red-100' ]"
              >
                Cancel
              </button>
              <span v-else :class="theme === 'dark' ? 'text-gray-500 text-xs' : 'text-gray-500 text-xs'">-</span>
            </td>
          </tr>
          <tr v-if="ordersStore.orders.length === 0">
            <td colspan="7" :class="[ 'py-8 text-center', theme === 'dark' ? 'text-gray-500' : 'text-gray-600' ]">
              No orders yet
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
