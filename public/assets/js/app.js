document.addEventListener('DOMContentLoaded', function(){
  const btns = document.querySelectorAll('[data-theme-toggle]');
  const saved = localStorage.getItem('grg_theme') || 'dark';
  document.body.setAttribute('data-theme', saved);
  btns.forEach(btn => btn.addEventListener('click', () => {
    const next = (document.body.getAttribute('data-theme') || 'dark') === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', next);
    localStorage.setItem('grg_theme', next);
  }));
});
