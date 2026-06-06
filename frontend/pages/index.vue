<script setup lang="ts">
useSeoMeta({
  title: '個人網站',
  description: '個人入口網站 — 收藏、學習、作品集',
})
</script>

<template>
  <div>
    <section class="hero">
      <div class="container">
        <div class="hero-inner">
          <div class="hero-text">
            <div class="hero-badge">
              <span class="hero-badge-dot"></span>
              歡迎來到我的個人網站
            </div>
            <h1 class="hero-title">
              Hi, 我是<br>
              <span class="accent">Your Name</span>
            </h1>
            <p class="hero-subtitle">
              資安研究 × 機器學習 × 軟體開發<br>
              熱愛探索技術的每個角落，也喜歡收藏動漫與漫畫。
            </p>
            <div class="hero-actions">
              <NuxtLink to="/resume" class="btn btn-primary btn-lg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                查看簡歷
              </NuxtLink>
              <NuxtLink to="/collection" class="btn btn-ghost btn-lg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                收藏清單
              </NuxtLink>
            </div>
            <div class="hero-social">
              <a href="https://github.com/yourusername" target="_blank" rel="noopener noreferrer" class="social-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                GitHub
              </a>
              <a href="mailto:your@email.com" class="social-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
                Email
              </a>
            </div>
          </div>
          <div class="hero-avatar">
            <div class="hero-avatar-placeholder">👤</div>
          </div>
        </div>
      </div>
    </section>

    <section class="nav-section">
      <div class="container">
        <h2 class="section-heading">探索我的網站</h2>
        <p class="section-subheading">各個頁面的功能簡介</p>
        <div class="nav-cards">
          <NuxtLink to="/resume" class="nav-card">
            <div class="nav-card-icon">📄</div>
            <h3 class="nav-card-title">個人簡歷</h3>
            <p class="nav-card-desc">學歷、經歷、研究專案與技能，支援下載 PDF 版本。</p>
          </NuxtLink>
          <NuxtLink to="/collection" class="nav-card">
            <div class="nav-card-icon">🎌</div>
            <h3 class="nav-card-title">動漫 / 漫畫收藏</h3>
            <p class="nav-card-desc">個人觀看清單，支援搜尋、篩選、評分與心得紀錄。</p>
          </NuxtLink>
          <NuxtLink to="/vocabulary" class="nav-card">
            <div class="nav-card-icon">📚</div>
            <h3 class="nav-card-title">論文單字庫</h3>
            <p class="nav-card-desc">輸入英文單字自動查詢中譯，搭配抽卡系統複習記憶。</p>
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>
