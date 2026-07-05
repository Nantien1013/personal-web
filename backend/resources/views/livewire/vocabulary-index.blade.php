<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- Header + stats --}}
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-semibold tracking-tight">單字庫</h1>
                <p class="mt-1 text-sm text-[var(--color-muted)]">瀏覽、新增與複習英文單字。</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-card class="!p-4"><x-stat label="總單字數" :value="$stats['total']" /></x-card>
            <x-card class="!p-4"><x-stat label="本週新增" :value="$stats['added_this_week']" /></x-card>
            <x-card class="!p-4"><x-stat label="待複習" :value="$stats['pending_review']" /></x-card>
            <x-card class="!p-4"><x-stat label="平均熟悉度" :value="$stats['avg_familiarity']" /></x-card>
        </div>

        {{-- Mode tabs --}}
        <div role="tablist" aria-label="單字庫模式" class="inline-flex w-fit rounded-xl border border-[var(--color-line)] bg-[var(--color-surface)] p-1">
            @foreach (['browse' => '瀏覽', 'add' => '新增', 'flashcard' => '複習'] as $key => $label)
                <button
                    type="button"
                    role="tab"
                    :aria-selected="'{{ $mode === $key ? 'true' : 'false' }}'"
                    wire:click="setMode('{{ $key }}')"
                    @class([
                        'rounded-lg px-4 py-1.5 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]',
                        'bg-[var(--color-accent-text)] text-white shadow-sm' => $mode === $key,
                        'text-[var(--color-muted)] hover:text-[var(--color-accent-text)]' => $mode !== $key,
                    ])
                >{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="mt-8">
        @if ($mode === 'add')
            <livewire:vocabulary-form />
        @elseif ($mode === 'flashcard')
            <livewire:flashcard />
        @else
            {{-- Browse mode --}}
            <div class="flex flex-col gap-6">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[220px]">
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="搜尋單字或釋義…"
                            aria-label="搜尋單字"
                            class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"
                        />
                    </div>
                    <select
                        wire:model.live="familiarity"
                        aria-label="依熟悉度篩選"
                        class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"
                    >
                        <option value="">全部熟悉度</option>
                        @for ($i = 0; $i <= 5; $i++)
                            <option value="{{ $i }}">熟悉度 {{ $i }}</option>
                        @endfor
                    </select>
                </div>

                @if ($words->isEmpty())
                    <x-empty-state message="找不到符合條件的單字。" />
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($words as $word)
                            <x-card wire:key="word-{{ $word->id }}" class="flex flex-col gap-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h2 class="font-display text-xl font-semibold">{{ $word->word }}</h2>
                                            @if ($word->part_of_speech)
                                                <x-tag color="accent">{{ $word->part_of_speech }}</x-tag>
                                            @endif
                                        </div>
                                        @if ($word->phonetic)
                                            <p class="mt-0.5 font-mono text-sm text-[var(--color-muted)]">{{ $word->phonetic }}</p>
                                        @endif
                                    </div>
                                    <button
                                        type="button"
                                        aria-label="發音"
                                        title="發音"
                                        x-data
                                        x-on:click="
                                            @if ($word->audio_url)
                                                new Audio(@js($word->audio_url)).play()
                                            @else
                                                (function () {
                                                    const u = new SpeechSynthesisUtterance(@js($word->word));
                                                    u.lang = 'en-US';
                                                    window.speechSynthesis.speak(u);
                                                })()
                                            @endif
                                        "
                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-[var(--color-line)] text-[var(--color-muted)] hover:text-[var(--color-accent-text)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"
                                    >
                                        <span aria-hidden="true">🔊</span>
                                    </button>
                                </div>

                                <p class="text-sm leading-relaxed">{{ $word->meaning }}</p>

                                @if ($word->example)
                                    <p class="text-sm italic text-[var(--color-muted)]">{{ $word->example }}</p>
                                @endif

                                {{-- Familiarity meter --}}
                                <div class="mt-auto flex items-center gap-2">
                                    <span class="text-xs text-[var(--color-muted)]">熟悉度</span>
                                    <div class="flex gap-1" role="img" aria-label="熟悉度 {{ $word->familiarity }} / 5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span @class([
                                                'h-1.5 w-6 rounded-full',
                                                'bg-[var(--color-accent-text)]' => $i <= $word->familiarity,
                                                'bg-[var(--color-line)]' => $i > $word->familiarity,
                                            ])></span>
                                        @endfor
                                    </div>
                                </div>
                            </x-card>
                        @endforeach
                    </div>

                    <div>{{ $words->links() }}</div>
                @endif
            </div>
        @endif
    </div>
</div>
