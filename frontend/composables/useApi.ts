export const useApi = () => {
  const config = useRuntimeConfig()
  const auth = useAuthStore()

  return <T = unknown>(
    url: string,
    options: Parameters<typeof $fetch>[1] = {}
  ): Promise<T> => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...(options.headers as Record<string, string> ?? {}),
    }

    if (auth.token) {
      headers['Authorization'] = `Bearer ${auth.token}`
    }

    return $fetch<T>(url, {
      baseURL: config.public.apiBase,
      ...options,
      headers,
    })
  }
}
