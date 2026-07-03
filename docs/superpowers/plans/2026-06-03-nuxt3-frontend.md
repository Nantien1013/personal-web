# Nuxt3 前端 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**前置條件：** Laravel 後端（`2026-06-03-laravel-backend.md`）已完成並在本機執行於 `http://localhost:8000`。

**Goal:** 將現有靜態 HTML/JS 前端遷移至 Nuxt3 SSG 架構，支援管理員登入、收藏頁、單字庫頁，靜態頁面完整預渲染。

**Architecture:** Nuxt3 以 SSG 模式（`nuxt generate`）輸出靜態檔案。`index.vue` / `resume.vue` 完整預渲染（無 API 呼叫）。`collection.vue` / `vocabulary.vue` 為靜態 shell，資料在 `onMounted` client-side fetch。Admin 頁面透過 `routeRules` 強制 `ssr: false`，token 存 `sessionStorage`（關閉瀏覽器即清除）。

**Tech Stack:** Nuxt3, Vue3, TypeScript, Pinia, @pinia/nuxt, Vitest, @vue/test-utils

---

## 檔案清單

**新建：**
- `frontend/` — Nuxt3 專案根目錄
- `frontend/stores/auth.ts`
- `frontend/composables/useApi.ts`
- `frontend/composables/useAuth.ts`
- `frontend/composables/useVocabularyLookup.ts`
- `frontend/middleware/admin.ts`
- `frontend/pages/index.vue`
- `frontend/pages/resume.vue`
- `frontend/pages/collection.vue`
- `frontend/pages/vocabulary.vue`
- `frontend/pages/admin/login.vue`
- `frontend/components/collection/WorkCard.vue`
- `frontend/components/collection/WorkModal.vue`
- `frontend/components/collection/FilterBar.vue`
- `frontend/components/vocabulary/WordCard.vue`
- `frontend/components/vocabulary/FlashCard.vue`
- `frontend/components/vocabulary/LookupForm.vue`
- `frontend/tests/stores/auth.test.ts`
- `frontend/tests/composables/useVocabularyLookup.test.ts`

**修改：**
- `frontend/nuxt.config.ts` — SSG + routeRules + runtimeConfig
- `frontend/app.vue` — 全域 layout
- `frontend/.env.example`

---

## Task 1: 初始化 Nuxt3 + 安裝依賴

**Files:**
- Create: `frontend/` (Nuxt3 專案)
- Modify: `frontend/nuxt.config.ts`
- Modify: `frontend/.env.example`

- [ ] **Step 1: 在 repo 根目錄建立 Nuxt3 專案**

```bash
npx nuxi@latest init frontend
cd frontend
```

選擇：TypeScript、npm。

- [ ] **Step 2: 安裝必要依賴**

```bash
npm install @pinia/nuxt pinia
npm install -D vitest @vue/test-utils @vitejs/plugin-vue happy-dom
```

- [ ] **Step 3: 設定 nuxt.config.ts**

```ts
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  ssr: true,

  modules: ['@pinia/nuxt'],

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL ?? 'http://localhost:8000/api',
    },
  },

  routeRules: {
    '/admin/**': { ssr: false },
  },
})
```

- [ ] **Step 4: 建立 .env.example**

```
API_BASE_URL=https://your-laravel-api-domain.com/api
```

複製為 `.env`：

```bash
cp .env.example .env
```

編輯 `.env`：

```
API_BASE_URL=http://localhost:8000/api
```

- [ ] **Step 5: 設定 Vitest**

在 `frontend/vitest.config.ts` 新建：

```ts
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'happy-dom',
    globals: true,
  },
})
```

- [ ] **Step 6: 確認開發伺服器啟動**

```bash
npm run dev
```

預期：瀏覽器開啟 `http://localhost:3000`，顯示 Nuxt 預設歡迎頁。

Ctrl+C 停止。

- [ ] **Step 7: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: initialize Nuxt3 with Pinia and SSG config"
```

---

## Task 2: Auth Store + Composables

**Files:**
- Create: `frontend/stores/auth.ts`
- Create: `frontend/composables/useApi.ts`
- Create: `frontend/composables/useAuth.ts`
- Create: `frontend/tests/stores/auth.test.ts`

- [ ] **Step 1: 建立 auth store**

新建 `frontend/stores/auth.ts`：

```ts
import { defineStore } from 'pinia'

