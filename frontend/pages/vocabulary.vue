<script setup lang="ts">
useSeoMeta({ title: '論文單字庫' })

const auth = useAuthStore()
const api = useApi()

onMounted(() => auth.init())

type Mode = 'browse' | 'add' | 'review'
const mode = ref<Mode>('browse')

// 單字列表
const words = ref<any[]>([])
const stats = ref({ total: 0, added_this_week: 0, pending_review: 0, avg_familiarity: 0 })
const reviewQueue = ref<any[]>([])
const reviewIndex = ref(0)
const loading = ref(true)
const error = ref('')
const searchQuery = ref('')

// 新增表單
const wordForm = reactive({
  word: '', meaning: '', part_of_speech: '', phonetic: '',
  audio_url: '', example: '', example_zh: '', source: '', note: '', auto_filled: false,
})

const fetchAll = async () => {
  loading.value = true
  try {
    const params = searchQuery.value ? `?search=${encodeURIComponent(searchQuery.value)}` : ''
    const [w, s, q] = await Promise.all([
      api<any[]>(`/vocabulary${params}`),
      api<typeof stats.value>('/vocabulary/stats'),
      api<any[]>('/vocabulary/review-queue'),
    ])
    words.value = w
    stats.value = s
    reviewQueue.value = q
    reviewIndex.value = 0
  } catch {
    error.value = '載入失敗，請稍後再試。'
  } finally {
    loading.value = false
  }
}

onMounted(fetchAll)

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchAll(), 500)
})

const handleLookupFill = (result: any) => {
  wordForm.word = result.word
  wordForm.meaning = result.meaning ?? ''
  wordForm.part_of_speech = result.part_of_speech ?? ''
  wordForm.phonetic = result.phonetic ?? ''
  wordForm.audio_url = result.audio_url ?? ''
  wordForm.example = result.example ?? ''
  wordForm.auto_filled = true
}

const submitWord = async () => {
  try {
    await api('/vocabulary', { method: 'POST', body: { ...wordForm } })
    Object.assign(wordForm, { word: '', meaning: '', part_of_speech: '', phonetic: '',
      audio_url: '', example: '', example_zh: '', source: '', note: '', auto_filled: false })
    mode.value = 'browse'
    await fetchAll()
  } catch (e: any) {
    if (e?.status === 422) alert('此單字已存在於資料庫。')
    else alert('儲存失敗。')
  }
}

const handleReview = async (id: number, result: string) => {
  await api(`/vocabulary/${id}/review`, { method: 'PUT', body: { result } })
  if (reviewIndex.value < reviewQueue.value.length - 1) {
    reviewIndex.value++
  } else {
    alert('今日複習完成！')
    mode.value = 'browse'
    await fetchAll()
  }
}

const handleDelete = async (id: number) => {
  if (!confirm('確定刪除這個單字嗎？')) return
  try {
    await api(`/vocabulary/${id}`, { method: 'DELETE' })
    await fetchAll()
  } catch {
    alert('刪除失敗。')
  }
}
</script>

<template>
  <div class="vocabulary-page">
    <div class="stats-bar">
      <span>共 {{ stats.total }} 字</span>
      <span>本週新增 {{ stats.added_this_week }}</span>
      <span>待複習 {{ stats.pending_review }}</span>
      <span>平均熟悉度 {{ stats.avg_familiarity }}</span>
    </div>

    <div class="mode-tabs">
      <button :class="{ active: mode === 'browse' }" @click="mode = 'browse'">瀏覽</button>
      <button v-if="auth.isAdmin" :class="{ active: mode === 'add' }" @click="mode = 'add'">新增</button>
      <button :class="{ active: mode === 'review' }" @click="mode = 'review'">
        抽卡複習 <span v-if="stats.pending_review > 0">({{ stats.pending_review }})</span>
      </button>
    </div>

    <!-- 瀏覽模式 -->
    <div v-if="mode === 'browse'">
      <input v-model="searchQuery" type="search" placeholder="搜尋單字或中文..." />
      <div v-if="loading">載入中...</div>
      <div v-else-if="words.length === 0" class="empty">尚無單字，快去新增！</div>
      <div v-else class="words-grid">
        <VocabularyWordCard
          v-for="w in words" :key="w.id" :word="w"
          @edit="() => {}"
          @delete="handleDelete"
        />
      </div>
    </div>

    <!-- 新增模式 -->
    <div v-if="mode === 'add'" class="add-mode">
      <VocabularyLookupForm @fill="handleLookupFill" />
      <form @submit.prevent="submitWord" class="word-form">
        <label>英文單字 *<input v-model="wordForm.word" required /></label>
        <label>中文解釋 *<textarea v-model="wordForm.meaning" required rows="2" /></label>
        <label>詞性<input v-model="wordForm.part_of_speech" /></label>
        <label>KK 音標<input v-model="wordForm.phonetic" /></label>
        <label>例句<textarea v-model="wordForm.example" rows="2" /></label>
        <label>例句（中文）<textarea v-model="wordForm.example_zh" rows="2" /></label>
        <label>來源論文<input v-model="wordForm.source" /></label>
        <label>備註<textarea v-model="wordForm.note" rows="2" /></label>
        <button type="submit">儲存單字</button>
      </form>
    </div>

    <!-- 抽卡複習模式 -->
    <div v-if="mode === 'review'">
      <div v-if="reviewQueue.length === 0" class="empty">今日無待複習單字！</div>
      <div v-else>
        <p>{{ reviewIndex + 1 }} / {{ reviewQueue.length }}</p>
        <VocabularyFlashCard
          :word="reviewQueue[reviewIndex]"
          @review="handleReview"
        />
      </div>
    </div>
  </div>
</template>
