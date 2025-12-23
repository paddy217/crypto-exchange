import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'
import { initEcho, disconnectEcho } from '../services/echo'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user')) || null)
  const token = ref(localStorage.getItem('token') || null)
  const assets = ref([])
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const balance = computed(() => user.value?.balance || 0)

  async function login(email, password) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/login', { email, password })
      token.value = response.data.token
      user.value = response.data.user

      localStorage.setItem('token', token.value)
      localStorage.setItem('user', JSON.stringify(user.value))

      initEcho()
      await fetchProfile()

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(name, email, password, passwordConfirmation) {
    loading.value = true
    error.value = null

    try {
      const response = await api.post('/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      token.value = response.data.token
      user.value = response.data.user

      localStorage.setItem('token', token.value)
      localStorage.setItem('user', JSON.stringify(user.value))

      initEcho()
      await fetchProfile()

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      disconnectEcho()
      token.value = null
      user.value = null
      assets.value = []
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  async function fetchProfile() {
    if (!token.value) return

    try {
      const response = await api.get('/profile')
      user.value = response.data.user
      assets.value = response.data.assets
      localStorage.setItem('user', JSON.stringify(user.value))
    } catch (err) {
      console.error('Failed to fetch profile:', err)
    }
  }

  function updateBalance(newBalance) {
    if (user.value) {
      user.value.balance = newBalance
      localStorage.setItem('user', JSON.stringify(user.value))
    }
  }

  function updateAssets(newAssets) {
    assets.value = newAssets
  }

  return {
    user,
    token,
    assets,
    loading,
    error,
    isAuthenticated,
    balance,
    login,
    register,
    logout,
    fetchProfile,
    updateBalance,
    updateAssets,
  }
})
