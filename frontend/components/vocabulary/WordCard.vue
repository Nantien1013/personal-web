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

const playAudio = (url: string) => {
  if (url) new Audio(url).play()
  else window.speechSynthesis.speak(
    Object.assign(new SpeechSynthesisUtterance(props.word.word), { lang: 'en-US' })
  )
}
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
      {{ '★'.repeat(word.familiarity) }}{{ '☆'.repeat(5 - word.familiarity) }}
    </div>
    <div class="actions">
      <button @click="emit('edit', word)">編輯</button>
      <button @click="emit('delete', word.id)">刪除</button>
    </div>
  </div>
</template>
