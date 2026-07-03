# 個人網站遷移設計：Laravel 11 + Nuxt3 SSG

> 建立日期：2026-06-03  
> 狀態：已核准，待實作

---

## 一、背景

現有架構為 Node.js + Express 後端搭配純靜態 HTML/CSS/JS 前端，部署在 Railway（後端）和 GitHub Pages（前端）。本次遷移目標：

- 後端改為 **PHP Laravel 11**（API only）
- 前端改為 **Nuxt3**（SSG 靜態生成模式）
- 認證改為 **Laravel Sanctum**（email + password 登入）
- 維持 **Monorepo** 結構（單一 repo，`backend/` + `frontend/`）

---

## 二、技術版本

| 層 | 技術 | 說明 |
|---|---|---|
| 前端 | Nuxt3 3.x + Vue3 | SSG 模式（`nuxt generate`） |
| 狀態管理 | Pinia | 管理 auth token |
| 後端 | Laravel 11 | API only，無 Blade / web routes |
| 認證 | Laravel Sanctum | email + password，回傳 Bearer token |
| 資料庫 | MySQL 8.x | 沿用現有 4 張表 + 新增 users 表 |
| 前端部署 | GitHub Pages / Cloudflare Pages | 靜態 HTML+JS |
| 後端部署 | TBD（任何支援 PHP 8.2+ 的平台） | |

---

## 三、Monorepo 結構

```
repo/
├── backend/                Laravel 11
├── frontend/               Nuxt3
├── docs/
│   └── superpowers/specs/
├── .gitignore
└── README.md
```

---

## 四、資料流

```
使用者           Nuxt3 SSG            Laravel API          MySQL
  │──── 開啟頁面 ──→│ (靜態 HTML shell)      │                  │
  │                │──── GET /api/xxx ──────→│                  │
  │                │                         │── Eloquent ─────→│
  │                │←─── JSON response ──────│                  │
  │←── 渲染畫面 ───│                         │                  │

管理員           /admin/login          POST /api/auth/login
  │── email+pwd ──→│──────────────────────→│
  │←── token ──────│←──────────────────────│ (存 Pinia + sessionStorage)
  │                │                         │
  │── 新增/編輯 ──→│── POST/PUT/DELETE ────→│ (Authorization: Bearer token)
```

---

