<script setup lang="ts">
const isDark = ref(true)

onMounted(() => {
  const saved = localStorage.getItem('theme') || 'dark'
  isDark.value = saved === 'dark'
  document.documentElement.dataset.theme = saved
})

const toggleTheme = () => {
  isDark.value = !isDark.value
  const theme = isDark.value ? 'dark' : 'light'
  document.documentElement.dataset.theme = theme
  localStorage.setItem('theme', theme)
}
</script>

<template>
  <div>
    <nav class="navbar">
      <div class="navbar-inner">
        <NuxtLink to="/" class="navbar-logo">dev<span>.</span></NuxtLink>
        <div class="navbar-links">
          <NuxtLink to="/">首頁</NuxtLink>
          <NuxtLink to="/resume">簡歷</NuxtLink>
          <NuxtLink to="/collection">收藏</NuxtLink>
          <NuxtLink to="/vocabulary">單字庫</NuxtLink>
        </div>
        <div class="navbar-actions">
          <button class="theme-toggle" @click="toggleTheme" title="切換主題">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path v-if="isDark" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
              <g v-else>
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
              </g>
            </svg>
          </button>
        </div>
      </div>
    </nav>
    <main class="page-wrapper">
      <NuxtPage />
    </main>
    <footer>
      <div class="container">
        <p>Built with ❤️ &nbsp;·&nbsp; Nuxt3 + Laravel + MySQL</p>
      </div>
    </footer>
  </div>
</template>