interface AuthUser {
  email: string
  role: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as AuthUser | null,
  }),

  getters: {
    isLoggedIn: (state): boolean => !!state.token,
    isAdmin: (state): boolean => state.user?.role === 'admin',
  },

  actions: {
    init() {
      if (import.meta.client) {
        this.token = sessionStorage.getItem('auth_token')
        const raw = sessionStorage.getItem('auth_user')
        this.user = raw ? JSON.parse(raw) : null
      }
    },

    setAuth(token: string, user: AuthUser) {
      this.token = token
      this.user = user
      if (import.meta.client) {
        sessionStorage.setItem('auth_token', token)
        sessionStorage.setItem('auth_user', JSON.stringify(user))
      }
    },

    clearAuth() {
      this.token = null
      this.user = null
      if (import.meta.client) {
        sessionStorage.removeItem('auth_token')
        sessionStorage.removeItem('auth_user')
      }
    },
  },
})
```

- [ ] **Step 2: 建立 useApi composable**

新建 `frontend/composables/useApi.ts`：

```ts
export const useApi = () => {
  const config = useRuntimeConfig()
  const auth = useAuthStore()

  return <T = unknown>(
    url: string,
    options: Parameters<typeof $fetch>[1] = {}
  ): Promise<T> => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...(options.headers as Record<string, string> ?? {}),
    }

    if (auth.token) {
      headers['Authorization'] = `Bearer ${auth.token}`
    }

    return $fetch<T>(url, {
      baseURL: config.public.apiBase,
      ...options,
      headers,
    })
  }
}
```

- [ ] **Step 3: 建立 useAuth composable**

新建 `frontend/composables/useAuth.ts`：

```ts
export const useAuth = () => {
  const auth = useAuthStore()
  const config = useRuntimeConfig()

  const login = async (email: string, password: string): Promise<void> => {
    const res = await $fetch<{ token: string; user: { email: string; role: string } }>(
      '/auth/login',
      {
        baseURL: config.public.apiBase,
        method: 'POST',
        body: { email, password },
        headers: { Accept: 'application/json' },
      }
    )
    auth.setAuth(res.token, res.user)
  }

  const logout = async (): Promise<void> => {
    const api = useApi()
    await api('/auth/logout', { method: 'POST' }).catch(() => null)
    auth.clearAuth()
  }

  return { login, logout, isLoggedIn: computed(() => auth.isLoggedIn) }
}
```

- [ ] **Step 4: 撰寫 auth store 測試**

新建 `frontend/tests/stores/auth.test.ts`：

```ts
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../stores/auth'
import { beforeEach, describe, expect, it } from 'vitest'

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('初始狀態為未登入', () => {
    const auth = useAuthStore()
    expect(auth.isLoggedIn).toBe(false)
    expect(auth.isAdmin).toBe(false)
  })

  it('setAuth 後 isLoggedIn 為 true', () => {
    const auth = useAuthStore()
    auth.setAuth('test-token', { email: 'admin@test.com', role: 'admin' })
    expect(auth.isLoggedIn).toBe(true)
    expect(auth.isAdmin).toBe(true)
    expect(auth.token).toBe('test-token')
  })

  it('clearAuth 清除狀態', () => {
    const auth = useAuthStore()
    auth.setAuth('test-token', { email: 'admin@test.com', role: 'admin' })
    auth.clearAuth()
    expect(auth.isLoggedIn).toBe(false)
    expect(auth.token).toBeNull()
  })

  it('一般用戶 isAdmin 為 false', () => {
    const auth = useAuthStore()
    auth.setAuth('token', { email: 'user@test.com', role: 'user' })
    expect(auth.isAdmin).toBe(false)
  })
})
```

- [ ] **Step 5: 執行 auth store 測試**

```bash
cd frontend
npx vitest run tests/stores/auth.test.ts
```

預期：4 tests passed。

- [ ] **Step 6: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: add auth store, useApi and useAuth composables"
```

---

## Task 3: Admin Middleware + Login 頁面

**Files:**
- Create: `frontend/middleware/admin.ts`
- Create: `frontend/pages/admin/login.vue`

