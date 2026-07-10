@props([
    'title' => null,
    'description' => '個人網站 — 簡歷、作品收藏與單字庫。',
])

<!doctype html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' - 個人網站' : '個人網站' }}</title>
    <meta name="description" content="{{ $description }}">

    <meta property="og:title" content="{{ $title ? $title.' - 個人網站' : '個人網站' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- FOUC guard: apply saved theme before first paint --}}
    <script>
        (function () {
            var saved = localStorage.getItem('theme') || 'light';
            document.documentElement.dataset.theme = saved;
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-[var(--color-bg)] text-[var(--color-ink)] min-h-screen flex flex-col">
    <nav class="border-b border-[var(--color-line)] bg-[var(--color-surface)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="font-display font-semibold text-lg tracking-tight">
                    個人網站
                </a>

                <div class="hidden sm:flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('home') }}" class="hover:text-[var(--color-accent-text)] transition-colors {{ request()->routeIs('home') ? 'text-[var(--color-accent-text)]' : '' }}">首頁</a>
                    <a href="{{ route('resume') }}" class="hover:text-[var(--color-accent-text)] transition-colors {{ request()->routeIs('resume') ? 'text-[var(--color-accent-text)]' : '' }}">簡歷</a>
                    <a href="{{ route('collection') }}" class="hover:text-[var(--color-accent-text)] transition-colors {{ request()->routeIs('collection') ? 'text-[var(--color-accent-text)]' : '' }}">收藏</a>
                    <a href="{{ route('vocabulary') }}" class="hover:text-[var(--color-accent-text)] transition-colors {{ request()->routeIs('vocabulary') ? 'text-[var(--color-accent-text)]' : '' }}">單字庫</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium hover:text-[var(--color-accent-text)] transition-colors">
                                登出
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium hover:text-[var(--color-accent-text)] transition-colors">
                            登入
                        </a>
                    @endauth

                    <button
                        type="button"
                        data-theme-toggle
                        onclick="toggleTheme()"
                        aria-label="切換深色模式"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-[var(--color-line)] text-[var(--color-muted)] hover:text-[var(--color-accent-text)] transition-colors"
                    >
                        <span aria-hidden="true">🌓</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-[var(--color-line)] bg-[var(--color-surface)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-[var(--color-muted)]">
            &copy; {{ date('Y') }} 個人網站. All rights reserved.
        </div>
    </footer>

    @livewireScripts
    <x-toast />
</body>
</html>
