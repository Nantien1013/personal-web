import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../stores/auth'
import { beforeEach, describe, expect, it } from 'vitest'

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('初始狀態為未登入', () => {
    const auth = useAuthStore()
    expect(auth.isLoggedIn).toBe(false)
    expect(auth.isAdmin).toBe(false)
  })

  it('setAuth 後 isLoggedIn 為 true', () => {
    const auth = useAuthStore()
    auth.setAuth('test-token', { email: 'admin@test.com', role: 'admin' })
    expect(auth.isLoggedIn).toBe(true)
    expect(auth.isAdmin).toBe(true)
    expect(auth.token).toBe('test-token')
  })

  it('clearAuth 清除狀態', () => {
    const auth = useAuthStore()
    auth.setAuth('test-token', { email: 'admin@test.com', role: 'admin' })
    auth.clearAuth()
    expect(auth.isLoggedIn).toBe(false)
    expect(auth.token).toBeNull()
  })

  it('一般用戶 isAdmin 為 false', () => {
    const auth = useAuthStore()
    auth.setAuth('token', { email: 'user@test.com', role: 'user' })
    expect(auth.isAdmin).toBe(false)
  })
})
