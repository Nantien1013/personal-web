<x-layouts.app title="簡歷">
    <div class="resume max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Header / contact --}}
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 border-b border-[var(--color-line)] pb-8">
            <div>
                {{-- PLACEHOLDER personal data --}}
                <h1 class="font-display text-4xl font-semibold tracking-tight">[Your Name]</h1>
                <p class="mt-2 font-mono text-sm text-[var(--color-accent-text)]">資安研究 × 機器學習 × 軟體開發</p>
                <div class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-sm text-[var(--color-muted)]">
                    <a href="mailto:you@example.com" class="hover:text-[var(--color-accent-text)] transition-colors">✉️ you@example.com</a>
                    <a href="https://github.com/your-handle" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--color-accent-text)] transition-colors">🐙 github.com/your-handle</a>
                    <a href="https://www.linkedin.com/in/your-handle" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--color-accent-text)] transition-colors">💼 linkedin.com/in/your-handle</a>
                    <span>📍 Taipei, Taiwan</span>
                </div>
            </div>
            <div class="resume-actions shrink-0">
                {{-- Download CV: links to placeholder /cv.pdf, falls back to print via onclick --}}
                <x-button href="/cv.pdf" download>
                    ⬇ Download CV
                </x-button>
                <button type="button" onclick="window.print()" class="mt-2 block w-full text-center text-xs text-[var(--color-muted)] hover:text-[var(--color-accent-text)] transition-colors">
                    或列印此頁 (Print)
                </button>
            </div>
        </header>

        {{-- Summary --}}
        <section class="mt-10">
            <p class="max-w-2xl text-[var(--color-muted)] leading-relaxed">
                {{-- PLACEHOLDER summary --}}
                對資訊安全與機器學習充滿熱情的軟體工程師，擅長將研究成果落地為可維護的產品。以下內容為示範用途的佔位資料。
            </p>
        </section>

        {{-- Education --}}
        <section class="mt-12">
            <h2 class="font-display text-xl font-semibold mb-6">Education <span class="text-[var(--color-muted)] font-normal">學歷</span></h2>
            <ol class="timeline relative border-l border-[var(--color-line)] pl-6 space-y-8">
                <li class="relative">
                    <span class="timeline-dot" aria-hidden="true"></span>
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="font-semibold">[University Name] — 資訊工程碩士</h3>
                        <span class="font-mono text-xs text-[var(--color-muted)]">2023 – 2025</span>
                    </div>
                    <p class="mt-1 text-sm text-[var(--color-muted)]">研究方向：機器學習於惡意程式偵測的應用。（示範資料）</p>
                </li>
                <li class="relative">
                    <span class="timeline-dot" aria-hidden="true"></span>
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="font-semibold">[University Name] — 資訊工程學士</h3>
                        <span class="font-mono text-xs text-[var(--color-muted)]">2019 – 2023</span>
                    </div>
                    <p class="mt-1 text-sm text-[var(--color-muted)]">主修軟體開發與資訊安全基礎。（示範資料）</p>
                </li>
            </ol>
        </section>

        {{-- Experience --}}
        <section class="mt-12">
            <h2 class="font-display text-xl font-semibold mb-6">Experience <span class="text-[var(--color-muted)] font-normal">經歷</span></h2>
            <ol class="timeline relative border-l border-[var(--color-line)] pl-6 space-y-8">
                <li class="relative">
                    <span class="timeline-dot" aria-hidden="true"></span>
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="font-semibold">[Company Name] — 資安研究實習</h3>
                        <span class="font-mono text-xs text-[var(--color-muted)]">2024 – 2025</span>
                    </div>
                    <ul class="mt-2 list-disc list-inside text-sm text-[var(--color-muted)] space-y-1">
                        <li>建立自動化漏洞掃描流程，縮短回報時間 40%。（示範資料）</li>
                        <li>訓練分類模型辨識異常網路流量。</li>
                    </ul>
                </li>
                <li class="relative">
                    <span class="timeline-dot" aria-hidden="true"></span>
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="font-semibold">[Company Name] — 軟體工程實習</h3>
                        <span class="font-mono text-xs text-[var(--color-muted)]">2023 – 2024</span>
                    </div>
                    <ul class="mt-2 list-disc list-inside text-sm text-[var(--color-muted)] space-y-1">
                        <li>開發並維護 Laravel 後端服務與 REST API。（示範資料）</li>
                        <li>撰寫測試，提升測試涵蓋率至 85%。</li>
                    </ul>
                </li>
            </ol>
        </section>

        {{-- Projects --}}
        <section class="mt-12">
            <h2 class="font-display text-xl font-semibold mb-6">Projects <span class="text-[var(--color-muted)] font-normal">專案</span></h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-card>
                    <h3 class="font-display font-semibold">個人網站 Personal Site</h3>
                    <p class="mt-2 text-sm text-[var(--color-muted)]">Laravel + Livewire 打造的簡歷、收藏與單字庫平台。（示範資料）</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="skill-tag skill-dev">Laravel</span>
                        <span class="skill-tag skill-dev">Livewire</span>
                    </div>
                </x-card>
                <x-card>
                    <h3 class="font-display font-semibold">惡意流量偵測 ML Pipeline</h3>
                    <p class="mt-2 text-sm text-[var(--color-muted)]">以機器學習分析網路封包並標記異常行為。（示範資料）</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="skill-tag skill-ml">Python</span>
                        <span class="skill-tag skill-security">Network</span>
                    </div>
                </x-card>
            </div>
        </section>

        {{-- Skills --}}
        <section class="mt-12">
            <h2 class="font-display text-xl font-semibold mb-6">Skills <span class="text-[var(--color-muted)] font-normal">技能</span></h2>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-[var(--color-muted)] mb-2">Security</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="skill-tag skill-security">Penetration Testing</span>
                        <span class="skill-tag skill-security">Reverse Engineering</span>
                        <span class="skill-tag skill-security">Network Security</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-[var(--color-muted)] mb-2">Machine Learning</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="skill-tag skill-ml">PyTorch</span>
                        <span class="skill-tag skill-ml">scikit-learn</span>
                        <span class="skill-tag skill-ml">Pandas</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-[var(--color-muted)] mb-2">Development</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="skill-tag skill-dev">PHP / Laravel</span>
                        <span class="skill-tag skill-dev">JavaScript</span>
                        <span class="skill-tag skill-dev">Git</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Certifications --}}
        <section class="mt-12">
            <h2 class="font-display text-xl font-semibold mb-6">Certifications <span class="text-[var(--color-muted)] font-normal">證照</span></h2>
            <ul class="space-y-2 text-sm">
                <li class="flex items-baseline justify-between gap-2">
                    <span>[Certification Name] — 發證機構</span>
                    <span class="font-mono text-xs text-[var(--color-muted)]">2024</span>
                </li>
                <li class="flex items-baseline justify-between gap-2">
                    <span>[Certification Name] — 發證機構</span>
                    <span class="font-mono text-xs text-[var(--color-muted)]">2023</span>
                </li>
            </ul>
        </section>

    </div>

    <style>
        .timeline-dot {
            position: absolute;
            left: -1.53rem;
            top: 0.4rem;
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 9999px;
            background: var(--color-accent-text);
            box-shadow: 0 0 0 3px var(--color-bg);
        }

        /* Color-coded skill tags (security / ML / dev). */
        .skill-tag {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.15rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1.4;
        }
        .skill-security { background: color-mix(in srgb, #f43f5e 14%, transparent); color: #e11d48; }
        .skill-ml       { background: color-mix(in srgb, #8b5cf6 14%, transparent); color: #7c3aed; }
        .skill-dev      { background: color-mix(in srgb, var(--color-accent) 16%, transparent); color: var(--color-accent-text); }
        :root[data-theme="dark"] .skill-security { color: #fb7185; }
        :root[data-theme="dark"] .skill-ml       { color: #a78bfa; }

        /* Clean printable CV */
        @media print {
            :root { --color-bg: #ffffff; --color-surface: #ffffff; --color-ink: #000000; --color-muted: #333333; --color-line: #cccccc; --color-accent-text: #0369a1; }
            nav, footer, .resume-actions, [data-theme-toggle] { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .resume { max-width: 100%; padding: 0; margin: 0; font-size: 11pt; }
            a { color: #000 !important; text-decoration: none; }
            .timeline-dot { background: #000 !important; box-shadow: none !important; }
            .resume section { break-inside: avoid; page-break-inside: avoid; }
            h1, h2, h3 { break-after: avoid; }
        }
    </style>
</x-layouts.app>
