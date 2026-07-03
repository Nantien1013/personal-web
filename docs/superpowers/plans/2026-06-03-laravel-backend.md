# Laravel 11 後端 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 將現有 Node.js/Express 後端完整遷移至 Laravel 11 REST API，包含 Sanctum 認證、收藏模組、單字庫模組、外部 API 代理。

**Architecture:** Laravel 11 以 API-only 模式運作（無 Blade/web routes）。Sanctum Bearer token 認證，`AdminOnly` middleware 保護所有寫入端點。外部字典 API 透過 Laravel Http facade 代理，SM-2 間隔演算法在 VocabularyController 內實作。

**Tech Stack:** PHP 8.2+, Laravel 11, Laravel Sanctum, MySQL 8, PHPUnit (feature tests with SQLite in-memory)

---

## 檔案清單

**新建：**
- `backend/` — Laravel 專案根目錄
- `backend/app/Http/Controllers/Api/AuthController.php`
- `backend/app/Http/Controllers/Api/CollectionController.php`
- `backend/app/Http/Controllers/Api/CategoryController.php`
- `backend/app/Http/Controllers/Api/VocabularyController.php`
- `backend/app/Http/Middleware/AdminOnly.php`
- `backend/app/Models/CollectionWork.php`
- `backend/app/Models/CollectionCategory.php`
- `backend/app/Models/StudyVocabulary.php`
- `backend/database/migrations/xxxx_add_role_to_users_table.php`
- `backend/database/migrations/xxxx_create_collection_works_table.php`
- `backend/database/migrations/xxxx_create_collection_categories_table.php`
- `backend/database/migrations/xxxx_create_collection_work_categories_table.php`
- `backend/database/migrations/xxxx_create_study_vocabulary_table.php`
- `backend/database/seeders/AdminSeeder.php`
- `backend/database/seeders/CategorySeeder.php`
- `backend/tests/Feature/AuthTest.php`
- `backend/tests/Feature/CollectionTest.php`
- `backend/tests/Feature/VocabularyTest.php`

**修改：**
- `backend/app/Models/User.php` — 加入 role fillable/cast
- `backend/bootstrap/app.php` — 註冊 api routes、AdminOnly middleware alias
- `backend/config/cors.php` — 設定 FRONTEND_URL
- `backend/config/services.php` — 加入 dictionary/mymemory API URL
- `backend/routes/api.php` — 所有路由定義
- `backend/database/seeders/DatabaseSeeder.php` — 呼叫 AdminSeeder + CategorySeeder
- `backend/phpunit.xml` — SQLite in-memory 測試設定
- `backend/.env.example` — 加入自訂環境變數

---

## Task 1: 初始化 Laravel 專案 + 安裝 Sanctum

**Files:**
- Create: `backend/` (整個 Laravel 專案)
- Modify: `backend/.env.example`

- [ ] **Step 1: 在 repo 根目錄建立 Laravel 專案**

```bash
composer create-project laravel/laravel backend
cd backend
```

預期輸出：`Application ready! Build something amazing.`

- [ ] **Step 2: 安裝 Sanctum（API token 模式）**

```bash
php artisan install:api
```

預期輸出：產生 `routes/api.php` 並建立 `personal_access_tokens` migration。

- [ ] **Step 3: 移除不需要的預設路由檔**

刪除 `routes/web.php`（API-only 專案不需要）：

```bash
rm routes/web.php
```

- [ ] **Step 4: 更新 `.env.example`**

在 `.env.example` 末尾加入：

```
FRONTEND_URL=https://your-github-pages-url
DICTIONARY_API_URL=https://api.dictionaryapi.dev
MYMEMORY_API_URL=https://api.mymemory.translated.net
```

- [ ] **Step 5: 複製 .env 並設定本機資料庫**

```bash
cp .env.example .env
php artisan key:generate
```

接著編輯 `.env`，填入本機 MySQL 連線資訊：

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=personal_site
DB_USERNAME=root
DB_PASSWORD=
```

- [ ] **Step 6: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: initialize Laravel 11 project with Sanctum"
```

---

## Task 2: 資料庫 Migrations

**Files:**
- Modify: `backend/database/migrations/0001_01_01_000000_create_users_table.php` — 加入 role 欄位
- Create: `backend/database/migrations/xxxx_create_collection_works_table.php`
- Create: `backend/database/migrations/xxxx_create_collection_categories_table.php`
- Create: `backend/database/migrations/xxxx_create_collection_work_categories_table.php`
- Create: `backend/database/migrations/xxxx_create_study_vocabulary_table.php`
- Modify: `backend/phpunit.xml`

- [ ] **Step 1: 修改 users migration 加入 role 欄位**

開啟 `backend/database/migrations/0001_01_01_000000_create_users_table.php`，在 `$table->password()` 後加入：

```php
$table->enum('role', ['admin', 'user'])->default('admin');
```

完整 `up()` 方法如下：

```php
public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->enum('role', ['admin', 'user'])->default('admin');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}
```

- [ ] **Step 2: 建立 collection_works migration**

