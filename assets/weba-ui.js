'use strict';
(() => {
  const trigger = document.getElementById('main-menu');
  const menu = document.getElementById('weba-menu');
  const search = document.getElementById('chat-filter');
  if (!trigger || !menu) return;

  // Telegram Web A keeps search beside the menu and the compose action floating.
  const sidebar = document.querySelector('.sidebar');
  const searchBox = document.querySelector('.sidebar .search-box');
  const newChat = document.getElementById('new-chat');
  if (searchBox) trigger.after(searchBox);
  if (newChat && sidebar) {
    sidebar.append(newChat);
    const composeGlyph = newChat.querySelector('.weba-icon');
    if (composeGlyph) composeGlyph.textContent = String.fromCodePoint(0xf17d);
  }
  const callLink = document.createElement('button');
  callLink.type = 'button';
  callLink.dataset.proxyTarget = 'create-link';
  callLink.dataset.menuIcon = '\\uf1b9';
  callLink.textContent = document.documentElement.lang === 'ru' ? 'Звонок по ссылке' : 'Call by link';
  menu.querySelector('a')?.before(callLink);

  menu.querySelectorAll('[data-menu-icon]').forEach(item => {
    const glyph = document.createElement('span');
    glyph.className = 'weba-icon';
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

  const composer = document.getElementById('composer');
  const input = document.getElementById('message-input');
  let emojiPanel;
  if (composer && input) {
    const emojiButton = document.createElement('button');
    emojiButton.id = 'emoji-button';
    emojiButton.type = 'button';
    emojiButton.className = 'icon-button';
    emojiButton.title = document.documentElement.lang === 'ru' ? 'Эмодзи' : 'Emoji';
    emojiButton.setAttribute('aria-label', emojiButton.title);
    emojiButton.setAttribute('aria-expanded', 'false');
    const glyph = document.createElement('span');
    glyph.className = 'weba-icon';
    glyph.setAttribute('aria-hidden', 'true');
    glyph.textContent = String.fromCodePoint(0xf248);
    emojiButton.append(glyph);
    composer.prepend(emojiButton);
    emojiPanel = document.createElement('div');
    emojiPanel.id = 'weba-emoji-panel';
    emojiPanel.className = 'weba-emoji-panel';
    emojiPanel.hidden = true;
    emojiPanel.setAttribute('aria-label', emojiButton.title);
    for (const emoji of ['😀','😊','😂','❤️','👍','🔥','🎉','😎','🥰','🙏','✨','👋','💬','✅','😢','😡','🤔','🙌','🎵','💯']) {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = emoji;
      button.setAttribute('aria-label', emoji);
      button.addEventListener('click', () => {
        input.setRangeText(emoji, input.selectionStart, input.selectionEnd, 'end');
        input.dispatchEvent(new Event('input', {bubbles: true}));
        input.focus();
        emojiPanel.hidden = true;
        emojiButton.setAttribute('aria-expanded', 'false');
      });
      emojiPanel.append(button);
    }
    composer.before(emojiPanel);
    const closeEmoji = () => {
      emojiPanel.hidden = true;
      emojiButton.setAttribute('aria-expanded', 'false');
    };
    emojiButton.addEventListener('click', () => {
      emojiPanel.hidden = !emojiPanel.hidden;
      emojiButton.setAttribute('aria-expanded', String(!emojiPanel.hidden));
    });
    document.addEventListener('pointerdown', event => {
      if (!emojiPanel.hidden && !emojiPanel.contains(event.target) && !emojiButton.contains(event.target)) {
        closeEmoji();
      }
    });
    new MutationObserver(() => { if (composer.hidden) closeEmoji(); })
      .observe(composer, {attributes: true, attributeFilter: ['hidden']});
    document.querySelector('.chat-list')?.addEventListener('click', closeEmoji);
    document.getElementById('mobile-back')?.addEventListener('click', closeEmoji);
  }
  document.addEventListener('pointerdown', event => {
    if (!menu.hidden && !menu.contains(event.target) && !trigger.contains(event.target)) closeMenu();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !menu.hidden) { event.preventDefault(); closeMenu(true); return; }
    if (event.key === 'Escape' && emojiPanel && !emojiPanel.hidden) { emojiPanel.hidden = true; document.getElementById('emoji-button').setAttribute('aria-expanded', 'false'); return; }
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      if (document.getElementById('messenger')?.hidden) return;
      event.preventDefault();
      closeMenu();
      search?.focus();
      search?.select();
    }
  });
})();
