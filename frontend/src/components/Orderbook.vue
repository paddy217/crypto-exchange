<script setup>
import { computed, watch, toRef } from 'vue'
import { useOrdersStore } from '../stores/orders'

const props = defineProps({
  theme: { type: String, default: 'light' },
})
const theme = toRef(props, 'theme')

const ordersStore = useOrdersStore()

const symbols = ['BTC', 'ETH']

function selectSymbol(symbol) {
  ordersStore.setSelectedSymbol(symbol)
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
</script>

<template>
  <div :class="[ theme === 'dark' ? 'bg-gray-800 text-white' : 'bg-white text-gray-900', 'rounded-lg p-6' ]">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold">Orderbook</h2>

      <!-- Symbol Tabs -->
      <div class="flex space-x-2">
        <button
          v-for="sym in symbols"
          :key="sym"
          @click="selectSymbol(sym)"
          :class="[
            'px-4 py-1 rounded-md text-sm font-medium transition-colors',
            ordersStore.selectedSymbol === sym
              ? 'bg-blue-600 text-white'
              : (theme === 'dark' ? 'bg-gray-700 text-gray-400 hover:bg-gray-600' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')
          ]"
        >
          {{ sym }}
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <!-- Buy Orders -->
      <div>
        <h3 class="text-sm font-medium text-green-500 mb-2">Buy Orders (Bids)</h3>
        <div :class="[ 'rounded-md overflow-hidden', theme === 'dark' ? 'bg-gray-700/30' : 'bg-gray-100/50' ]">
          <div :class="[ 'grid grid-cols-2 gap-2 px-3 py-2 text-xs', theme === 'dark' ? 'bg-gray-700/50 text-gray-400' : 'bg-gray-100 text-gray-600' ]">
            <div>Price (USD)</div>
            <div class="text-right">Amount</div>
          </div>
          <div class="max-h-64 overflow-y-auto">
            <div
              v-for="order in ordersStore.orderbook.buy"
              :key="order.id"
              :class="[ 'grid grid-cols-2 gap-2 px-3 py-2 text-sm', theme === 'dark' ? 'hover:bg-gray-700/50' : 'hover:bg-gray-200' ]"
            >
              <div class="text-green-500">${{ formatPrice(order.price) }}</div>
              <div :class="[ 'text-right font-medium', theme === 'dark' ? 'text-white' : 'text-gray-900' ]">{{ formatAmount(order.amount) }}</div>
            </div>
            <div v-if="ordersStore.orderbook.buy.length === 0" :class="[ 'px-3 py-4 text-center text-sm', theme === 'dark' ? 'text-gray-500' : 'text-gray-600' ]">
              No buy orders
            </div>
          </div>
        </div>
      </div>

      <!-- Sell Orders -->
      <div>
        <h3 class="text-sm font-medium text-red-500 mb-2">Sell Orders (Asks)</h3>
        <div :class="[ 'rounded-md overflow-hidden', theme === 'dark' ? 'bg-gray-700/30' : 'bg-gray-100/50' ]">
          <div :class="[ 'grid grid-cols-2 gap-2 px-3 py-2 text-xs', theme === 'dark' ? 'bg-gray-700/50 text-gray-400' : 'bg-gray-100 text-gray-600' ]">
            <div>Price (USD)</div>
            <div class="text-right">Amount</div>
          </div>
          <div class="max-h-64 overflow-y-auto">
            <div
              v-for="order in ordersStore.orderbook.sell"
              :key="order.id"
              :class="[ 'grid grid-cols-2 gap-2 px-3 py-2 text-sm', theme === 'dark' ? 'hover:bg-gray-700/50' : 'hover:bg-gray-200' ]"
            >
              <div class="text-red-500">${{ formatPrice(order.price) }}</div>
              <div :class="[ 'text-right font-medium', theme === 'dark' ? 'text-white' : 'text-gray-900' ]">{{ formatAmount(order.amount) }}</div>
            </div>
            <div v-if="ordersStore.orderbook.sell.length === 0" :class="[ 'px-3 py-4 text-center text-sm', theme === 'dark' ? 'text-gray-500' : 'text-gray-600' ]">
              No sell orders
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
