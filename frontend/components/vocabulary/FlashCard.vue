<script setup lang="ts">
interface Word {
  id: number
  word: string
  meaning: string
  part_of_speech?: string
  phonetic?: string
  audio_url?: string
  example?: string
}

const props = defineProps<{ word: Word }>()
const emit = defineEmits<{
  review: [id: number, result: 'forgot' | 'vague' | 'remembered' | 'mastered']
}>()

const flipped = ref(false)

const speakWord = () => {
  if (props.word.audio_url) {
    new Audio(props.word.audio_url).play()
  } else {
    window.speechSynthesis.speak(
      Object.assign(new SpeechSynthesisUtterance(props.word.word), { lang: 'en-US' })
    )
  }
}

watch(() => props.word.id, () => { flipped.value = false })
</script>

<template>
  <div class="flash-card" @click="flipped = !flipped">
    <div v-if="!flipped" class="front">
      <h2>{{ word.word }}</h2>
      <span v-if="word.phonetic">{{ word.phonetic }}</span>
      <button @click.stop="speakWord">🔊</button>
    </div>
    <div v-else class="back">
      <p class="meaning">{{ word.meaning }}</p>
      <p v-if="word.example" class="example">{{ word.example }}</p>
      <div class="review-buttons">
        <button @click.stop="emit('review', word.id, 'forgot')">忘記</button>
        <button @click.stop="emit('review', word.id, 'vague')">有點印象</button>
        <button @click.stop="emit('review', word.id, 'remembered')">記得</button>
        <button @click.stop="emit('review', word.id, 'mastered')">很熟</button>
      </div>
    </div>
  </div>
</template>
