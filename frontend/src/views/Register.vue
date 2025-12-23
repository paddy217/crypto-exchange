<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMessage = ref('')

async function handleRegister() {
  errorMessage.value = ''

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Passwords do not match'
    return
  }

  const success = await authStore.register(
    name.value,
    email.value,
    password.value,
    passwordConfirmation.value
  )

  if (success) {
    router.push('/dashboard')
  } else {
    errorMessage.value = authStore.error || 'Registration failed'
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
          Crypto Exchange
        </h2>
        <p class="mt-2 text-center text-sm text-gray-400">
          Create a new account
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div v-if="errorMessage" class="bg-red-500/10 border border-red-500 text-red-500 px-4 py-3 rounded">
          {{ errorMessage }}
        </div>

        <div class="rounded-md shadow-sm space-y-2">
          <div>
            <label for="name" class="sr-only">Name</label>
            <input
              id="name"
              v-model="name"
              type="text"
              required
              class="appearance-none relative block w-full px-3 py-2 border border-gray-700 placeholder-gray-500 text-white bg-gray-800 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Full name"
            />
          </div>
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="appearance-none relative block w-full px-3 py-2 border border-gray-700 placeholder-gray-500 text-white bg-gray-800 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Email address"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              minlength="8"
              class="appearance-none relative block w-full px-3 py-2 border border-gray-700 placeholder-gray-500 text-white bg-gray-800 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Password (min 8 characters)"
            />
          </div>
          <div>
            <label for="password_confirmation" class="sr-only">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="passwordConfirmation"
              type="password"
              required
              class="appearance-none relative block w-full px-3 py-2 border border-gray-700 placeholder-gray-500 text-white bg-gray-800 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Confirm password"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="authStore.loading">Creating account...</span>
            <span v-else>Create account</span>
          </button>
        </div>

        <div class="text-center">
          <router-link to="/login" class="text-blue-400 hover:text-blue-300">
            Already have an account? Sign in
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>
