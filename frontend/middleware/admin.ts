export default defineNuxtRouteMiddleware(() => {
  if (!import.meta.client) return

  const auth = useAuthStore()
  auth.init()

  if (!auth.isLoggedIn) {
    return navigateTo('/admin/login')
  }
})
