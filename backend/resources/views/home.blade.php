<x-layouts.app>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <section class="py-20 sm:py-28 motion-safe:animate-[fadeup_0.6s_ease-out]">
            <p class="font-mono text-sm text-[var(--color-accent-text)] mb-4">
                {{-- Placeholder positioning line --}}
                資安研究 × 機器學習 × 軟體開發
            </p>
            <h1 class="font-display text-4xl sm:text-6xl font-semibold tracking-tight leading-tight">
                {{-- PLACEHOLDER: replace with your real name --}}
                [Your Name]
            </h1>
            <p class="mt-6 max-w-2xl text-lg text-[var(--color-muted)] leading-relaxed">
                {{-- PLACEHOLDER positioning / bio line --}}
                我專注於資訊安全研究、機器學習與軟體開發，喜歡把複雜的問題拆解成優雅、可靠的解法。
                這裡收錄我的簡歷、作品收藏與正在學習的單字。
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <x-button href="{{ route('resume') }}">查看簡歷</x-button>
                <x-button variant="ghost" href="{{ route('collection') }}">瀏覽收藏</x-button>
            </div>
        </section>

        {{-- Navigation cards --}}
        <section class="pb-16">
            <h2 class="sr-only">網站導覽</h2>
            <div class="grid gap-5 sm:grid-cols-3">

                <a href="{{ route('resume') }}" class="group block rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] focus-visible:ring-offset-2">
                    <x-card class="h-full transition-colors group-hover:border-[var(--color-accent-text)]">
                        <div class="text-2xl mb-3" aria-hidden="true">📄</div>
                        <h3 class="font-display text-lg font-semibold">個人簡歷</h3>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">學經歷、專案作品與技能一覽，可下載 PDF。</p>
                        <span class="mt-4 inline-block text-sm font-medium text-[var(--color-accent-text)]">前往簡歷 →</span>
                    </x-card>
                </a>

                <a href="{{ route('collection') }}" class="group block rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] focus-visible:ring-offset-2">
                    <x-card class="h-full transition-colors group-hover:border-[var(--color-accent-text)]">
                        <div class="text-2xl mb-3" aria-hidden="true">🔖</div>
                        <h3 class="font-display text-lg font-semibold">收藏</h3>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">我覺得值得收藏的文章、工具與作品集。</p>
                        <span class="mt-4 inline-block text-sm font-medium text-[var(--color-accent-text)]">瀏覽收藏 →</span>
                    </x-card>
                </a>

                <a href="{{ route('vocabulary') }}" class="group block rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] focus-visible:ring-offset-2">
                    <x-card class="h-full transition-colors group-hover:border-[var(--color-accent-text)]">
                        <div class="text-2xl mb-3" aria-hidden="true">📚</div>
                        <h3 class="font-display text-lg font-semibold">單字庫</h3>
                        <p class="mt-2 text-sm text-[var(--color-muted)]">用間隔重複法持續累積的英文單字學習庫。</p>
                        <span class="mt-4 inline-block text-sm font-medium text-[var(--color-accent-text)]">開始複習 →</span>
                    </x-card>
                </a>

            </div>
        </section>

        {{-- Social links --}}
        <section class="pb-24">
            <h2 class="sr-only">社群連結</h2>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm">
                {{-- PLACEHOLDER links: replace with real profiles --}}
                <a href="https://github.com/your-handle" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-[var(--color-muted)] hover:text-[var(--color-accent-text)] transition-colors">
                    <span aria-hidden="true">🐙</span> GitHub
                </a>
                <a href="https://www.linkedin.com/in/your-handle" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-[var(--color-muted)] hover:text-[var(--color-accent-text)] transition-colors">
                    <span aria-hidden="true">💼</span> LinkedIn
                </a>
                <a href="mailto:you@example.com"
                   class="inline-flex items-center gap-2 text-[var(--color-muted)] hover:text-[var(--color-accent-text)] transition-colors">
                    <span aria-hidden="true">✉️</span> Email
                </a>
            </div>
        </section>

    </div>

    <style>
        @keyframes fadeup {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-layouts.app>
