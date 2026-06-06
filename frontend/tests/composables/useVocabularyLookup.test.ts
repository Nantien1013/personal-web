import { describe, it, expect, vi, beforeEach } from 'vitest'
import { ref } from 'vue'

vi.mock('#app', () => ({
  useRuntimeConfig: () => ({ public: { apiBase: 'http://localhost:8000/api' } }),
}))

vi.mock('../../composables/useApi', () => ({
  useApi: () => () => Promise.resolve(null),
}))

// Provide Nuxt auto-imports that are not available in vitest context
;(globalThis as Record<string, unknown>).ref = ref
;(globalThis as Record<string, unknown>).useApi = (await import('../../composables/useApi')).useApi

const { useVocabularyLookup } = await import('../../composables/useVocabularyLookup')

describe('useVocabularyLookup', () => {
  it('空字串不發請求', async () => {
    const fetchSpy = vi.spyOn(global, 'fetch').mockResolvedValue(new Response('{}'))
    const { debouncedLookup, result } = useVocabularyLookup()
    debouncedLookup('')
    await new Promise(r => setTimeout(r, 600))
    expect(result.value).toBeNull()
    fetchSpy.mockRestore()
  })
})
