<script setup>
import { ref, computed, toRef } from 'vue'
const props = defineProps({
  theme: { type: String, default: 'light' },
})
const theme = toRef(props, 'theme')
import { useOrdersStore } from '../stores/orders'
import { useAuthStore } from '../stores/auth'

const ordersStore = useOrdersStore()
const authStore = useAuthStore()

const symbol = ref('BTC')
const side = ref('buy')
const price = ref('')
const amount = ref('')
const error = ref('')
const success = ref('')

const total = computed(() => {
  const p = parseFloat(price.value) || 0
  const a = parseFloat(amount.value) || 0
  return (p * a).toFixed(2)
})

const commission = computed(() => {
  const t = parseFloat(total.value) || 0
  return (t * 0.015).toFixed(2)
})

async function handleSubmit() {
  error.value = ''
  success.value = ''

  if (!price.value || !amount.value) {
    error.value = 'Please fill in all fields'
    return
  }

  const result = await ordersStore.createOrder({
    symbol: symbol.value,
    side: side.value,
    price: parseFloat(price.value),
    amount: parseFloat(amount.value),
  })

  if (result.success) {
    success.value = `Order placed successfully!`
    price.value = ''
    amount.value = ''
    authStore.fetchProfile()
    setTimeout(() => {
      success.value = ''
    }, 3000)
  } else {
    error.value = result.error
  }
}
</script>

<template>
  <div :class="[theme === 'dark' ? 'bg-gray-800 text-white' : 'bg-white text-gray-900', 'rounded-lg p-6']">
    <h2 class="text-xl font-bold mb-4">Place Order</h2>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Error/Success Messages -->
      <div v-if="error" class="bg-red-500/10 border border-red-500 text-red-500 px-3 py-2 rounded text-sm">
        {{ error }}
      </div>
      <div v-if="success" class="bg-green-500/10 border border-green-500 text-green-500 px-3 py-2 rounded text-sm">
        {{ success }}
      </div>

      <!-- Symbol -->
      <div>
        <label :class="['block text-sm font-medium mb-1', theme === 'dark' ? 'text-gray-400' : 'text-gray-700']">Symbol</label>
        <select
          v-model="symbol"
          :class="[ 'w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500', theme === 'dark' ? 'bg-gray-700 border border-gray-600 text-white placeholder-gray-500' : 'bg-white border border-gray-300 text-gray-900 placeholder-gray-400' ]"
        >
          <option value="BTC">BTC</option>
          <option value="ETH">ETH</option>
        </select>
      </div>

      <!-- Side -->
      <div>
        <label :class="['block text-sm font-medium mb-1', theme === 'dark' ? 'text-gray-400' : 'text-gray-700']">Side</label>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            @click="side = 'buy'"
              :class="[
              'py-2 px-4 rounded-md font-medium transition-colors',
              side === 'buy'
                ? 'bg-green-600 text-white'
                : (theme === 'dark' ? 'bg-gray-700 text-gray-400 hover:bg-gray-600' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')
            ]"
          >
            Buy
          </button>
          <button
            type="button"
            @click="side = 'sell'"
            :class="[
              'py-2 px-4 rounded-md font-medium transition-colors',
              side === 'sell'
                ? 'bg-red-600 text-white'
                : (theme === 'dark' ? 'bg-gray-700 text-gray-400 hover:bg-gray-600' : 'bg-gray-100 text-gray-700 hover:bg-gray-200')
            ]"
          >
            Sell
          </button>
        </div>
      </div>

      <!-- Price -->
      <div>
        <label :class="['block text-sm font-medium mb-1', theme === 'dark' ? 'text-gray-400' : 'text-gray-700']">Price (USD)</label>
        <input
          v-model="price"
          type="number"
          step="0.00000001"
          min="0"
          placeholder="0.00"
          :class="[ 'w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500', theme === 'dark' ? 'bg-gray-700 border border-gray-600 text-white placeholder-gray-500' : 'bg-white border border-gray-300 text-gray-900 placeholder-gray-400' ]"
        />
      </div>

      <!-- Amount -->
      <div>
        <label :class="['block text-sm font-medium mb-1', theme === 'dark' ? 'text-gray-400' : 'text-gray-700']">Amount ({{ symbol }})</label>
        <input
          v-model="amount"
          type="number"
          step="0.00000001"
          min="0"
          placeholder="0.00000000"
          :class="[ 'w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500', theme === 'dark' ? 'bg-gray-700 border border-gray-600 text-white placeholder-gray-500' : 'bg-white border border-gray-300 text-gray-900 placeholder-gray-400' ]"
        />
      </div>

      <!-- Total & Commission -->
      <div :class="[ theme === 'dark' ? 'bg-gray-700/50' : 'bg-gray-100', 'rounded-md p-3 space-y-1' ]">
        <div class="flex justify-between text-sm">
          <span :class="theme === 'dark' ? 'text-gray-400' : 'text-gray-600'">Total</span>
          <span :class="theme === 'dark' ? 'text-white font-medium' : 'text-gray-900 font-medium'">${{ total }} USD</span>
        </div>
        <div class="flex justify-between text-sm">
          <span :class="theme === 'dark' ? 'text-gray-400' : 'text-gray-600'">Commission (1.5%)</span>
          <span class="text-yellow-500">${{ commission }} USD</span>
        </div>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        :disabled="ordersStore.loading"
        :class="[
          'w-full py-3 px-4 rounded-md font-medium text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
          side === 'buy'
            ? 'bg-green-600 hover:bg-green-700'
            : 'bg-red-600 hover:bg-red-700'
        ]"
      >
        <span v-if="ordersStore.loading">Processing...</span>
        <span v-else>{{ side === 'buy' ? 'Buy' : 'Sell' }} {{ symbol }}</span>
      </button>
    </form>
  </div>
</template>
