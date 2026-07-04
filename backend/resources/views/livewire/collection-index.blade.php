<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col gap-6">
        {{-- Header --}}
        <div class="flex items-center justify-between gap-4">
            <h1 class="font-display text-3xl font-semibold">收藏</h1>
            @can('create', App\Models\CollectionWork::class)
                <x-button wire:click="$dispatch('open-collection-form')">新增</x-button>
            @endcan
        </div>

        {{-- Stats --}}
        <x-card>
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
                <x-stat label="總數" :value="$stats['total']" />
                <x-stat label="動畫" :value="$stats['anime']" />
                <x-stat label="漫畫" :value="$stats['manga']" />
                <x-stat label="已完成" :value="$stats['completed']" />
                <x-stat label="觀看中" :value="$stats['watching']" />
                <x-stat label="最愛" :value="$stats['favorites']" />
            </div>
        </x-card>

        {{-- Type tabs --}}
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="類型">
            @foreach (['all' => '全部', 'anime' => '動畫', 'manga' => '漫畫'] as $value => $label)
                <button
                    type="button"
                    wire:click="$set('type', '{{ $value }}')"
                    role="tab"
                    aria-selected="{{ $type === $value ? 'true' : 'false' }}"
                    @class([
                        'rounded-full px-4 py-1.5 text-sm font-medium transition-colors border',
                        'bg-[var(--color-accent-text)] text-white border-transparent' => $type === $value,
                        'border-[var(--color-line)] text-[var(--color-muted)] hover:text-[var(--color-accent-text)]' => $type !== $value,
                    ])
                >{{ $label }}</button>
            @endforeach
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="搜尋標題…"
                    aria-label="搜尋"
                    class="w-full sm:max-w-xs rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"
                >

                <select wire:model.live="status" aria-label="狀態" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                    <option value="">全部狀態</option>
                    <option value="watching">觀看中</option>
                    <option value="completed">已完成</option>
                    <option value="plan">計畫中</option>
                    <option value="on_hold">暫停</option>
                    <option value="dropped">棄坑</option>
                </select>

                <select wire:model.live="season" aria-label="季節" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                    <option value="">全部季節</option>
                    <option value="winter">冬</option>
                    <option value="spring">春</option>
                    <option value="summer">夏</option>
                    <option value="autumn">秋</option>
                </select>

                <label class="inline-flex items-center gap-2 text-sm text-[var(--color-muted)]">
                    <input type="checkbox" wire:model.live="favoriteOnly" class="rounded border-[var(--color-line)]">
                    只看最愛
                </label>
            </div>

            <div class="flex items-center gap-2">
                <select wire:model.live="sort" aria-label="排序" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                    <option value="newest">最新</option>
                    <option value="rating">評分</option>
                    <option value="year">年份</option>
                    <option value="title">標題</option>
                </select>

                <div class="inline-flex rounded-lg border border-[var(--color-line)] overflow-hidden" role="group" aria-label="檢視方式">
                    <button type="button" wire:click="$set('view', 'card')" aria-pressed="{{ $view === 'card' ? 'true' : 'false' }}"
                        @class(['px-3 py-2 text-sm', 'bg-[var(--color-accent-text)] text-white' => $view === 'card', 'text-[var(--color-muted)]' => $view !== 'card'])>卡片</button>
                    <button type="button" wire:click="$set('view', 'table')" aria-pressed="{{ $view === 'table' ? 'true' : 'false' }}"
                        @class(['px-3 py-2 text-sm', 'bg-[var(--color-accent-text)] text-white' => $view === 'table', 'text-[var(--color-muted)]' => $view !== 'table'])>列表</button>
                </div>
            </div>
        </div>

        {{-- Category filter --}}
        @if ($categories->isNotEmpty())
            <div class="flex flex-col gap-3 rounded-xl border border-[var(--color-line)] bg-[var(--color-surface)] p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">分類篩選</span>
                    <select wire:model.live="categoryMode" aria-label="分類模式" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-2 py-1 text-xs">
                        <option value="or">符合任一</option>
                        <option value="and">符合全部</option>
                    </select>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($categories as $group => $groupCategories)
                        @foreach ($groupCategories as $category)
                            <label @class([
                                'inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-xs',
                                'border-[var(--color-accent-text)] text-[var(--color-accent-text)]' => in_array($category->id, $categoryIds),
                                'border-[var(--color-line)] text-[var(--color-muted)]' => ! in_array($category->id, $categoryIds),
                            ])>
                                <input type="checkbox" class="sr-only" wire:model.live="categoryIds" value="{{ $category->id }}">
                                {{ $category->name }}
                            </label>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Loading skeleton --}}
        <div wire:loading.delay class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @for ($i = 0; $i < 6; $i++)
                <x-skeleton class="h-40" />
            @endfor
        </div>

        {{-- Results --}}
        <div wire:loading.remove>
            @if ($works->isEmpty())
                <x-empty-state message="沒有符合條件的收藏。" />
            @elseif ($view === 'table')
                <div class="overflow-x-auto rounded-xl border border-[var(--color-line)]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[var(--color-surface)] text-[var(--color-muted)]">
                            <tr>
                                <th class="px-4 py-2 font-medium">標題</th>
                                <th class="px-4 py-2 font-medium">類型</th>
                                <th class="px-4 py-2 font-medium">年份</th>
                                <th class="px-4 py-2 font-medium">評分</th>
                                <th class="px-4 py-2 font-medium">分類</th>
                                @can('update', App\Models\CollectionWork::class)
                                    <th class="px-4 py-2 font-medium">操作</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($works as $work)
                                <tr class="border-t border-[var(--color-line)]" wire:key="row-{{ $work->id }}">
                                    <td class="px-4 py-2">
                                        {{ $work->title }}
                                        @if ($work->is_favorite)<span aria-label="最愛" title="最愛">★</span>@endif
                                    </td>
                                    <td class="px-4 py-2">{{ $work->type === 'anime' ? '動畫' : '漫畫' }}</td>
                                    <td class="px-4 py-2">{{ $work->release_year }}</td>
                                    <td class="px-4 py-2">{{ $work->rating !== null ? $work->rating.' / 5' : '—' }}</td>
                                    <td class="px-4 py-2">
                                        @foreach ($work->categories as $category)
                                            <x-tag>{{ $category->name }}</x-tag>
                                        @endforeach
                                    </td>
                                    @can('update', $work)
                                        <td class="px-4 py-2">
                                            <button type="button" class="text-[var(--color-accent-text)] hover:underline" wire:click="$dispatch('open-collection-form', { workId: {{ $work->id }} })">編輯</button>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($works as $work)
                        <x-card wire:key="card-{{ $work->id }}" class="flex flex-col gap-3">
                            <div class="flex items-start gap-3">
                                @if ($work->cover_url)
                                    <img src="{{ $work->cover_url }}" alt="{{ $work->title }}" class="h-20 w-14 flex-shrink-0 rounded object-cover" loading="lazy">
                                @endif
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5">
                                        <h2 class="font-medium leading-tight">{{ $work->title }}</h2>
                                        @if ($work->is_favorite)<span aria-label="最愛" title="最愛" class="text-[var(--color-accent-text)]">★</span>@endif
                                    </div>
                                    @if ($work->title_original)
                                        <p class="text-xs text-[var(--color-muted)]">{{ $work->title_original }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5">
                                @if ($work->release_year)
                                    <x-tag color="muted">{{ $work->release_year }}@if ($work->release_season) · {{ ['winter' => '冬', 'spring' => '春', 'summer' => '夏', 'autumn' => '秋'][$work->release_season] ?? $work->release_season }}@endif</x-tag>
                                @endif
                                @foreach ($work->categories as $category)
                                    <x-tag>{{ $category->name }}</x-tag>
                                @endforeach
                            </div>

                            <div class="mt-auto flex items-center justify-between">
                                <span class="text-sm text-[var(--color-muted)]">
                                    @if ($work->rating !== null){{ str_repeat('★', $work->rating).str_repeat('☆', 5 - $work->rating) }}@endif
                                </span>
                                @can('update', $work)
                                    <div class="flex items-center gap-2 text-sm">
                                        <button type="button" class="text-[var(--color-accent-text)] hover:underline" wire:click="$dispatch('open-collection-form', { workId: {{ $work->id }} })">編輯</button>
                                    </div>
                                @endcan
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif

            <div class="mt-6">
                {{ $works->links() }}
            </div>
        </div>
    </div>

    <livewire:collection-form />
</div>
