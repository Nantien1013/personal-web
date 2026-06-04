import { defineStore } from 'pinia'

export interface AuthUser {
  email: string
  role: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as AuthUser | null,
  }),

  getters: {
    isLoggedIn: (state): boolean => !!state.token,
    isAdmin: (state): boolean => state.user?.role === 'admin',
  },

  actions: {
    init() {
      if (import.meta.client) {
        this.token = sessionStorage.getItem('auth_token')
        const raw = sessionStorage.getItem('auth_user')
        this.user = raw ? JSON.parse(raw) : null
      }
    },

    setAuth(token: string, user: AuthUser) {
      this.token = token
      this.user = user
      if (import.meta.client) {
        sessionStorage.setItem('auth_token', token)
        sessionStorage.setItem('auth_user', JSON.stringify(user))
      }
    },

    clearAuth() {
      this.token = null
      this.user = null
      if (import.meta.client) {
        sessionStorage.removeItem('auth_token')
        sessionStorage.removeItem('auth_user')
      }
    },
  },
})
