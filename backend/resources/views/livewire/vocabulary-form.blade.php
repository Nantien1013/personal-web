<div class="mx-auto max-w-2xl">
    <x-card>
        <form wire:submit="save" class="flex flex-col gap-5">
            <div>
                <h2 class="font-display text-xl font-semibold">新增單字</h2>
                <p class="mt-1 text-sm text-[var(--color-muted)]">輸入單字後可自動查詢釋義、音標與例句。</p>
            </div>

            {{-- Word + lookup --}}
            <div>
                <label for="vf-word" class="mb-1 block text-sm font-medium">單字 <span class="text-[var(--color-accent-text)]">*</span></label>
                <div class="flex gap-2">
                    <input
                        id="vf-word"
                        type="text"
                        wire:model.live.debounce.500ms="word"
                        wire:keydown.enter.prevent="lookup"
                        placeholder="例如：serendipity"
                        class="flex-1 rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"
                    />
                    <x-button type="button" variant="ghost" wire:click="lookup" wire:loading.attr="disabled" wire:target="lookup">
                        <span wire:loading.remove wire:target="lookup">查詢</span>
                        <span wire:loading wire:target="lookup" class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                            查詢中
                        </span>
                    </x-button>
                </div>
                @error('word') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @if ($auto_filled)
                    <p class="mt-1 text-xs text-[var(--color-accent-text)]">已自動填入查詢結果，可自行修改。</p>
                @endif
            </div>

            {{-- Meaning --}}
            <div>
                <label for="vf-meaning" class="mb-1 block text-sm font-medium">釋義 <span class="text-[var(--color-accent-text)]">*</span></label>
                <textarea
                    id="vf-meaning"
                    wire:model="meaning"
                    rows="2"
                    class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"
                ></textarea>
                @error('meaning') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="vf-pos" class="mb-1 block text-sm font-medium">詞性</label>
                    <input id="vf-pos" type="text" wire:model="part_of_speech"
                        class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]" />
                    @error('part_of_speech') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="vf-phonetic" class="mb-1 block text-sm font-medium">音標</label>
                    <input id="vf-phonetic" type="text" wire:model="phonetic"
                        class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 font-mono text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]" />
                    @error('phonetic') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="vf-example" class="mb-1 block text-sm font-medium">例句</label>
                <textarea id="vf-example" wire:model="example" rows="2"
                    class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"></textarea>
                @error('example') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="vf-example-zh" class="mb-1 block text-sm font-medium">例句翻譯</label>
                    <input id="vf-example-zh" type="text" wire:model="example_zh"
                        class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]" />
                    @error('example_zh') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="vf-source" class="mb-1 block text-sm font-medium">來源</label>
                    <input id="vf-source" type="text" wire:model="source"
                        class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]" />
                    @error('source') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="vf-note" class="mb-1 block text-sm font-medium">筆記</label>
                <textarea id="vf-note" wire:model="note" rows="2"
                    class="w-full rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)]"></textarea>
                @error('note') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">儲存</span>
                    <span wire:loading wire:target="save">儲存中…</span>
                </x-button>
            </div>
        </form>
    </x-card>
</div>
