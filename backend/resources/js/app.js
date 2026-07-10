// Apply saved theme before paint (also duplicated inline in layout head to avoid FOUC).
const saved = localStorage.getItem('theme') || 'light';
document.documentElement.dataset.theme = saved;
window.toggleTheme = () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('theme', next);
};
