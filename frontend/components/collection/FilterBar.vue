<script setup lang="ts">
interface Filters {
  type: string
  status: string
  search: string
  season: string
  favorite: boolean
}

const props = defineProps<{ modelValue: Filters }>()
const emit = defineEmits<{ 'update:modelValue': [filters: Filters] }>()

const filters = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const update = (key: keyof Filters, value: string | boolean) => {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>

<template>
  <div class="filter-bar">
    <input
      :value="filters.search"
      @input="update('search', ($event.target as HTMLInputElement).value)"
      placeholder="搜尋..."
      type="search"
    />
    <select :value="filters.type" @change="update('type', ($event.target as HTMLSelectElement).value)">
      <option value="">全部類型</option>
      <option value="anime">動漫</option>
      <option value="manga">漫畫</option>
    </select>
    <select :value="filters.status" @change="update('status', ($event.target as HTMLSelectElement).value)">
      <option value="">全部狀態</option>
      <option value="watching">觀看中</option>
      <option value="completed">已完成</option>
      <option value="plan">計畫</option>
      <option value="on_hold">擱置</option>
      <option value="dropped">放棄</option>
    </select>
    <select :value="filters.season" @change="update('season', ($event.target as HTMLSelectElement).value)">
      <option value="">全部番別</option>
      <option value="winter">冬番</option>
      <option value="spring">春番</option>
      <option value="summer">夏番</option>
      <option value="autumn">秋番</option>
    </select>
    <label>
      <input type="checkbox" :checked="filters.favorite" @change="update('favorite', ($event.target as HTMLInputElement).checked)" />
      只看最愛
    </label>
  </div>
</template>
