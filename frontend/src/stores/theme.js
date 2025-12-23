import { defineStore } from 'pinia'

export const useThemeStore = defineStore('theme', {
  state: () => ({
    theme: 'light',
  }),
  actions: {
    setTheme(t) {
      this.theme = t
      try {
        localStorage.setItem('theme', t)
      } catch (e) {}
      document.documentElement.classList.toggle('dark', t === 'dark')
    },

    toggleTheme() {
      this.setTheme(this.theme === 'dark' ? 'light' : 'dark')
    },

    init() {
      try {
        const saved = localStorage.getItem('theme')
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
        const t = saved || (prefersDark ? 'dark' : 'light')
        this.setTheme(t)
      } catch (e) {
        this.setTheme('light')
      }
    },
  },
})

export default useThemeStore
