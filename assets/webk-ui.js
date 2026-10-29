'use strict';
(() => {
  const trigger = document.getElementById('main-menu');
  const menu = document.getElementById('webk-menu');
  const search = document.getElementById('chat-filter');
  if (!trigger || !menu) return;

  menu.querySelectorAll('[data-menu-icon]').forEach(item => {
    const glyph = document.createElement('span');
    glyph.className = 'webk-icon';
    glyph.setAttribute('aria-hidden', 'true');
    glyph.textContent = String.fromCodePoint(parseInt(item.dataset.menuIcon.slice(2), 16));
    item.prepend(glyph);
  });
  function closeMenu(returnFocus = false) {
    if (menu.hidden) return;
    menu.hidden = true;
    trigger.setAttribute('aria-expanded', 'false');
    if (returnFocus) trigger.focus();
  }
  function openMenu() {
    menu.hidden = false;
    trigger.setAttribute('aria-expanded', 'true');
    menu.querySelector('button')?.focus();
  }
  trigger.addEventListener('click', () => menu.hidden ? openMenu() : closeMenu(true));
  menu.addEventListener('click', event => {
    const item = event.target.closest('[data-proxy-target]');
    if (!item) return;
    closeMenu();
    document.getElementById(item.dataset.proxyTarget)?.click();
  });
  document.addEventListener('pointerdown', event => {
    if (!menu.hidden && !menu.contains(event.target) && !trigger.contains(event.target)) closeMenu();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !menu.hidden) { event.preventDefault(); closeMenu(true); return; }
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      if (document.getElementById('messenger')?.hidden) return;
      event.preventDefault();
      closeMenu();
      search?.focus();
      search?.select();
    }
  });
})();