```bash
cd backend
php artisan make:migration create_collection_works_table
```

開啟產生的檔案，填入：

```php
public function up(): void
{
    Schema::create('collection_works', function (Blueprint $table) {
        $table->unsignedInteger('id')->autoIncrement();
        $table->enum('type', ['anime', 'manga']);
        $table->string('title', 255);
        $table->string('title_original', 255)->nullable();
        $table->string('cover_url', 500)->nullable();
        $table->enum('status', ['watching', 'completed', 'plan', 'on_hold', 'dropped'])->default('plan');
        $table->unsignedTinyInteger('rating')->nullable();
        $table->boolean('is_favorite')->default(false);
        $table->unsignedSmallInteger('release_year')->nullable();
        $table->enum('release_season', ['winter', 'spring', 'summer', 'autumn'])->nullable();
        $table->string('media_type', 30)->nullable();
        $table->string('source_type', 30)->nullable();
        $table->unsignedSmallInteger('episodes_total')->nullable();
        $table->unsignedSmallInteger('episodes_watched')->default(0);
        $table->unsignedSmallInteger('volumes_total')->nullable();
        $table->unsignedSmallInteger('volumes_read')->default(0);
        $table->string('author', 100)->nullable();
        $table->string('studio', 100)->nullable();
        $table->text('note')->nullable();
        $table->timestamps();

        $table->index('type');
        $table->index('status');
        $table->index('release_year');
        $table->index('is_favorite');
    });
}

public function down(): void
{
    Schema::dropIfExists('collection_works');
}
```

- [ ] **Step 3: 建立 collection_categories migration**

```bash
php artisan make:migration create_collection_categories_table
```

```php
public function up(): void
{
    Schema::create('collection_categories', function (Blueprint $table) {
        $table->unsignedInteger('id')->autoIncrement();
        $table->string('name', 50)->unique();
        $table->enum('group', ['theme', 'source', 'media_type']);
        $table->integer('display_order')->default(0);
    });
}

public function down(): void
{
    Schema::dropIfExists('collection_categories');
}
```

- [ ] **Step 4: 建立 collection_work_categories migration**

```bash
php artisan make:migration create_collection_work_categories_table
```

```php
public function up(): void
{
    Schema::create('collection_work_categories', function (Blueprint $table) {
        $table->unsignedInteger('work_id');
        $table->unsignedInteger('category_id');
        $table->primary(['work_id', 'category_id']);
        $table->foreign('work_id')->references('id')->on('collection_works')->cascadeOnDelete();
        $table->foreign('category_id')->references('id')->on('collection_categories')->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::dropIfExists('collection_work_categories');
}
```

- [ ] **Step 5: 建立 study_vocabulary migration**

```bash
php artisan make:migration create_study_vocabulary_table
```

```php
public function up(): void
{
    Schema::create('study_vocabulary', function (Blueprint $table) {
        $table->unsignedInteger('id')->autoIncrement();
        $table->string('word', 100)->unique();
        $table->text('meaning');
        $table->string('part_of_speech', 30)->nullable();
        $table->string('phonetic', 100)->nullable();
        $table->string('audio_url', 500)->nullable();
        $table->text('example')->nullable();
        $table->text('example_zh')->nullable();
        $table->string('source', 255)->nullable();
        $table->text('note')->nullable();
        $table->unsignedTinyInteger('familiarity')->default(0);
        $table->unsignedInteger('review_count')->default(0);
        $table->unsignedInteger('correct_count')->default(0);
        $table->timestamp('next_review_at')->nullable();
        $table->timestamp('last_reviewed_at')->nullable();
        $table->boolean('auto_filled')->default(false);
        $table->timestamps();

        $table->index('next_review_at');
        $table->index('familiarity');
    });
}

public function down(): void
{
    Schema::dropIfExists('study_vocabulary');
}
```

- [ ] **Step 6: 設定 phpunit.xml 使用 SQLite in-memory**

開啟 `backend/phpunit.xml`，在 `<php>` 區塊加入：

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
</php>
```

- [ ] **Step 7: 執行 migrations 確認無誤**

```bash
php artisan migrate
```

預期輸出：所有 migration 顯示 `DONE`，無錯誤。

- [ ] **Step 8: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: add database migrations for collection and vocabulary"
```

---

## Task 3: Eloquent Models

**Files:**
- Modify: `backend/app/Models/User.php`
- Create: `backend/app/Models/CollectionWork.php`
- Create: `backend/app/Models/CollectionCategory.php`
- Create: `backend/app/Models/StudyVocabulary.php`

- [ ] **Step 1: 更新 User model 加入 role**

