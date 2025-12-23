<script setup>
import { RouterView } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useThemeStore } from './stores/theme'

const themeStore = useThemeStore()
const { theme } = storeToRefs(themeStore)
</script>

<template>
  <div :class="['min-h-screen', theme === 'dark' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900']">
    <RouterView />
  </div>
</template>
