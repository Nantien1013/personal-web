export const useAuth = () => {
  const auth = useAuthStore()
  const config = useRuntimeConfig()

  const login = async (email: string, password: string): Promise<void> => {
    const res = await $fetch<{ token: string; user: { email: string; role: string } }>(
      '/auth/login',
      {
        baseURL: config.public.apiBase,
        method: 'POST',
        body: { email, password },
        headers: { Accept: 'application/json' },
      }
    )
    auth.setAuth(res.token, res.user)
  }

  const logout = async (): Promise<void> => {
    const api = useApi()
    await api('/auth/logout', { method: 'POST' }).catch(() => null)
    auth.clearAuth()
  }

  return { login, logout, isLoggedIn: computed(() => auth.isLoggedIn) }
}