開啟 `backend/app/Models/User.php`，修改 `$fillable`：

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
];
```

在 `$hidden` 後加入 `$casts`（若不存在則新增）：

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

- [ ] **Step 2: 建立 CollectionWork model**

```bash
cd backend
php artisan make:model CollectionWork
```

以下內容取代產生的檔案：

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollectionWork extends Model
{
    protected $table = 'collection_works';

    protected $fillable = [
        'type', 'title', 'title_original', 'cover_url', 'status',
        'rating', 'is_favorite', 'release_year', 'release_season',
        'media_type', 'source_type', 'episodes_total', 'episodes_watched',
        'volumes_total', 'volumes_read', 'author', 'studio', 'note',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'rating' => 'integer',
        'release_year' => 'integer',
        'episodes_total' => 'integer',
        'episodes_watched' => 'integer',
        'volumes_total' => 'integer',
        'volumes_read' => 'integer',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CollectionCategory::class,
            'collection_work_categories',
            'work_id',
            'category_id'
        );
    }
}
```

- [ ] **Step 3: 建立 CollectionCategory model**

```bash
php artisan make:model CollectionCategory
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollectionCategory extends Model
{
    protected $table = 'collection_categories';
    public $timestamps = false;

    protected $fillable = ['name', 'group', 'display_order'];

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(
            CollectionWork::class,
            'collection_work_categories',
            'category_id',
            'work_id'
        );
    }
}
```

- [ ] **Step 4: 建立 StudyVocabulary model**

```bash
php artisan make:model StudyVocabulary
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyVocabulary extends Model
{
    protected $table = 'study_vocabulary';

    protected $fillable = [
        'word', 'meaning', 'part_of_speech', 'phonetic', 'audio_url',
        'example', 'example_zh', 'source', 'note',
        'familiarity', 'review_count', 'correct_count',
        'next_review_at', 'last_reviewed_at', 'auto_filled',
    ];

    protected $casts = [
        'familiarity' => 'integer',
        'review_count' => 'integer',
        'correct_count' => 'integer',
        'auto_filled' => 'boolean',
        'next_review_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];
}
```

- [ ] **Step 5: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: add Eloquent models for collection and vocabulary"
```

---

## Task 4: Seeders

**Files:**
- Create: `backend/database/seeders/AdminSeeder.php`
- Create: `backend/database/seeders/CategorySeeder.php`
- Modify: `backend/database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: 建立 AdminSeeder**

```bash
cd backend
php artisan make:seeder AdminSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => 'Admin',
                'password' => bcrypt(env('ADMIN_PASSWORD', 'changeme')),
                'role' => 'admin',
            ]
        );
    }
}
```

在 `.env.example` 加入：

```
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=changeme
```

- [ ] **Step 2: 建立 CategorySeeder**

```bash
php artisan make:seeder CategorySeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\CollectionCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // 主題類
            ['name' => '戀愛', 'group' => 'theme', 'display_order' => 1],
            ['name' => '校園', 'group' => 'theme', 'display_order' => 2],
            ['name' => '科幻', 'group' => 'theme', 'display_order' => 3],
            ['name' => '奇幻', 'group' => 'theme', 'display_order' => 4],
            ['name' => '冒險', 'group' => 'theme', 'display_order' => 5],
            ['name' => '動作', 'group' => 'theme', 'display_order' => 6],
            ['name' => '戰鬥', 'group' => 'theme', 'display_order' => 7],
            ['name' => '運動', 'group' => 'theme', 'display_order' => 8],
            ['name' => '搞笑', 'group' => 'theme', 'display_order' => 9],
            ['name' => '治癒', 'group' => 'theme', 'display_order' => 10],
            ['name' => '音樂', 'group' => 'theme', 'display_order' => 11],
            ['name' => '美食', 'group' => 'theme', 'display_order' => 12],
            ['name' => '偵探', 'group' => 'theme', 'display_order' => 13],
            ['name' => '懸疑', 'group' => 'theme', 'display_order' => 14],
            ['name' => '恐怖', 'group' => 'theme', 'display_order' => 15],
            ['name' => '機甲', 'group' => 'theme', 'display_order' => 16],
            ['name' => '魔法', 'group' => 'theme', 'display_order' => 17],
            ['name' => '異世界', 'group' => 'theme', 'display_order' => 18],
            ['name' => '職場', 'group' => 'theme', 'display_order' => 19],
            ['name' => '歷史', 'group' => 'theme', 'display_order' => 20],
            ['name' => '戰爭', 'group' => 'theme', 'display_order' => 21],
            ['name' => '後宮', 'group' => 'theme', 'display_order' => 22],
            ['name' => '百合', 'group' => 'theme', 'display_order' => 23],
            ['name' => '萌系', 'group' => 'theme', 'display_order' => 24],
            ['name' => '日常', 'group' => 'theme', 'display_order' => 25],
            // 來源類
            ['name' => '原創', 'group' => 'source', 'display_order' => 1],
            ['name' => '漫畫改編', 'group' => 'source', 'display_order' => 2],
            ['name' => '輕小說改編', 'group' => 'source', 'display_order' => 3],
            ['name' => '遊戲改編', 'group' => 'source', 'display_order' => 4],
            ['name' => '小說改編', 'group' => 'source', 'display_order' => 5],
            // 媒體類型
            ['name' => 'TV 動畫', 'group' => 'media_type', 'display_order' => 1],
            ['name' => '劇場版', 'group' => 'media_type', 'display_order' => 2],
            ['name' => 'OVA', 'group' => 'media_type', 'display_order' => 3],
            ['name' => 'ONA', 'group' => 'media_type', 'display_order' => 4],
            ['name' => '特別篇', 'group' => 'media_type', 'display_order' => 5],
            ['name' => '漫畫單行本', 'group' => 'media_type', 'display_order' => 6],
            ['name' => '網路漫畫', 'group' => 'media_type', 'display_order' => 7],
        ];

        foreach ($categories as $cat) {
            CollectionCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
```

