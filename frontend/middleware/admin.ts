export default defineNuxtRouteMiddleware(() => {
  if (!import.meta.client) return

  const auth = useAuthStore()

  if (!auth.isLoggedIn) {
    return navigateTo('/admin/login')
  }

  if (!auth.isAdmin) {
    return navigateTo('/')
  }
})
