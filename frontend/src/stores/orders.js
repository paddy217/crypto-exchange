import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref([])
  const orderbook = ref({ buy: [], sell: [] })
  const selectedSymbol = ref('BTC')
  const loading = ref(false)
  const error = ref(null)

  const openOrders = computed(() =>
    orders.value.filter((o) => o.status === 1)
  )

  const filledOrders = computed(() =>
    orders.value.filter((o) => o.status === 2)
  )

  const cancelledOrders = computed(() =>
    orders.value.filter((o) => o.status === 3)
  )

  async function fetchOrderbook(symbol = null) {
    const sym = symbol || selectedSymbol.value
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/orders', {
        params: { symbol: sym },
      })
      orderbook.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch orderbook'
    } finally {
      loading.value = false
    }
  }

  async function fetchUserOrders() {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/user/orders')
      orders.value = response.data.orders
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch orders'
    } finally {
      loading.value = false
    }
  }

  async function createOrder(orderData) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/orders', orderData)
      await fetchUserOrders()
      await fetchOrderbook(orderData.symbol)
      return { success: true, order: response.data.order }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create order'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function cancelOrder(orderId) {
    loading.value = true
    error.value = null

    try {
      await api.post(`/orders/${orderId}/cancel`)
      await fetchUserOrders()
      await fetchOrderbook()
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to cancel order'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  function setSelectedSymbol(symbol) {
    selectedSymbol.value = symbol
    fetchOrderbook(symbol)
  }

  function updateOrderStatus(orderId, status) {
    const order = orders.value.find((o) => o.id === orderId)
    if (order) {
      order.status = status
    }
  }

  return {
    orders,
    orderbook,
    selectedSymbol,
    loading,
    error,
    openOrders,
    filledOrders,
    cancelledOrders,
    fetchOrderbook,
    fetchUserOrders,
    createOrder,
    cancelOrder,
    setSelectedSymbol,
    updateOrderStatus,
  }
})
