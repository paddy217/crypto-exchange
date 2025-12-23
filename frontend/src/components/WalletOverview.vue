<script setup>
import { computed, toRef } from 'vue'
import { useAuthStore } from '../stores/auth'

const props = defineProps({
  theme: { type: String, default: 'light' },
})
const theme = toRef(props, 'theme')

const authStore = useAuthStore()

const formattedBalance = computed(() => {
  const balance = parseFloat(authStore.balance) || 0
  return balance.toLocaleString('en-US', {
    style: 'currency',
    currency: 'USD',
  })
})
</script>

<template>
  <div :class="[ theme === 'dark' ? 'bg-gray-800 text-white' : 'bg-white text-gray-900', 'rounded-lg p-6' ]">
    <h2 class="text-xl font-bold mb-4">Wallet</h2>

    <!-- USD Balance -->
    <div class="mb-6">
      <div :class="theme === 'dark' ? 'text-sm text-gray-400 mb-1' : 'text-sm text-gray-700 mb-1'">USD Balance</div>
      <div class="text-2xl font-bold text-green-500">{{ formattedBalance }}</div>
    </div>

    <!-- Asset Balances -->
    <div>
      <div :class="theme === 'dark' ? 'text-sm text-gray-400 mb-3' : 'text-sm text-gray-700 mb-3'">Assets</div>

      <div v-if="authStore.assets.length === 0" :class="theme === 'dark' ? 'text-gray-500 text-sm' : 'text-gray-600 text-sm'">
        No assets yet
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="asset in authStore.assets"
          :key="asset.symbol"
          :class="[ 'rounded-md p-3', theme === 'dark' ? 'bg-gray-700/50' : 'bg-gray-100' ]"
        >
          <div class="flex justify-between items-center">
            <div>
              <div class="font-medium">{{ asset.symbol }}</div>
              <div :class="theme === 'dark' ? 'text-xs text-gray-400' : 'text-xs text-gray-600'">
                Locked: {{ parseFloat(asset.locked_amount).toFixed(8) }}
              </div>
            </div>
            <div class="text-right">
              <div class="font-medium">
                {{ parseFloat(asset.amount).toFixed(8) }}
              </div>
              <div :class="theme === 'dark' ? 'text-xs text-gray-400' : 'text-xs text-gray-600'">Available</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
