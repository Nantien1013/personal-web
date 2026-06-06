<script setup lang="ts">
interface Category { id: number; name: string; group: string }
interface WorkForm {
  type: 'anime' | 'manga'
  title: string
  title_original: string
  cover_url: string
  status: 'plan' | 'watching' | 'completed' | 'on_hold' | 'dropped'
  rating: number | null
  is_favorite: boolean
  release_year: number | null
  release_season: string
  media_type: string
  source_type: string
  episodes_total: number | null
  episodes_watched: number
  volumes_total: number | null
  volumes_read: number
  author: string
  studio: string
  note: string
  category_ids: number[]
}

const props = defineProps<{
  modelValue: boolean
  work?: WorkForm & { id?: number }
  categories: Category[]
}>()

const emit = defineEmits<{
  'update:modelValue': [v: boolean]
  submit: [form: WorkForm & { id?: number }]
}>()

const isAnime = computed(() => form.type === 'anime')

const defaultForm = (): WorkForm => ({
  type: 'anime', title: '', title_original: '', cover_url: '',
  status: 'plan', rating: null, is_favorite: false,
  release_year: null, release_season: '', media_type: '',
  source_type: '', episodes_total: null, episodes_watched: 0,
  volumes_total: null, volumes_read: 0, author: '', studio: '',
  note: '', category_ids: [],
})

const form = reactive<WorkForm & { id?: number }>(defaultForm())

watch(() => props.work, (w) => {
  if (w) {
    Object.assign(form, w)
  } else {
    Object.assign(form, defaultForm())
    delete form.id
  }
}, { immediate: true })

const groupedCategories = computed(() =>
  ['theme', 'source', 'media_type'].map(group => ({
    group,
    label: { theme: '主題', source: '來源', media_type: '媒體類型' }[group],
    items: props.categories.filter(c => c.group === group),
  }))
)

const toggleCategory = (id: number) => {
  const idx = form.category_ids.indexOf(id)
  if (idx >= 0) form.category_ids.splice(idx, 1)
  else form.category_ids.push(id)
}

const handleSubmit = () => emit('submit', { ...form })
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="modal-overlay" @click.self="emit('update:modelValue', false)">
      <div class="modal">
        <h2>{{ form.id ? '編輯' : '新增' }}作品</h2>
        <form @submit.prevent="handleSubmit" class="modal-form">
          <label>類型
            <select v-model="form.type">
              <option value="anime">動漫</option>
              <option value="manga">漫畫</option>
            </select>
          </label>
          <label>標題 *<input v-model="form.title" required /></label>
          <label>原文標題<input v-model="form.title_original" /></label>
          <label>封面圖 URL<input v-model="form.cover_url" type="url" /></label>
          <label>狀態
            <select v-model="form.status">
              <option value="plan">計畫</option>
              <option value="watching">觀看中</option>
              <option value="completed">已完成</option>
              <option value="on_hold">擱置</option>
              <option value="dropped">放棄</option>
            </select>
          </label>
          <label>評分（0-5）<input v-model.number="form.rating" type="number" min="0" max="5" /></label>
          <label><input type="checkbox" v-model="form.is_favorite" /> 最愛</label>
          <label>發行年份<input v-model.number="form.release_year" type="number" min="1900" max="2100" /></label>
          <label v-if="isAnime">番別
            <select v-model="form.release_season">
              <option value="">無</option>
              <option value="winter">冬番</option>
              <option value="spring">春番</option>
              <option value="summer">夏番</option>
              <option value="autumn">秋番</option>
            </select>
          </label>
          <template v-if="isAnime">
            <label>總集數<input v-model.number="form.episodes_total" type="number" min="0" /></label>
            <label>已看集數<input v-model.number="form.episodes_watched" type="number" min="0" /></label>
            <label>動畫公司<input v-model="form.studio" /></label>
          </template>
          <template v-else>
            <label>總卷數<input v-model.number="form.volumes_total" type="number" min="0" /></label>
            <label>已讀卷數<input v-model.number="form.volumes_read" type="number" min="0" /></label>
            <label>作者<input v-model="form.author" /></label>
          </template>
          <label>心得<textarea v-model="form.note" rows="3" /></label>

          <div v-for="group in groupedCategories" :key="group.group">
            <p class="category-group-label">{{ group.label }}</p>
            <div class="category-tags">
              <button
                v-for="cat in group.items" :key="cat.id"
                type="button"
                :class="{ active: form.category_ids.includes(cat.id) }"
                @click="toggleCategory(cat.id)"
              >{{ cat.name }}</button>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" @click="emit('update:modelValue', false)">取消</button>
            <button type="submit">{{ form.id ? '儲存' : '新增' }}</button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>
