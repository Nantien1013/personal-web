<script setup lang="ts">
useSeoMeta({
  title: '個人簡歷',
  description: '學歷、經歷、研究專案與技能',
})

let currentLang = ref<'zh' | 'en'>('zh')
const toggleLang = () => {
  currentLang.value = currentLang.value === 'zh' ? 'en' : 'zh'
}
</script>

<template>
  <div class="container">
    <div class="resume-layout">
      <!-- Sidebar -->
      <aside class="resume-sidebar">
        <div class="resume-avatar">
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--color-text-faint);">👤</div>
        </div>
        <h1 class="resume-name">{{ currentLang === 'zh' ? '你的名字' : 'Your Name' }}</h1>
        <p class="resume-title">{{ currentLang === 'zh' ? '資安研究 / 機器學習工程師' : 'Security Researcher / ML Engineer' }}</p>

        <ul class="resume-contact-list">
          <li class="resume-contact-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,12 2,6"/></svg>
            <a href="mailto:your@email.com">your@email.com</a>
          </li>
          <li class="resume-contact-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            <a href="https://github.com/yourusername" target="_blank" rel="noopener">github.com/yourusername</a>
          </li>
        </ul>

        <button class="btn btn-ghost btn-sm" @click="toggleLang" style="margin-bottom: 1rem;">
          {{ currentLang === 'zh' ? 'EN' : '中' }}
        </button>

        <a href="#" class="btn btn-primary" style="width:100%;justify-content:center;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          {{ currentLang === 'zh' ? '下載 CV' : 'Download CV' }}
        </a>

        <hr class="divider">

        <div class="resume-section">
          <p class="resume-section-title">{{ currentLang === 'zh' ? '技能' : 'Skills' }}</p>
          <div class="skills-grid">
            <div>
              <p class="skill-group-title">{{ currentLang === 'zh' ? '資安' : 'Security' }}</p>
              <div class="skill-tags">
                <span class="tag tag-red">Penetration Testing</span>
                <span class="tag tag-red">CTF</span>
                <span class="tag tag-red">Reverse Engineering</span>
              </div>
            </div>
            <div>
              <p class="skill-group-title">{{ currentLang === 'zh' ? '機器學習' : 'Machine Learning' }}</p>
              <div class="skill-tags">
                <span class="tag tag-primary">Python</span>
                <span class="tag tag-primary">PyTorch</span>
                <span class="tag tag-primary">NLP</span>
              </div>
            </div>
            <div>
              <p class="skill-group-title">{{ currentLang === 'zh' ? '開發' : 'Development' }}</p>
              <div class="skill-tags">
                <span class="tag tag-green">JavaScript</span>
                <span class="tag tag-green">Node.js</span>
                <span class="tag tag-green">SQL</span>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main content -->
      <div class="resume-main">
        <section class="resume-content-section">
          <h2 class="resume-content-title">{{ currentLang === 'zh' ? '學歷' : 'Education' }}</h2>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-header">
                <span class="timeline-title">{{ currentLang === 'zh' ? '國立某某大學' : 'National XX University' }}</span>
                <span class="timeline-period">2022 – 2024</span>
              </div>
              <p class="timeline-subtitle">{{ currentLang === 'zh' ? '資訊工程學系 碩士' : 'M.S. in Computer Science' }}</p>
              <p class="timeline-desc">{{ currentLang === 'zh' ? '研究方向：網路安全與機器學習之結合應用，論文主題為使用深度學習進行惡意流量分類。' : 'Research focus: intersection of network security and machine learning. Thesis: malicious traffic classification using deep learning.' }}</p>
            </div>
            <div class="timeline-item">
              <div class="timeline-header">
                <span class="timeline-title">{{ currentLang === 'zh' ? '國立某某大學' : 'National XX University' }}</span>
                <span class="timeline-period">2018 – 2022</span>
              </div>
              <p class="timeline-subtitle">{{ currentLang === 'zh' ? '資訊工程學系 學士' : 'B.S. in Computer Science' }}</p>
              <p class="timeline-desc">{{ currentLang === 'zh' ? '主修資安與演算法，GPA 3.9/4.0，畢業專題獲系上優秀論文獎。' : 'Major in security and algorithms. GPA 3.9/4.0. Outstanding thesis award.' }}</p>
            </div>
          </div>
        </section>

        <section class="resume-content-section">
          <h2 class="resume-content-title">{{ currentLang === 'zh' ? '經歷' : 'Experience' }}</h2>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-header">
                <span class="timeline-title">{{ currentLang === 'zh' ? '資安研究實習生' : 'Security Research Intern' }}</span>
                <span class="timeline-period">2023.07 – 2023.12</span>
              </div>
              <p class="timeline-subtitle">XX Security Lab</p>
              <p class="timeline-desc">{{ currentLang === 'zh' ? '負責惡意程式靜態分析與行為分類，撰寫自動化分析工具，協助完成 3 篇研究報告。' : 'Static malware analysis and behavior classification. Developed automation tools. Co-authored 3 research reports.' }}</p>
            </div>
            <div class="timeline-item">
              <div class="timeline-header">
                <span class="timeline-title">{{ currentLang === 'zh' ? '助教' : 'Teaching Assistant' }}</span>
                <span class="timeline-period">2022.09 – 2023.06</span>
              </div>
              <p class="timeline-subtitle">{{ currentLang === 'zh' ? '計算機網路安全 課程' : 'Computer Network Security Course' }}</p>
              <p class="timeline-desc">{{ currentLang === 'zh' ? '協助設計 CTF 競賽題目、批改作業、主持實驗課，帶領 60 名學生學習網路滲透測試技術。' : 'Designed CTF challenges, graded assignments, led lab sessions for 60 students on penetration testing.' }}</p>
            </div>
          </div>
        </section>

        <section class="resume-content-section">
          <h2 class="resume-content-title">{{ currentLang === 'zh' ? '研究與專案' : 'Research & Projects' }}</h2>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-header">
                <span class="timeline-title">{{ currentLang === 'zh' ? '惡意流量深度學習分類器' : 'Deep Learning Malicious Traffic Classifier' }}</span>
                <span class="timeline-period">2023 – 2024</span>
              </div>
              <p class="timeline-subtitle">{{ currentLang === 'zh' ? '碩士論文研究' : "Master's Thesis" }}</p>
              <p class="timeline-desc">{{ currentLang === 'zh' ? '使用 Transformer 架構對網路封包進行多分類，達到 97.3% 準確率，並在公開資料集上超越現有 baseline。' : 'Applied Transformer architecture for multi-class network packet classification. Achieved 97.3% accuracy, surpassing existing baselines on public datasets.' }}</p>
              <div style="margin-top: 0.5rem; display:flex; gap: 0.5rem; flex-wrap:wrap;">
                <span class="tag tag-primary">Python</span>
                <span class="tag tag-primary">PyTorch</span>
                <span class="tag tag-red">Network Security</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-header">
                <span class="timeline-title">Personal Website</span>
                <span class="timeline-period">2026</span>
              </div>
              <p class="timeline-subtitle">{{ currentLang === 'zh' ? '個人專案' : 'Personal Project' }}</p>
              <p class="timeline-desc">{{ currentLang === 'zh' ? '設計並開發全端個人網站，包含動漫收藏管理、論文單字庫與抽卡複習系統。' : 'Designed and built a full-stack personal website with anime collection management and an academic vocabulary flashcard system.' }}</p>
              <div style="margin-top: 0.5rem; display:flex; gap: 0.5rem; flex-wrap:wrap;">
                <span class="tag tag-green">Nuxt3</span>
                <span class="tag tag-green">Laravel</span>
                <span class="tag tag-green">MySQL</span>
              </div>
            </div>
          </div>
        </section>

        <section class="resume-content-section">
          <h2 class="resume-content-title">{{ currentLang === 'zh' ? '證照' : 'Certifications' }}</h2>
          <div style="display:flex; flex-direction:column; gap: var(--space-3);">
            <div class="card" style="padding: var(--space-4);">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <p style="font-weight:var(--weight-semibold);">CompTIA Security+</p>
                  <p class="text-muted text-sm">CompTIA</p>
                </div>
                <span class="tag tag-green">2023</span>
              </div>
            </div>
            <div class="card" style="padding: var(--space-4);">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <p style="font-weight:var(--weight-semibold);">IELTS Academic</p>
                  <p class="text-muted text-sm">{{ currentLang === 'zh' ? '總分 7.0' : 'Overall Band 7.0' }}</p>
                </div>
                <span class="tag tag-primary">2022</span>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>
