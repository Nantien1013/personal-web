<div class="mx-auto max-w-xl">
    {{-- Today's progress --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-[var(--color-muted)]">
            <span>今日已複習</span>
            <span class="font-mono text-base font-semibold text-[var(--color-accent-text)]">{{ $reviewedToday }}</span>
        </div>
        <div class="text-sm text-[var(--color-muted)]">
            @if (count($queue) > 0 && $index < count($queue))
                {{ $index + 1 }} / {{ count($queue) }}
            @else
                {{ count($queue) }} / {{ count($queue) }}
            @endif
        </div>
    </div>

    @if ($index >= count($queue))
        {{-- Done / empty state --}}
        <x-empty-state message="太好了，目前沒有待複習的單字！">
            @if ($reviewedToday > 0)
                <p class="text-sm">本次共複習了 {{ $reviewedToday }} 個單字。</p>
            @endif
            <x-button type="button" variant="ghost" wire:click="loadQueue">重新載入佇列</x-button>
        </x-empty-state>
    @else
        @php $card = $queue[$index]; @endphp

        {{-- Flip card --}}
        <div wire:key="card-{{ $card['id'] }}" x-data="{ flipped: @entangle('flipped') }" class="[perspective:1200px]">
            <div
                class="relative min-h-[280px] w-full transition-transform duration-500 [transform-style:preserve-3d]"
                :class="flipped ? '[transform:rotateY(180deg)]' : ''"
            >
                {{-- Front --}}
                <div class="absolute inset-0 [backface-visibility:hidden]">
                    <x-card class="flex h-full min-h-[280px] flex-col items-center justify-center gap-4 text-center">
                        <div class="flex items-center gap-3">
                            <h2 class="font-display text-4xl font-semibold tracking-tight">{{ $card['word'] }}</h2>
                            <button
                                type="button"
                                aria-label="發音"
                                title="發音"
                                x-on:click.stop="
                                    @if (!empty($card['audio_url']))
                                        new Audio(@js($card['audio_url'])).play()
                                    @else
                                        (function () {
                                            const u = new SpeechSynthesisUtterance(@js($card['word']));
                                            u.lang = 'en-US';
                                            window.speechSynthesis.speak(u);
                                        })()
                                    @endif
                                "
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-line)] text-[var(--color-muted)] hover:text-[var(--color-accent-text)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"
                            >
                                <span aria-hidden="true">🔊</span>
                            </button>
                        </div>
                        @if (!empty($card['phonetic']))
                            <p class="font-mono text-[var(--color-muted)]">{{ $card['phonetic'] }}</p>
                        @endif
                        <x-button type="button" variant="ghost" wire:click="flip">翻牌看釋義</x-button>
                    </x-card>
                </div>

                {{-- Back --}}
                <div class="absolute inset-0 [transform:rotateY(180deg)] [backface-visibility:hidden]">
                    <x-card class="flex h-full min-h-[280px] flex-col justify-center gap-4">
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-2xl font-semibold">{{ $card['word'] }}</h3>
                            @if (!empty($card['part_of_speech']))
                                <x-tag color="accent">{{ $card['part_of_speech'] }}</x-tag>
                            @endif
                        </div>
                        <p class="text-lg leading-relaxed">{{ $card['meaning'] }}</p>
                        @if (!empty($card['example']))
                            <p class="text-sm italic text-[var(--color-muted)]">{{ $card['example'] }}</p>
                        @endif
                        <button type="button" wire:click="flip" class="self-start text-sm text-[var(--color-accent-text)] hover:underline">翻回正面</button>
                    </x-card>
                </div>
            </div>
        </div>

        {{-- Rating buttons (admin only) --}}
        @auth
            @if (auth()->user()->isAdmin())
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <button type="button" wire:click="rate('forgot')"
                        class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] dark:hover:bg-red-950/30">忘記</button>
                    <button type="button" wire:click="rate('vague')"
                        class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm font-medium text-amber-600 transition-colors hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] dark:hover:bg-amber-950/30">模糊</button>
                    <button type="button" wire:click="rate('remembered')"
                        class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm font-medium text-[var(--color-accent-text)] transition-colors hover:bg-[var(--color-accent-text)]/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]">記得</button>
                    <button type="button" wire:click="rate('mastered')"
                        class="rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-3 py-2 text-sm font-medium text-emerald-600 transition-colors hover:bg-emerald-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] dark:hover:bg-emerald-950/30">精熟</button>
                </div>
            @else
                <p class="mt-6 text-center text-sm text-[var(--color-muted)]">翻牌自我測驗。評分功能僅限管理員。</p>
            @endif
        @else
            <p class="mt-6 text-center text-sm text-[var(--color-muted)]">翻牌自我測驗。評分功能僅限管理員。</p>
        @endauth
    @endif
</div>
