<script setup lang="ts">
useSeoMeta({ title: '收藏' })

const auth = useAuthStore()
const api = useApi()

onMounted(() => auth.init())

// 資料
const works = ref<any[]>([])
const categories = ref<any[]>([])
const stats = ref({ total: 0, anime: 0, manga: 0, completed: 0, watching: 0, favorites: 0 })
const loading = ref(true)
const error = ref('')

// 篩選狀態
const filters = reactive({ type: '', status: '', search: '', season: '', favorite: false })

// Modal 狀態
const showModal = ref(false)
const editingWork = ref<any>(null)

const fetchAll = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = new URLSearchParams()
    if (filters.type)     params.set('type', filters.type)
    if (filters.status)   params.set('status', filters.status)
    if (filters.search)   params.set('search', filters.search)
    if (filters.season)   params.set('season', filters.season)
    if (filters.favorite) params.set('favorite', '1')

    const [w, c, s] = await Promise.all([
      api<any[]>(`/collection?${params}`),
      api<any[]>('/categories'),
      api<typeof stats.value>('/collection/stats'),
    ])
    works.value = w
    categories.value = c
    stats.value = s
  } catch {
    error.value = '載入失敗，請稍後再試。'
  } finally {
    loading.value = false
  }
}

onMounted(fetchAll)
watch(filters, fetchAll, { deep: true })

const openCreate = () => {
  editingWork.value = null
  showModal.value = true
}

const openEdit = (work: any) => {
  editingWork.value = {
    ...work,
    category_ids: work.categories.map((c: any) => c.id),
  }
  showModal.value = true
}

const handleSubmit = async (form: any) => {
  try {
    if (form.id) {
      await api(`/collection/${form.id}`, { method: 'PUT', body: form })
    } else {
      await api('/collection', { method: 'POST', body: form })
    }
    showModal.value = false
    await fetchAll()
  } catch {
    alert('儲存失敗，請確認是否已登入。')
  }
}

const handleDelete = async (id: number) => {
  if (!confirm('確定要刪除這筆收藏嗎？')) return
  try {
    await api(`/collection/${id}`, { method: 'DELETE' })
    await fetchAll()
  } catch {
    alert('刪除失敗。')
  }
}
</script>

<template>
  <div class="collection-page">
    <div class="stats-bar">
      <span>共 {{ stats.total }} 筆</span>
      <span>動漫 {{ stats.anime }} / 漫畫 {{ stats.manga }}</span>
      <span>已完成 {{ stats.completed }}</span>
      <span>觀看中 {{ stats.watching }}</span>
      <span>最愛 {{ stats.favorites }}</span>
    </div>

    <div class="toolbar">
      <CollectionFilterBar v-model="filters" />
      <button v-if="auth.isAdmin" @click="openCreate" class="btn btn-primary">+ 新增</button>
    </div>

    <div v-if="loading" class="loading">載入中...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="works.length === 0" class="empty">尚無收藏，快去新增第一筆吧！</div>
    <div v-else class="works-grid">
      <CollectionWorkCard
        v-for="work in works"
        :key="work.id"
        :work="work"
        @edit="openEdit"
        @delete="handleDelete"
      />
    </div>

    <CollectionWorkModal
      v-model="showModal"
      :work="editingWork"
      :categories="categories"
      @submit="handleSubmit"
    />
  </div>
</template>
