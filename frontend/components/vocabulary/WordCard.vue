<script setup lang="ts">
interface Word {
  id: number
  word: string
  meaning: string
  part_of_speech?: string
  phonetic?: string
  familiarity: number
  next_review_at?: string
}

const props = defineProps<{ word: Word }>()
const emit = defineEmits<{
  edit: [word: Word]
  delete: [id: number]
}>()
</script>

<template>
  <div class="word-card">
    <div class="word-header">
      <strong class="word">{{ word.word }}</strong>
      <span v-if="word.phonetic" class="phonetic">{{ word.phonetic }}</span>
      <span class="pos">{{ word.part_of_speech }}</span>
    </div>
    <p class="meaning">{{ word.meaning }}</p>
    <div class="familiarity">
      {{ '★'.repeat(Math.min(word.familiarity, 5)) }}{{ '☆'.repeat(5 - Math.min(word.familiarity, 5)) }}
    </div>
    <div class="actions">
      <button @click="emit('edit', word)">編輯</button>
      <button @click="emit('delete', word.id)">刪除</button>
    </div>
  </div>
</template>
