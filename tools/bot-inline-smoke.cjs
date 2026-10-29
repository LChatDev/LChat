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
const botChat = {id:10,kind:'direct',title:'Test bot',role:'member',peer:{id:2,username:'testbot',last_seen:'2026-10-26 12:00:00'}};
let sent = null;
let keyboardDismissed = false;
window.fetch = async (input, options = {}) => {
  const url = new URL(String(input), 'https://chat.lbs.su/');
  const action = url.searchParams.get('action');
  const value = action === 'me'
    ? {user:{id:1,name:'Test',username:'test'},csrf:'csrf',registration_open:true,call_state:{status:'idle'}}
    : action === 'sync'
      ? {chats:[botChat],calls:[],active_calls:[],call_state:{status:'idle'},...(url.searchParams.has('chat_id')?{messages:[],members:[],edited_messages:[],removed_ids:[],reaction_state:[],edit_cursor:0,bot_keyboard:{message_id:20,dismissed:keyboardDismissed,markup:{keyboard:[[{text:'Help'},{text:'Profile'}]],one_time_keyboard:true,input_field_placeholder:'Choose'}}}:{})}
      : action === 'message.send'
        ? (sent = JSON.parse(options.body), keyboardDismissed = true, {id:30})
      : {profile:{id:1,name:'Test',username:'test'},settings:{theme:'dark'}};
  return {ok:true,status:200,json:async()=>value};
};
const source = fs.readFileSync(path.join(root, 'assets', 'app.js'), 'utf8')
  .replace('window.LChat = {api,', 'window.LChat = {renderMessage, api,');
window.eval(source);
setTimeout(async () => {
  try {
    const row = window.LChat.renderMessage({id:9,user_id:1,name:'Test',body:'Choose',created_at:'2026-10-26 12:00:00',reply_markup:{inline_keyboard:[[
      {text:'Done',callback_data:'done'},
      {text:'Website',url:'https://example.com'}
    ]]}});
    assert.equal(row.querySelectorAll('.bot-inline-button').length, 2);
    assert.equal(row.querySelector('button.bot-inline-button').textContent, 'Done');
    assert.equal(row.querySelector('a.bot-inline-button').getAttribute('href'), 'https://example.com');
    assert.equal(row.querySelector('a.bot-inline-button').getAttribute('rel'), 'noopener noreferrer');
    await window.LChat.selectChat(botChat);
    const panel = window.document.getElementById('bot-reply-keyboard');
    assert.equal(panel.hidden, false);
    assert.equal(panel.querySelectorAll('.bot-reply-button').length, 2);
    assert.equal(window.document.getElementById('message-input').placeholder, 'Choose');
    panel.querySelector('.bot-reply-button').click();
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(sent.body, 'Help');
    assert.equal(sent.chat_id, 10);
    assert.equal(panel.hidden, true);
    assert.equal(window.document.getElementById('bot-keyboard-toggle').hidden, false);
    await window.LChat.selectChat(botChat);
    assert.equal(panel.hidden, true);
    window.document.getElementById('bot-keyboard-toggle').click();
    assert.equal(panel.hidden, false);
    console.log('Bot inline keyboard smoke test OK');
  } catch (error) {
    console.error(error);
    process.exitCode = 1;
  } finally {
    window.close();
  }
}, 0);
