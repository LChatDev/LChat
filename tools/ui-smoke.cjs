// Run with: node tools/ui-smoke.cjs (jsdom installed in %TEMP%\lchat-dom-test).
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require(path.join(process.env.TEMP, 'lchat-dom-test', 'node_modules', 'jsdom'));

const root = path.dirname(__dirname);
const html = fs.readFileSync(path.join(root, 'index.php'), 'utf8')
  .replace(/^<\?php[^\n]*\?>\s*/u, '')
  .replace(/<script[^>]*><\/script>/gu, '');
const dom = new JSDOM(html, {url:'https://chat.lbs.su/', runScripts:'outside-only', pretendToBeVisual:true});
const {window} = dom;
window.HTMLDialogElement.prototype.showModal = function () {this.open = true;};
window.HTMLDialogElement.prototype.close = function () {this.open = false;};
window.fetch = async (input, options = {}) => {
  const url = new URL(String(input), 'https://chat.lbs.su/');
  let value;
  if (url.pathname === '/api.php' && url.searchParams.get('action') === 'me') value = {user:{id:1,name:'Test',username:'test',is_admin:false}, csrf:'test-csrf', registration_open:true, call_state:{status:'idle'}};
  else if (url.pathname === '/api.php' && url.searchParams.get('action') === 'sync') value = {chats:[],calls:[],active_calls:[],call_state:{status:'idle'}};
  else if (url.pathname === '/bot-manage.php') value = options.method === 'POST' ? {id:2,username:'testbot',token:'2:'+'a'.repeat(48)} : {bots:[],creator_id:3};
  else if (url.pathname === '/settings.php') value = {profile:{id:1,name:'Test',username:'test'},settings:{bio:'',theme:'dark',accent:0,compact:0}};
  else throw new Error('Unexpected request: ' + url);
  return {ok:true, status:200, json:async()=>value};
};
for (const name of ['app.js','settings.js','bots.js','weba-ui.js']) window.eval(fs.readFileSync(path.join(root,'assets',name),'utf8'));
const tick = () => new Promise(resolve => setTimeout(resolve, 0));
(async () => {
  await tick();
  assert.equal(typeof window.LChat.api, 'function');
  const menuButton = window.document.getElementById('main-menu');
  const menu = window.document.getElementById('weba-menu');
  assert.equal(window.document.querySelector('.sidebar-header .search-box')?.contains(window.document.getElementById('chat-filter')), true);
  assert.equal(window.document.querySelector('.sidebar>#new-chat')?.id, 'new-chat');
  assert.equal(menu.querySelector('[data-proxy-target="create-link"]')?.textContent.includes('Call by link'), true);
  menuButton.click();
  assert.equal(menu.hidden, false);
  assert.equal(menuButton.getAttribute('aria-expanded'), 'true');
  assert.equal(menu.querySelector('[data-proxy-target="bots-button"] .weba-icon')?.textContent, String.fromCodePoint(0xf1cc));
  window.document.dispatchEvent(new window.KeyboardEvent('keydown', {key:'Escape', bubbles:true}));
  assert.equal(menu.hidden, true);
  const emoji = window.document.getElementById('emoji-button');
  emoji.click();
  assert.equal(emoji.getAttribute('aria-expanded'), 'true');
  window.document.querySelector('#weba-emoji-panel button').click();
  assert.equal(window.document.getElementById('message-input').value, '😀');
  menuButton.click();
  menu.querySelector('[data-proxy-target="bots-button"]').click();
  await tick();
  assert.equal(menu.hidden, true);
  window.document.getElementById('bots-button').click();
  await tick();
  assert.equal(window.document.getElementById('modal-title').textContent, 'My bots');
  assert.match(window.document.getElementById('modal-body').textContent, /Message @BotCreator/u);
  assert.match(window.document.getElementById('modal-body').textContent, /Bot API documentation/u);
  const form = window.document.querySelector('.bot-create-form');
  form.querySelectorAll('input')[0].value = 'Test bot';
  form.querySelectorAll('input')[1].value = 'testbot';
  form.dispatchEvent(new window.Event('submit', {bubbles:true,cancelable:true}));
  await tick();
  assert.match(window.document.getElementById('modal-title').textContent, /Token/u);
  window.document.getElementById('settings-button').click();
  await tick();
  assert.equal(window.document.getElementById('modal-title').textContent, 'Settings');
  console.log('UI module smoke test OK');
  window.close();
})().catch(error => {console.error(error);process.exitCode = 1;window.close();});