- [ ] **Step 3: 更新 DatabaseSeeder**

開啟 `backend/database/seeders/DatabaseSeeder.php`：

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
```

- [ ] **Step 4: 執行 seeders 驗證**

```bash
php artisan db:seed
```

預期輸出：`AdminSeeder` 和 `CategorySeeder` 各顯示 `DONE`。

- [ ] **Step 5: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: add AdminSeeder and CategorySeeder"
```

---

## Task 5: CORS、Services 設定、bootstrap/app.php

**Files:**
- Modify: `backend/config/cors.php`
- Modify: `backend/config/services.php`
- Modify: `backend/bootstrap/app.php`

- [ ] **Step 1: 設定 CORS**

開啟 `backend/config/cors.php`，修改如下：

```php
<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

- [ ] **Step 2: 加入外部 API 設定至 services.php**

開啟 `backend/config/services.php`，在陣列末尾加入：

```php
'dictionary_api' => env('DICTIONARY_API_URL', 'https://api.dictionaryapi.dev'),
'mymemory_api' => env('MYMEMORY_API_URL', 'https://api.mymemory.translated.net'),
```

- [ ] **Step 3: 設定 bootstrap/app.php**

開啟 `backend/bootstrap/app.php`，確認 `withRouting` 有 api 路由，並加入 AdminOnly middleware alias：

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

- [ ] **Step 4: 確認 /up health check 回應正常**

```bash
php artisan serve &
curl http://localhost:8000/up
```

預期回應：`{"status":"up",...}`

停止伺服器：`kill %1`

- [ ] **Step 5: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: configure CORS, services, and bootstrap"
```

---

## Task 6: AuthController + AdminOnly Middleware + routes/api.php

**Files:**
- Create: `backend/app/Http/Controllers/Api/AuthController.php`
- Create: `backend/app/Http/Middleware/AdminOnly.php`
- Modify: `backend/routes/api.php`

- [ ] **Step 1: 建立 AdminOnly middleware**

```bash
cd backend
php artisan make:middleware AdminOnly
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
```

- [ ] **Step 2: 建立 AuthController**

```bash
php artisan make:controller Api/AuthController
```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => '帳號或密碼錯誤'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['email' => $user->email, 'role' => $user->role],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '已登出']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'email' => $request->user()->email,
            'role' => $request->user()->role,
        ]);
    }
}
```

- [ ] **Step 3: 定義 routes/api.php 骨架（含所有路由）**

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\VocabularyController;
use Illuminate\Support\Facades\Route;

// Rate limit: 300 requests per 15 minutes
Route::middleware('throttle:300,15')->group(function () {

    // --- Auth ---
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // --- Collection (public reads) ---
    Route::get('/collection/stats', [CollectionController::class, 'stats']);
    Route::get('/collection/{id}', [CollectionController::class, 'show']);
    Route::get('/collection', [CollectionController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);

    // --- Collection (admin writes) ---
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/collection', [CollectionController::class, 'store']);
        Route::put('/collection/{id}', [CollectionController::class, 'update']);
        Route::delete('/collection/{id}', [CollectionController::class, 'destroy']);
    });

    // --- Vocabulary (public reads) ---
    Route::get('/vocabulary/stats', [VocabularyController::class, 'stats']);
    Route::get('/vocabulary/review-queue', [VocabularyController::class, 'reviewQueue']);
    Route::get('/vocabulary/lookup', [VocabularyController::class, 'lookup']);
    Route::get('/vocabulary', [VocabularyController::class, 'index']);

    // --- Vocabulary (admin writes) ---
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/vocabulary', [VocabularyController::class, 'store']);
        Route::put('/vocabulary/{id}/review', [VocabularyController::class, 'review']);
        Route::put('/vocabulary/{id}', [VocabularyController::class, 'update']);
        Route::delete('/vocabulary/{id}', [VocabularyController::class, 'destroy']);
    });
});
```

- [ ] **Step 4: 驗證路由列表正確**

```bash
php artisan route:list --path=api
```

預期：顯示所有上述路由，無錯誤。