## 五、Laravel 後端結構

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── CollectionController.php
│   │   │   ├── CategoryController.php
│   │   │   └── VocabularyController.php
│   │   └── Middleware/
│   │       └── AdminOnly.php              # 區分管理員與一般用戶（預留多用戶擴充）
│   └── Models/
│       ├── User.php
│       ├── CollectionWork.php
│       ├── CollectionCategory.php
│       └── StudyVocabulary.php
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_collection_works_table.php
│   │   ├── xxxx_create_collection_categories_table.php
│   │   ├── xxxx_create_collection_work_categories_table.php
│   │   └── xxxx_create_study_vocabulary_table.php
│   └── seeders/
│       ├── AdminSeeder.php
│       └── CategorySeeder.php
├── routes/
│   └── api.php
├── .env.example
└── composer.json
```

### API 路由（routes/api.php）

**公開路由（無需登入）**
| Method | Path | 說明 |
|---|---|---|
| GET | `/api/collection` | 列表（type, status, year, season, category, favorite, search） |
| GET | `/api/collection/stats` | 統計摘要 |
| GET | `/api/collection/{id}` | 單筆詳細 |
| GET | `/api/categories` | 所有類別 |
| GET | `/api/vocabulary` | 列表（search, familiarity） |
| GET | `/api/vocabulary/stats` | 統計摘要 |
| GET | `/api/vocabulary/lookup` | 外部 API 代理（Free Dictionary + MyMemory） |
| GET | `/api/vocabulary/review-queue` | 今日待複習清單 |

**認證路由**
| Method | Path | 說明 |
|---|---|---|
| POST | `/api/auth/login` | email + password 登入，回傳 Sanctum token |
| POST | `/api/auth/logout` | 登出（需 Bearer token） |
| GET | `/api/auth/me` | 取得目前登入用戶資訊 |

**需要登入且為管理員（`auth:sanctum` + `admin` middleware）**
| Method | Path | 說明 |
|---|---|---|
| POST | `/api/collection` | 新增收藏 |
| PUT | `/api/collection/{id}` | 更新收藏 |
| DELETE | `/api/collection/{id}` | 刪除收藏 |
| POST | `/api/vocabulary` | 新增單字 |
| PUT | `/api/vocabulary/{id}` | 更新單字 |
| PUT | `/api/vocabulary/{id}/review` | 提交複習結果（SM-2 算法） |
| DELETE | `/api/vocabulary/{id}` | 刪除單字 |

### Rate Limiting

```php
Route::middleware('throttle:300,15')->group(...);  // 15 分鐘內 300 次
```

### 環境變數（.env）

```
APP_KEY=
DB_CONNECTION=mysql
DB_HOST=
DB_DATABASE=personal_site
DB_USERNAME=
DB_PASSWORD=
FRONTEND_URL=https://your-github-pages-url
DICTIONARY_API_URL=https://api.dictionaryapi.dev
MYMEMORY_API_URL=https://api.mymemory.translated.net
SANCTUM_STATELESS_DOMAINS=your-github-pages-domain
```

---

## 六、Nuxt3 前端結構

```
frontend/
├── pages/
│   ├── index.vue               # 主頁（完整靜態渲染）
│   ├── resume.vue              # 個人簡歷（完整靜態渲染）
│   ├── collection.vue          # 收藏（SSG shell + client fetch）
│   ├── vocabulary.vue          # 單字庫（SSG shell + client fetch）
│   └── admin/
│       └── login.vue           # 管理員登入頁（client-side only）
├── components/
│   ├── collection/
│   │   ├── WorkCard.vue
│   │   ├── WorkModal.vue
│   │   └── FilterBar.vue
│   └── vocabulary/
│       ├── WordCard.vue
│       ├── FlashCard.vue
│       └── LookupForm.vue
├── composables/
│   ├── useApi.ts               # $fetch 封裝，自動帶 Bearer token
│   ├── useAuth.ts              # login / logout / isAdmin
│   └── useVocabularyLookup.ts  # debounce + 外部查詢
├── stores/
│   └── auth.ts                 # Pinia：token + user 狀態
├── middleware/
│   └── admin.ts                # 保護 /admin/* 路由
├── nuxt.config.ts
└── .env.example
```

### SSG 渲染行為

| 頁面 | 建置時 | 瀏覽器端 |
|---|---|---|
| `index.vue` | 完整渲染 | 純靜態 |
| `resume.vue` | 完整渲染 | 純靜態 |
| `collection.vue` | 空 shell（骨架） | `onMounted` fetch API |
| `vocabulary.vue` | 空 shell（骨架） | `onMounted` fetch API |
| `admin/login.vue` | 登入表單 | POST 給 Laravel，存 token |

### nuxt.config.ts 關鍵設定

```ts
export default defineNuxtConfig({
  ssr: true,
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL
    }
  },
  routeRules: {
    '/admin/**': { ssr: false }
  }
})
```

### useApi.ts

```ts
export const useApi = () => {
  const auth = useAuthStore()
  return (url: string, options = {}) =>
    $fetch(url, {
      baseURL: useRuntimeConfig().public.apiBase,
      headers: auth.token
        ? { Authorization: `Bearer ${auth.token}` }
        : {},
      ...options
    })
}
```

---

## 七、認證流程

**登入**
1. 管理員進入 `/admin/login`
2. 輸入 email + password → `POST /api/auth/login`
3. Laravel Sanctum 驗證 → 回傳 token
4. Nuxt3 auth store 儲存 token 到 Pinia + `sessionStorage`
5. 之後所有寫入請求自動帶 `Authorization: Bearer <token>`
6. 重整頁面 → 從 `sessionStorage` 還原 token
7. 關閉瀏覽器 → `sessionStorage` 清除，下次需重新登入

**Admin 帳號**
- `users` 表只有一筆管理員記錄
- 透過 `php artisan db:seed --class=AdminSeeder` 建立
- 不開放 `/api/auth/register`

---

## 八、CORS 設定（config/cors.php）

```php
'allowed_origins'     => [env('FRONTEND_URL')],
'allowed_methods'     => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
'allowed_headers'     => ['Content-Type', 'Authorization', 'Accept'],
'supports_credentials' => false,
```

---

## 九、資料庫遷移對照

| 現有 SQL migration | Laravel migration |
|---|---|
| `001_init.sql`（collection_works, categories, work_categories, vocabulary）| 拆為 4 支 migration 檔 |
| `002_categories.sql`（類別 seed 資料）| 移至 `CategorySeeder.php` |
| 無 | 新增 `users` 表（Sanctum 用） |

users 表額外欄位：
- `role` ENUM('admin','user') DEFAULT 'admin'（預留多用戶擴充，`AdminOnly` middleware 依此欄位判斷權限）

Eloquent 關聯：
- `CollectionWork` belongsToMany `CollectionCategory`（透過 `collection_work_categories`）
- `User` hasMany `PersonalAccessToken`（Sanctum 內建）

---

## 十、Vocabulary Lookup 代理

```
GET /api/vocabulary/lookup?word=xxx
  → VocabularyController::lookup()
  → Http::get(Free Dictionary API)   // Laravel Http facade
  → Http::get(MyMemory API)
  → 合併結果 → JSON response
```

取代現有 Node.js 的 `node-fetch` 實作。

---

## 十一、待決定事項

- [ ] Laravel 後端部署平台（Railway / Render / Fly.io / 其他）
- [ ] Nuxt3 前端部署目標（GitHub Pages 或 Cloudflare Pages）
- [ ] 管理員 email + 初始密碼（AdminSeeder 設定）
- [ ] 自訂網域（可選）