- [ ] **Step 1: 建立 admin route middleware**

新建 `frontend/middleware/admin.ts`：

```ts
export default defineNuxtRouteMiddleware(() => {
  if (!import.meta.client) return

  const auth = useAuthStore()
  auth.init()

  if (!auth.isLoggedIn) {
    return navigateTo('/admin/login')
  }
})
```

- [ ] **Step 2: 建立 admin/login.vue**

新建 `frontend/pages/admin/login.vue`：

```vue
<script setup lang="ts">
definePageMeta({ middleware: false })

const { login } = useAuth()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await login(email.value, password.value)
    await navigateTo('/')
  } catch {
    error.value = '帳號或密碼錯誤，請確認後再試。'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="w-full max-w-sm bg-white rounded-xl shadow p-8">
      <h1 class="text-2xl font-bold text-center mb-6">管理員登入</h1>
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            autocomplete="email"
            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">密碼</label>
          <input
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white rounded-lg py-2 font-medium hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? '登入中...' : '登入' }}
        </button>
      </form>
    </div>
  </div>
</template>
```

- [ ] **Step 3: 手動驗證登入流程**

啟動 Laravel（`php artisan serve`）和 Nuxt dev server（`npm run dev`）。

開啟 `http://localhost:3000/admin/login`，用 AdminSeeder 建立的帳號登入。確認：
- 正確帳號 → 跳轉至首頁
- 錯誤密碼 → 顯示錯誤訊息

- [ ] **Step 4: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: add admin route middleware and login page"
```

---

## Task 4: 靜態頁面（index.vue, resume.vue）

**Files:**
- Modify: `frontend/app.vue`
- Create: `frontend/pages/index.vue`
- Create: `frontend/pages/resume.vue`

- [ ] **Step 1: 設定 app.vue（共用 layout）**

```vue
<template>
  <div>
    <nav class="navbar">
      <NuxtLink to="/">首頁</NuxtLink>
      <NuxtLink to="/resume">簡歷</NuxtLink>
      <NuxtLink to="/collection">收藏</NuxtLink>
      <NuxtLink to="/vocabulary">單字庫</NuxtLink>
    </nav>
    <main>
      <NuxtPage />
    </main>
  </div>
</template>

<style>
/* 將現有 css/variables.css + css/base.css + css/components.css 的內容移至此處，
   或改用 assets/css/ 並在 nuxt.config.ts 的 css 陣列引入 */
</style>
```

> 注意：將現有 `css/` 資料夾複製到 `frontend/assets/css/`，並在 `nuxt.config.ts` 加入：
> ```ts
> css: ['~/assets/css/variables.css', '~/assets/css/base.css', '~/assets/css/components.css'],
> ```

- [ ] **Step 2: 建立 index.vue（從 index.html 移植內容）**

```vue
<script setup lang="ts">
useSeoMeta({
  title: '個人網站',
  description: '個人入口網站',
})
</script>

<template>
  <section class="hero">
    <!-- 將 index.html 的 Hero 區塊、自我介紹、導覽卡片、底部連結移植至此 -->
    <!-- 保持與原始 HTML 相同的 class 名稱，CSS 已移植至 assets/css/ -->
    <h1>姓名</h1>
    <p>一句話定位</p>
    <div class="nav-cards">
      <NuxtLink to="/resume" class="card">關於我</NuxtLink>
      <NuxtLink to="/collection" class="card">收藏</NuxtLink>
      <NuxtLink to="/vocabulary" class="card">學習</NuxtLink>
    </div>
  </section>
</template>
```

> **移植步驟**：開啟 `index.html`，將 `<body>` 內的 HTML 結構複製到 `<template>` 內，`<a href>` 改為 `<NuxtLink to>`。

- [ ] **Step 3: 建立 resume.vue（從 resume.html 移植內容）**

```vue
<script setup lang="ts">
useSeoMeta({
  title: '個人簡歷',
})
</script>

<template>
  <article class="resume">
    <!-- 將 resume.html 的所有區塊（教育、經歷、技能、證照）移植至此 -->
    <!-- <a> 改 <NuxtLink>，外部連結保留 <a target="_blank"> -->
  </article>
