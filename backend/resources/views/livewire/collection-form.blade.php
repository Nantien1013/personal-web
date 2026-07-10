<div>
    @if ($show)
        {{-- Livewire-driven modal: $show is the single source of truth. Escape and
             backdrop both call $wire.close() so Alpine can never desync from server. --}}
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" x-data x-on:keydown.escape.window="$wire.close()" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/50" wire:click="close"></div>
            <div class="relative mb-6 sm:w-full sm:max-w-2xl sm:mx-auto bg-[var(--color-surface)] text-[var(--color-ink)] rounded-lg overflow-hidden shadow-xl border border-[var(--color-line)]">
            <form wire:submit="save" class="flex flex-col gap-5 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-xl font-semibold">
                        {{ $workId ? '編輯收藏' : '新增收藏' }}
                    </h2>
                    <button type="button" wire:click="close" class="text-[var(--color-muted)] hover:text-[var(--color-ink)]" aria-label="關閉">✕</button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Type --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium" for="cf-type">類型</label>
                        <select id="cf-type" wire:model.live="type" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            <option value="anime">動畫</option>
                            <option value="manga">漫畫</option>
                        </select>
                        @error('type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium" for="cf-status">狀態</label>
                        <select id="cf-status" wire:model="status" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            <option value="watching">觀看中</option>
                            <option value="completed">已完成</option>
                            <option value="plan">計畫中</option>
                            <option value="on_hold">暫停</option>
                            <option value="dropped">棄坑</option>
                        </select>
                        @error('status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Title --}}
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-sm font-medium" for="cf-title">標題</label>
                        <input id="cf-title" type="text" wire:model="title" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                        @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Original title --}}
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-sm font-medium" for="cf-title-original">原文標題</label>
                        <input id="cf-title-original" type="text" wire:model="title_original" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                        @error('title_original') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Cover URL --}}
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-sm font-medium" for="cf-cover">封面網址</label>
                        <input id="cf-cover" type="url" wire:model="cover_url" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                        @error('cover_url') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Rating --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium" for="cf-rating">評分 (0–5)</label>
                        <input id="cf-rating" type="number" min="0" max="5" wire:model="rating" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                        @error('rating') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Favorite --}}
                    <div class="flex items-end gap-2">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="is_favorite" class="rounded border-[var(--color-line)]">
                            最愛
                        </label>
                    </div>

                    {{-- Release year --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium" for="cf-year">年份</label>
                        <input id="cf-year" type="number" min="1900" max="2100" wire:model="release_year" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                        @error('release_year') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Release season --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium" for="cf-season">季節</label>
                        <select id="cf-season" wire:model="release_season" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            <option value="">—</option>
                            <option value="winter">冬</option>
                            <option value="spring">春</option>
                            <option value="summer">夏</option>
                            <option value="autumn">秋</option>
                        </select>
                        @error('release_season') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Anime-specific fields --}}
                    @if ($type === 'anime')
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium" for="cf-episodes-total">總集數</label>
                            <input id="cf-episodes-total" type="number" min="0" wire:model="episodes_total" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            @error('episodes_total') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium" for="cf-episodes-watched">已看集數</label>
                            <input id="cf-episodes-watched" type="number" min="0" wire:model="episodes_watched" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            @error('episodes_watched') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1 sm:col-span-2">
                            <label class="text-sm font-medium" for="cf-studio">製作公司</label>
                            <input id="cf-studio" type="text" wire:model="studio" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            @error('studio') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium" for="cf-volumes-total">總卷數</label>
                            <input id="cf-volumes-total" type="number" min="0" wire:model="volumes_total" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            @error('volumes_total') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium" for="cf-volumes-read">已讀卷數</label>
                            <input id="cf-volumes-read" type="number" min="0" wire:model="volumes_read" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            @error('volumes_read') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1 sm:col-span-2">
                            <label class="text-sm font-medium" for="cf-author">作者</label>
                            <input id="cf-author" type="text" wire:model="author" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm">
                            @error('author') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Categories --}}
                    <div class="flex flex-col gap-2 sm:col-span-2">
                        <span class="text-sm font-medium">分類</span>
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
                        @error('categoryIds') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Note --}}
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-sm font-medium" for="cf-note">備註</label>
                        <textarea id="cf-note" rows="3" wire:model="note" class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm"></textarea>
                        @error('note') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <div>
                        @if ($workId)
                            <x-button type="button" variant="ghost" wire:click="delete" wire:confirm="確定要刪除嗎？">刪除</x-button>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <x-button type="button" variant="ghost" wire:click="close">取消</x-button>
                        <x-button type="submit">儲存</x-button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    @endif
</div>