- [ ] **Step 5: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: add AuthController, AdminOnly middleware, and api routes"
```

---

## Task 7: Auth 功能測試

**Files:**
- Create: `backend/tests/Feature/AuthTest.php`

- [ ] **Step 1: 建立 AuthTest**

```bash
cd backend
php artisan make:test AuthTest
```

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('secret'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'secret',
        ]);

        $response->assertOk()
                 ->assertJsonStructure(['token', 'user' => ['email', 'role']])
                 ->assertJsonPath('user.role', 'admin');
    }

    public function test_wrong_password_returns_401(): void
    {
        User::factory()->create(['email' => 'admin@test.com']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_me(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response->assertOk()
                 ->assertJsonPath('email', $user->email)
                 ->assertJsonPath('role', 'admin');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/collection', [
            'type' => 'anime',
            'title' => 'Test',
            'status' => 'plan',
        ]);

        $response->assertStatus(403);
    }
}
```

- [ ] **Step 2: 執行 Auth 測試確認全部通過**

```bash
php artisan test tests/Feature/AuthTest.php --verbose
```

預期輸出：5 tests passed。

- [ ] **Step 3: Commit**

```bash
cd ..
git add backend/
git commit -m "test: add auth feature tests"
```

---

## Task 8: CollectionController + CategoryController

**Files:**
- Create: `backend/app/Http/Controllers/Api/CollectionController.php`
- Create: `backend/app/Http/Controllers/Api/CategoryController.php`

- [ ] **Step 1: 建立 CategoryController**

```bash
cd backend
php artisan make:controller Api/CategoryController
```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionCategory;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = CollectionCategory::orderBy('group')
            ->orderBy('display_order')
            ->get();
        return response()->json($categories);
    }
}
```

- [ ] **Step 2: 建立 CollectionController**

```bash
php artisan make:controller Api/CollectionController
```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionWork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CollectionWork::with('categories');

        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('year'))     $query->where('release_year', $request->year);
        if ($request->filled('season'))   $query->where('release_season', $request->season);
        if ($request->boolean('favorite')) $query->where('is_favorite', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('title_original', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $ids = explode(',', $request->category);
            $query->whereHas('categories', fn($q) => $q->whereIn('collection_categories.id', $ids));
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total'     => CollectionWork::count(),
            'anime'     => CollectionWork::where('type', 'anime')->count(),
            'manga'     => CollectionWork::where('type', 'manga')->count(),
            'completed' => CollectionWork::where('status', 'completed')->count(),
            'watching'  => CollectionWork::where('status', 'watching')->count(),
            'favorites' => CollectionWork::where('is_favorite', true)->count(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $work = CollectionWork::with('categories')->findOrFail($id);
        return response()->json($work);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'             => 'required|in:anime,manga',
            'title'            => 'required|string|max:255',
            'title_original'   => 'nullable|string|max:255',
            'cover_url'        => 'nullable|url|max:500',
            'status'           => 'required|in:watching,completed,plan,on_hold,dropped',
            'rating'           => 'nullable|integer|min:0|max:5',
            'is_favorite'      => 'boolean',
            'release_year'     => 'nullable|integer|min:1900|max:2100',
            'release_season'   => 'nullable|in:winter,spring,summer,autumn',
            'media_type'       => 'nullable|string|max:30',
            'source_type'      => 'nullable|string|max:30',
            'episodes_total'   => 'nullable|integer|min:0',
            'episodes_watched' => 'integer|min:0',
            'volumes_total'    => 'nullable|integer|min:0',
            'volumes_read'     => 'integer|min:0',
            'author'           => 'nullable|string|max:100',
            'studio'           => 'nullable|string|max:100',
            'note'             => 'nullable|string',
            'category_ids'     => 'array',
            'category_ids.*'   => 'integer|exists:collection_categories,id',
        ]);

        $work = CollectionWork::create($validated);

        if (!empty($validated['category_ids'])) {
            $work->categories()->sync($validated['category_ids']);
        }

        return response()->json($work->load('categories'), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $work = CollectionWork::findOrFail($id);

        $validated = $request->validate([
            'type'             => 'sometimes|in:anime,manga',
            'title'            => 'sometimes|string|max:255',
            'title_original'   => 'nullable|string|max:255',
            'cover_url'        => 'nullable|url|max:500',
            'status'           => 'sometimes|in:watching,completed,plan,on_hold,dropped',
            'rating'           => 'nullable|integer|min:0|max:5',
            'is_favorite'      => 'boolean',
            'release_year'     => 'nullable|integer|min:1900|max:2100',
            'release_season'   => 'nullable|in:winter,spring,summer,autumn',
            'media_type'       => 'nullable|string|max:30',
            'source_type'      => 'nullable|string|max:30',
            'episodes_total'   => 'nullable|integer|min:0',
            'episodes_watched' => 'integer|min:0',
            'volumes_total'    => 'nullable|integer|min:0',
            'volumes_read'     => 'integer|min:0',
            'author'           => 'nullable|string|max:100',
            'studio'           => 'nullable|string|max:100',
            'note'             => 'nullable|string',
            'category_ids'     => 'array',
            'category_ids.*'   => 'integer|exists:collection_categories,id',
        ]);

        $work->update($validated);

        if (array_key_exists('category_ids', $validated)) {
            $work->categories()->sync($validated['category_ids']);
        }

        return response()->json($work->load('categories'));
    }

    public function destroy(int $id): JsonResponse
    {
        CollectionWork::findOrFail($id)->delete();
        return response()->json(['message' => '已刪除']);
    }
}
```