</template>
```

- [ ] **Step 4: 確認靜態頁面正確渲染**

```bash
cd frontend
npm run generate
```

確認 `frontend/.output/public/index.html` 和 `frontend/.output/public/resume/index.html` 存在且包含完整 HTML 內容（非空 shell）。

- [ ] **Step 5: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: add static index and resume pages"
```

---

## Task 5: Collection 元件

**Files:**
- Create: `frontend/components/collection/WorkCard.vue`
- Create: `frontend/components/collection/FilterBar.vue`
- Create: `frontend/components/collection/WorkModal.vue`

- [ ] **Step 1: 建立 WorkCard.vue**

```vue
<script setup lang="ts">
interface Work {
  id: number
  type: 'anime' | 'manga'
  title: string
  title_original?: string
  cover_url?: string
  status: string
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
        <span class="tag status">{{ statusLabel[work.status] }}</span>
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
```

- [ ] **Step 2: 建立 FilterBar.vue**

```vue
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
```

- [ ] **Step 3: 建立 WorkModal.vue**

```vue
<script setup lang="ts">
interface Category { id: number; name: string; group: string }
interface WorkForm {
  type: 'anime' | 'manga'
  title: string
  title_original: string
  cover_url: string
  status: string
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
  if (w) Object.assign(form, w)
  else Object.assign(form, defaultForm())
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
```

- [ ] **Step 4: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: add collection components (WorkCard, FilterBar, WorkModal)"
```

---

## Task 6: collection.vue 頁面

**Files:**
- Create: `frontend/pages/collection.vue`

- [ ] **Step 1: 建立 collection.vue**

```vue
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

// 刪除確認
const confirmDelete = ref<number | null>(null)

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
      <FilterBar v-model="filters" />
      <button v-if="auth.isAdmin" @click="openCreate" class="btn-primary">+ 新增</button>
    </div>

    <div v-if="loading" class="loading">載入中...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="works.length === 0" class="empty">尚無收藏，快去新增第一筆吧！</div>
    <div v-else class="works-grid">
      <WorkCard
        v-for="work in works"
        :key="work.id"
        :work="work"
        @edit="openEdit"
        @delete="handleDelete"
      />
    </div>

    <WorkModal
      v-model="showModal"
      :work="editingWork"
      :categories="categories"
      @submit="handleSubmit"
    />
  </div>
</template>
```

- [ ] **Step 2: 手動驗證收藏頁**

確保 Laravel 已執行（`php artisan serve`），開啟 `http://localhost:3000/collection`：
- 未登入：可瀏覽但無新增/編輯按鈕
- 登入後：可新增、編輯、刪除收藏
- 篩選器：切換類型/狀態，確認列表即時更新

- [ ] **Step 3: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: add collection page with CRUD and filtering"
```

---

## Task 7: Vocabulary Composable + 元件

**Files:**
- Create: `frontend/composables/useVocabularyLookup.ts`
- Create: `frontend/components/vocabulary/LookupForm.vue`
- Create: `frontend/components/vocabulary/WordCard.vue`
- Create: `frontend/components/vocabulary/FlashCard.vue`
- Create: `frontend/tests/composables/useVocabularyLookup.test.ts`

- [ ] **Step 1: 建立 useVocabularyLookup composable**

新建 `frontend/composables/useVocabularyLookup.ts`：

```ts
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
```

- [ ] **Step 2: 撰寫 useVocabularyLookup 測試**

新建 `frontend/tests/composables/useVocabularyLookup.test.ts`：

```ts
import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('#app', () => ({
  useRuntimeConfig: () => ({ public: { apiBase: 'http://localhost:8000/api' } }),
}))

describe('useVocabularyLookup', () => {
  it('空字串不發請求', async () => {
    const fetchSpy = vi.spyOn(global, 'fetch').mockResolvedValue(new Response('{}'))
    const { debouncedLookup, result } = useVocabularyLookup()
    debouncedLookup('')
    await new Promise(r => setTimeout(r, 600))
    expect(result.value).toBeNull()
    fetchSpy.mockRestore()
  })
})
```

- [ ] **Step 3: 執行 composable 測試**

```bash
cd frontend
npx vitest run tests/composables/useVocabularyLookup.test.ts
```

預期：1 test passed。

- [ ] **Step 4: 建立 LookupForm.vue**

```vue
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
```

- [ ] **Step 5: 建立 WordCard.vue**

```vue
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
```

- [ ] **Step 6: 建立 FlashCard.vue（抽卡複習）**

```vue
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
```

- [ ] **Step 7: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: add vocabulary composable and components (LookupForm, WordCard, FlashCard)"
```

