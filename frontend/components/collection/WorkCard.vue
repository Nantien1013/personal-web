<script setup lang="ts">
interface Work {
  id: number
  type: 'anime' | 'manga'
  title: string
  title_original?: string
  cover_url?: string
  status: 'plan' | 'watching' | 'completed' | 'on_hold' | 'dropped'
  rating?: number
  is_favorite: boolean
  release_year?: number
  release_season?: string
  categories: Array<{ id: number; name: string; group: string }>
}

const props = defineProps<{ work: Work }>()
const emit = defineEmits<{
  edit: [work: Work]
  delete: [id: number]
}>()

const statusLabel: Record<string, string> = {
  watching: '觀看中', completed: '已完成', plan: '計畫',
  on_hold: '擱置', dropped: '放棄',
}

const seasonLabel: Record<string, string> = {
  winter: '冬番', spring: '春番', summer: '夏番', autumn: '秋番',
}
</script>

<template>
  <div class="work-card">
    <img v-if="work.cover_url" :src="work.cover_url" :alt="work.title" class="cover" />
    <div class="info">
      <h3>{{ work.title }}</h3>
      <p v-if="work.title_original" class="original">{{ work.title_original }}</p>
      <div class="tags">
        <span class="tag status">{{ statusLabel[work.status] ?? work.status }}</span>
        <span v-if="work.release_year" class="tag year">{{ work.release_year }}</span>
        <span v-if="work.release_season" class="tag season">{{ seasonLabel[work.release_season] }}</span>
        <span v-for="cat in work.categories" :key="cat.id" class="tag category">{{ cat.name }}</span>
      </div>
      <div class="rating" v-if="work.rating !== null && work.rating !== undefined">
        {{ '★'.repeat(work.rating) }}{{ '☆'.repeat(5 - work.rating) }}
      </div>
      <span v-if="work.is_favorite">❤️</span>
    </div>
    <div class="actions">
      <button @click="emit('edit', work)">編輯</button>
      <button @click="emit('delete', work.id)">刪除</button>
    </div>
  </div>
</template>