- [ ] **Step 3: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: add CollectionController and CategoryController"
```

---

## Task 9: Collection 功能測試

**Files:**
- Create: `backend/tests/Feature/CollectionTest.php`

- [ ] **Step 1: 建立 CollectionTest**

```bash
cd backend
php artisan make:test CollectionTest
```

```php
<?php

namespace Tests\Feature;

use App\Models\CollectionCategory;
use App\Models\CollectionWork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $user = User::factory()->create(['role' => 'admin']);
        return $user->createToken('test')->plainTextToken;
    }

    public function test_can_list_collection(): void
    {
        CollectionWork::factory()->count(3)->create();

        $response = $this->getJson('/api/collection');

        $response->assertOk()->assertJsonCount(3);
    }

    public function test_can_filter_by_type(): void
    {
        CollectionWork::factory()->create(['type' => 'anime']);
        CollectionWork::factory()->create(['type' => 'manga']);

        $response = $this->getJson('/api/collection?type=anime');

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_can_get_stats(): void
    {
        CollectionWork::factory()->count(2)->create(['status' => 'completed']);
        CollectionWork::factory()->create(['status' => 'watching']);

        $response = $this->getJson('/api/collection/stats');

        $response->assertOk()
                 ->assertJsonPath('total', 3)
                 ->assertJsonPath('completed', 2)
                 ->assertJsonPath('watching', 1);
    }

    public function test_admin_can_create_work(): void
    {
        $token = $this->adminToken();
        $category = CollectionCategory::factory()->create();

        $response = $this->withToken($token)->postJson('/api/collection', [
            'type' => 'anime',
            'title' => '進擊的巨人',
            'status' => 'completed',
            'category_ids' => [$category->id],
        ]);

        $response->assertCreated()
                 ->assertJsonPath('title', '進擊的巨人')
                 ->assertJsonCount(1, 'categories');
    }

    public function test_admin_can_update_work(): void
    {
        $token = $this->adminToken();
        $work = CollectionWork::factory()->create(['title' => 'Old Title']);

        $response = $this->withToken($token)->putJson("/api/collection/{$work->id}", [
            'title' => 'New Title',
        ]);

        $response->assertOk()->assertJsonPath('title', 'New Title');
    }

    public function test_admin_can_delete_work(): void
    {
        $token = $this->adminToken();
        $work = CollectionWork::factory()->create();

        $response = $this->withToken($token)->deleteJson("/api/collection/{$work->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('collection_works', ['id' => $work->id]);
    }

    public function test_unauthenticated_cannot_create_work(): void
    {
        $response = $this->postJson('/api/collection', [
            'type' => 'anime',
            'title' => 'Test',
            'status' => 'plan',
        ]);

        $response->assertStatus(401);
    }
}
```

- [ ] **Step 2: 建立 CollectionWork + CollectionCategory factory**

```bash
php artisan make:factory CollectionWorkFactory --model=CollectionWork
php artisan make:factory CollectionCategoryFactory --model=CollectionCategory
```

`CollectionWorkFactory.php`：

```php
public function definition(): array
{
    return [
        'type'   => $this->faker->randomElement(['anime', 'manga']),
        'title'  => $this->faker->sentence(3),
        'status' => $this->faker->randomElement(['watching', 'completed', 'plan', 'on_hold', 'dropped']),
    ];
}
```

`CollectionCategoryFactory.php`：

```php
public function definition(): array
{
    return [
        'name'          => $this->faker->unique()->word(),
        'group'         => $this->faker->randomElement(['theme', 'source', 'media_type']),
        'display_order' => $this->faker->numberBetween(1, 10),
    ];
}
```

- [ ] **Step 3: 執行 Collection 測試確認全部通過**

```bash
php artisan test tests/Feature/CollectionTest.php --verbose
```

預期：7 tests passed。

- [ ] **Step 4: Commit**

```bash
cd ..
git add backend/
git commit -m "test: add collection feature tests and factories"
```

---

## Task 10: VocabularyController

**Files:**
- Create: `backend/app/Http/Controllers/Api/VocabularyController.php`

- [ ] **Step 1: 建立 VocabularyController**

```bash
cd backend
php artisan make:controller Api/VocabularyController
```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyVocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VocabularyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StudyVocabulary::query();

        if ($request->filled('search')) {
            $query->where('word', 'like', "%{$request->search}%")
                  ->orWhere('meaning', 'like', "%{$request->search}%");
        }

        if ($request->filled('familiarity')) {
            $query->where('familiarity', $request->familiarity);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total'           => StudyVocabulary::count(),
            'added_this_week' => StudyVocabulary::where('created_at', '>=', now()->startOfWeek())->count(),
            'pending_review'  => StudyVocabulary::where('next_review_at', '<=', now())->count(),
            'avg_familiarity' => round(StudyVocabulary::avg('familiarity'), 1),
        ]);
    }

    public function reviewQueue(): JsonResponse
    {
        $words = StudyVocabulary::where('next_review_at', '<=', now())
            ->orWhereNull('next_review_at')
            ->orderBy('next_review_at')
            ->get();

        return response()->json($words);
    }

    public function lookup(Request $request): JsonResponse
    {
        $request->validate(['word' => 'required|string|max:100']);
        $word = trim($request->word);

        $result = [
            'word'           => $word,
            'meaning'        => null,
            'part_of_speech' => null,
            'phonetic'       => null,
            'audio_url'      => null,
            'example'        => null,
        ];

        $dictResponse = Http::timeout(5)->get(
            config('services.dictionary_api') . "/api/v2/entries/en/{$word}"
        );

        if ($dictResponse->successful()) {
            $data    = $dictResponse->json()[0] ?? null;
            $meaning = $data['meanings'][0] ?? null;

            $result['part_of_speech'] = $meaning['partOfSpeech'] ?? null;
            $result['example']        = $meaning['definitions'][0]['example'] ?? null;
            $result['phonetic']       = $data['phonetic'] ?? null;

            $audioUrl = collect($data['phonetics'] ?? [])
                ->first(fn($p) => !empty($p['audio']));
            $result['audio_url'] = $audioUrl['audio'] ?? null;
        }

        $transResponse = Http::timeout(5)->get(
            config('services.mymemory_api') . '/get',
            ['q' => $word, 'langpair' => 'en|zh-TW']
        );

        if ($transResponse->successful()) {
            $result['meaning'] = $transResponse->json()['responseData']['translatedText'] ?? null;
        }

        return response()->json($result);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'word'           => 'required|string|max:100|unique:study_vocabulary,word',
            'meaning'        => 'required|string',
            'part_of_speech' => 'nullable|string|max:30',
            'phonetic'       => 'nullable|string|max:100',
            'audio_url'      => 'nullable|url|max:500',
            'example'        => 'nullable|string',
            'example_zh'     => 'nullable|string',
            'source'         => 'nullable|string|max:255',
            'note'           => 'nullable|string',
            'auto_filled'    => 'boolean',
        ]);

        $validated['next_review_at'] = now()->addDay();

        $word = StudyVocabulary::create($validated);
        return response()->json($word, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $word = StudyVocabulary::findOrFail($id);

        $validated = $request->validate([
            'word'           => "sometimes|string|max:100|unique:study_vocabulary,word,{$id}",
            'meaning'        => 'sometimes|string',
            'part_of_speech' => 'nullable|string|max:30',
            'phonetic'       => 'nullable|string|max:100',
            'audio_url'      => 'nullable|url|max:500',
            'example'        => 'nullable|string',
            'example_zh'     => 'nullable|string',
            'source'         => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);

        $word->update($validated);
        return response()->json($word);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'result' => 'required|in:forgot,vague,remembered,mastered',
        ]);

        $word = StudyVocabulary::findOrFail($id);
        [$nextReview, $newFamiliarity] = $this->calculateNextReview($word, $request->result);

        $word->update([
            'next_review_at'  => $nextReview,
            'last_reviewed_at' => now(),
            'familiarity'     => $newFamiliarity,
            'review_count'    => $word->review_count + 1,
            'correct_count'   => in_array($request->result, ['remembered', 'mastered'])
                                 ? $word->correct_count + 1
                                 : $word->correct_count,
        ]);

        return response()->json($word->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        StudyVocabulary::findOrFail($id)->delete();
        return response()->json(['message' => '已刪除']);
    }

    private function calculateNextReview(StudyVocabulary $word, string $result): array
    {
        $now = now();

        // 目前距離下次複習的天數（若無紀錄則預設 1 天）
        $currentInterval = 1;
        if ($word->next_review_at && $word->last_reviewed_at) {
            $currentInterval = max(1, (int) $word->last_reviewed_at->diffInDays($word->next_review_at));
        }

        return match ($result) {
            'forgot'     => [$now->copy()->addDay(),                           max(0, $word->familiarity - 1)],
            'vague'      => [$now->copy()->addDays($currentInterval),          $word->familiarity],
            'remembered' => [$now->copy()->addDays($currentInterval * 2),      min(5, $word->familiarity + 1)],
            'mastered'   => [$now->copy()->addDays((int)($currentInterval * 2.5)), min(5, $word->familiarity + 1)],
        };
    }
}
```

- [ ] **Step 2: Commit**

```bash
cd ..
git add backend/
git commit -m "feat: add VocabularyController with SM-2 review algorithm and lookup proxy"
```

---

## Task 11: Vocabulary 功能測試

**Files:**
- Create: `backend/tests/Feature/VocabularyTest.php`

- [ ] **Step 1: 建立 VocabularyTest**

```bash
cd backend
php artisan make:test VocabularyTest
php artisan make:factory StudyVocabularyFactory --model=StudyVocabulary
```

`StudyVocabularyFactory.php`：

```php
public function definition(): array
{
    return [
        'word'        => $this->faker->unique()->word(),
        'meaning'     => $this->faker->sentence(),
        'familiarity' => $this->faker->numberBetween(0, 5),
    ];
}
```

`VocabularyTest.php`：

```php
<?php

namespace Tests\Feature;

use App\Models\StudyVocabulary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VocabularyTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        return User::factory()->create(['role' => 'admin'])
                   ->createToken('test')->plainTextToken;
    }

    public function test_can_list_vocabulary(): void
    {
        StudyVocabulary::factory()->count(5)->create();

        $response = $this->getJson('/api/vocabulary');

        $response->assertOk()->assertJsonCount(5);
    }

    public function test_can_get_stats(): void
    {
        StudyVocabulary::factory()->count(3)->create();

        $response = $this->getJson('/api/vocabulary/stats');

        $response->assertOk()
                 ->assertJsonStructure(['total', 'added_this_week', 'pending_review', 'avg_familiarity'])
                 ->assertJsonPath('total', 3);
    }

    public function test_admin_can_create_word(): void
    {
        $token = $this->adminToken();

        $response = $this->withToken($token)->postJson('/api/vocabulary', [
            'word'    => 'ephemeral',
            'meaning' => '短暫的',
        ]);

        $response->assertCreated()
                 ->assertJsonPath('word', 'ephemeral');
        $this->assertDatabaseHas('study_vocabulary', ['word' => 'ephemeral']);
    }

    public function test_duplicate_word_returns_422(): void
    {
        $token = $this->adminToken();
        StudyVocabulary::factory()->create(['word' => 'ephemeral']);

        $response = $this->withToken($token)->postJson('/api/vocabulary', [
            'word'    => 'ephemeral',
            'meaning' => '短暫的',
        ]);

        $response->assertStatus(422);
    }

    public function test_review_forgot_resets_to_one_day(): void
    {
        $token = $this->adminToken();
        $word = StudyVocabulary::factory()->create([
            'familiarity'     => 3,
            'next_review_at'  => now()->addDays(7),
            'last_reviewed_at' => now()->subDays(7),
        ]);

        $response = $this->withToken($token)->putJson("/api/vocabulary/{$word->id}/review", [
            'result' => 'forgot',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('study_vocabulary', [
            'id'          => $word->id,
            'familiarity' => 2, // decreased by 1
        ]);
    }

    public function test_review_mastered_increases_familiarity(): void
    {
        $token = $this->adminToken();
        $word = StudyVocabulary::factory()->create([
            'familiarity'     => 2,
            'next_review_at'  => now()->addDay(),
            'last_reviewed_at' => now()->subDay(),
        ]);

        $response = $this->withToken($token)->putJson("/api/vocabulary/{$word->id}/review", [
            'result' => 'mastered',
        ]);

        $response->assertOk();
        $updated = StudyVocabulary::find($word->id);
        $this->assertEquals(3, $updated->familiarity);
        $this->assertEquals(1, $updated->correct_count);
    }

    public function test_review_queue_returns_due_words(): void
    {
        StudyVocabulary::factory()->create(['next_review_at' => now()->subDay()]);
        StudyVocabulary::factory()->create(['next_review_at' => now()->addDay()]);
        StudyVocabulary::factory()->create(['next_review_at' => null]);

        $response = $this->getJson('/api/vocabulary/review-queue');

        $response->assertOk()->assertJsonCount(2);
    }
}
```

- [ ] **Step 2: 執行所有測試，確認全部通過**

```bash
php artisan test --verbose
```

預期：所有 tests passed，無 failures。

- [ ] **Step 3: Commit**

```bash
cd ..
git add backend/
git commit -m "test: add vocabulary feature tests and StudyVocabulary factory"
```

---

## Task 12: 最終驗證與部署準備

**Files:**
- Modify: `backend/.env.example`（確認完整）
- Create: `backend/README.md`（啟動說明）

- [ ] **Step 1: 確認 .env.example 完整**

`backend/.env.example` 最終版本應包含：

```
APP_NAME=PersonalSite
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-api-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=personal_site
DB_USERNAME=root
DB_PASSWORD=

FRONTEND_URL=https://your-github-pages-url

DICTIONARY_API_URL=https://api.dictionaryapi.dev
MYMEMORY_API_URL=https://api.mymemory.translated.net

ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=changeme
```

- [ ] **Step 2: 執行完整測試套件**

```bash
cd backend
php artisan test
```

預期：所有測試通過，Exit code 0。

- [ ] **Step 3: 確認 API 本機正常運行**

```bash
php artisan migrate:fresh --seed
php artisan serve
```

在另一個終端執行：

```bash
curl http://localhost:8000/api/collection
curl http://localhost:8000/api/vocabulary/stats
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"changeme"}'
```

預期：前兩個回傳空陣列/統計，第三個回傳包含 `token` 的 JSON。

- [ ] **Step 4: 最終 commit**

```bash
cd ..
git add backend/
git commit -m "feat: complete Laravel 11 backend migration"
```