---

## Task 8: vocabulary.vue 頁面

**Files:**
- Create: `frontend/pages/vocabulary.vue`

- [ ] **Step 1: 建立 vocabulary.vue**

```vue
<script setup lang="ts">
useSeoMeta({ title: '論文單字庫' })

const auth = useAuthStore()
const api = useApi()
const { debouncedLookup } = useVocabularyLookup()

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
  await api(`/vocabulary/${id}`, { method: 'DELETE' })
  await fetchAll()
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
        <WordCard
          v-for="w in words" :key="w.id" :word="w"
          @edit="() => {}"
          @delete="handleDelete"
        />
      </div>
    </div>

    <!-- 新增模式 -->
    <div v-if="mode === 'add'" class="add-mode">
      <LookupForm @fill="handleLookupFill" />
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
        <FlashCard
          :word="reviewQueue[reviewIndex]"
          @review="handleReview"
        />
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 2: 手動驗證單字庫頁面**

開啟 `http://localhost:3000/vocabulary`：
- 瀏覽模式：列表顯示、搜尋即時篩選
- 新增模式：輸入單字後 500ms 自動帶入欄位
- 抽卡模式：點擊翻面、點評分按鈕更新熟悉度、進度條推進

- [ ] **Step 3: Commit**

```bash
cd ..
git add frontend/
git commit -m "feat: add vocabulary page with browse, add, and flashcard modes"
```

---

## Task 9: SSG 建置 + 部署設定

**Files:**
- Modify: `frontend/nuxt.config.ts`
- Modify: `frontend/.env.example`
- Modify: `.github/workflows/deploy.yml`（新建）

- [ ] **Step 1: 確認 nuxt.config.ts 完整**

```ts
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: false },
  ssr: true,

  modules: ['@pinia/nuxt'],

  css: [
    '~/assets/css/variables.css',
    '~/assets/css/base.css',
    '~/assets/css/components.css',
  ],

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL ?? 'http://localhost:8000/api',
    },
  },

  routeRules: {
    '/admin/**': { ssr: false },
  },

  app: {
    baseURL: process.env.BASE_URL ?? '/',  // GitHub Pages 需設為 /repo-name/
    head: {
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
    },
  },
})
```

- [ ] **Step 2: 執行 SSG 建置並確認輸出**

```bash
cd frontend
npm run generate
```

確認 `frontend/.output/public/` 包含：
- `index.html`（含完整 HTML 內容，非空 shell）
- `resume/index.html`（含完整 HTML 內容）
- `collection/index.html`（空 shell，等待 client fetch）
- `vocabulary/index.html`（空 shell）
- `admin/login/index.html`

- [ ] **Step 3: 執行所有 Vitest 測試**

```bash
npx vitest run
```

預期：所有測試通過。

- [ ] **Step 4: 建立 GitHub Actions 部署 workflow**

新建 `.github/workflows/deploy.yml`（從 repo 根目錄）：

```yaml
name: Deploy to GitHub Pages

on:
  push:
    branches: [main]
    paths: ['frontend/**']

jobs:
  deploy:
    runs-on: ubuntu-latest
    permissions:
      contents: write

    steps:
      - uses: actions/checkout@v4

      - uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install and build
        working-directory: frontend
        env:
          API_BASE_URL: ${{ secrets.API_BASE_URL }}
          BASE_URL: /${{ github.event.repository.name }}/
        run: |
          npm ci
          npm run generate

      - name: Deploy
        uses: peaceiris/actions-gh-pages@v4
        with:
          github_token: ${{ secrets.GITHUB_TOKEN }}
          publish_dir: frontend/.output/public
```

> 在 GitHub repo Settings → Secrets 加入 `API_BASE_URL`（Laravel 後端的公開網址）。

- [ ] **Step 5: 最終 commit**

```bash
cd ..
git add .
git commit -m "feat: complete Nuxt3 SSG frontend with GitHub Actions deployment"
```
