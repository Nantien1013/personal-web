<script setup lang="ts">
const { result, loading, error, debouncedLookup } = useVocabularyLookup()

const emit = defineEmits<{
  fill: [result: NonNullable<typeof result.value>]
}>()

const word = ref('')

watch(word, (w) => debouncedLookup(w))
watch(result, (r) => { if (r) emit('fill', r) })
</script>

<template>
  <div class="lookup-form">
    <input
      v-model="word"
      type="text"
      placeholder="輸入英文單字自動查詢..."
      class="lookup-input"
    />
    <span v-if="loading" class="lookup-status">查詢中...</span>
    <span v-if="error" class="lookup-error">{{ error }}</span>
  </div>
</template>
