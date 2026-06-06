interface LookupResult {
  word: string
  meaning: string | null
  part_of_speech: string | null
  phonetic: string | null
  audio_url: string | null
  example: string | null
}

export const useVocabularyLookup = () => {
  const api = useApi()
  const result = ref<LookupResult | null>(null)
  const loading = ref(false)
  const error = ref('')
  let timer: ReturnType<typeof setTimeout> | null = null

  const lookup = async (word: string) => {
    const trimmed = word.trim()
    if (!trimmed) { result.value = null; return }

    loading.value = true
    error.value = ''
    try {
      result.value = await api<LookupResult>(`/vocabulary/lookup?word=${encodeURIComponent(trimmed)}`)
    } catch {
      error.value = 'API 查詢失敗，請手動填寫。'
      result.value = null
    } finally {
      loading.value = false
    }
  }

  const debouncedLookup = (word: string) => {
    if (timer) clearTimeout(timer)
    timer = setTimeout(() => lookup(word), 500)
  }

  return { result, loading, error, debouncedLookup, lookup }
}
