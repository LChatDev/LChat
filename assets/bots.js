(() => {
  const app = window.LChat;
  if (!app) return;
  const {api, modal, node, button, avatar, toast, report, refreshChats, selectChat} = app;
  const byId = id => document.getElementById(id);
  async function request(action = '', data = null, retry = true) {
    const options = {credentials:'same-origin', cache:'no-store'};
    if (data) {
      const session = await api('me');
      options.method = 'POST';
      options.headers = {'Content-Type':'application/json', 'X-CSRF-Token':session.csrf};
      options.body = JSON.stringify({action, ...data});
    }
    const response = await fetch('bot-manage.php', options);
    const result = await response.json();
    if (!response.ok) {
      if (data && retry && response.status === 403 && result.error === 'Refresh the page and try again.') return request(action, data, false);
      throw new Error(result.error || 'Failed to perform action with bot.');
    }
    return result;
  }
  function inputLabel(title, min, max, placeholder) {
    const label = document.createElement('label'); label.textContent = title;
    const input = document.createElement('input'); input.required = true; input.minLength = min; input.maxLength = max; input.placeholder = placeholder;
    label.append(input); return [label, input];
  }
  function showToken(title, token, ai = false) {
    modal(title, body => {
      body.append(node('p', 'muted', 'Copy the token now. He doesn’t show up later; it can only be replaced.'));
      const input = document.createElement('input'); input.className = 'bot-token'; input.readOnly = true; input.value = token; input.setAttribute('aria-label','Bot token');
      body.append(input, button('Copy token','primary wide',async () => {await navigator.clipboard.writeText(token);toast('Token copied');}));
      body.append(node('p','muted small','Bot API: https://chat.lbs.su/bot<TOKEN>/getMe. Do not transfer the token to anyone.'));
      if (ai) {
        body.append(node('p','muted small','AI-model: Kilo Auto Free. The bot must be run on your PC or server.'));
        body.append(button('Download Python-template','button subtle wide',()=>{window.location.href='examples/kilo-ai-bot.py';}));
      }
      body.append(button('Done','button subtle wide',openBots));
    });
  }
  async function startChat(bot) {
    const created = await api('chat.direct', {user_id:bot.id}, true);
    byId('modal').close();
    await refreshChats();
    const chat = app.getChats().find(item => Number(item.id) === Number(created.id));
    if (chat) await selectChat(chat);
  }
  function addDialog(bot) {
    const owned = app.getChats().filter(chat => ['group','channel'].includes(chat.kind) && chat.role === 'owner');
    modal('Add @' + bot.username, body => {
      if (!owned.length) {body.append(node('p','muted','First create a group or channel of which you will be the owner.'));return;}
      body.append(node('p','muted small','The bot will see new messages after adding.'));
      const form = document.createElement('form'); form.className = 'bot-add-form';
      const select = document.createElement('select'); select.required = true;
      for (const chat of owned) {const option = document.createElement('option');option.value = chat.id;option.textContent = (chat.kind === 'channel' ? 'Channel · ' : 'Group · ') + chat.title;select.append(option);}
      const submit = document.createElement('button');submit.type = 'submit';submit.className = 'primary wide';submit.textContent = 'Add a bot';
      form.append(select, submit);
      form.onsubmit = async event => {event.preventDefault();submit.disabled=true;try{await request('add',{bot_id:bot.id,chat_id:Number(select.value)});toast('Bot added');await refreshChats();await openBots();}catch(error){report(error);}finally{submit.disabled=false;}};
      body.append(form);
    });
  }
  async function openBots() {
    const data = await request();
    modal('My bots', body => {
      body.append(node('p','muted small','Create a bot and get API-token via @BotCreator or the form below. Bots receive messages in personal chat and groups.'));
      if (data.aibot_id) body.append(button('✨ Open @AiBot','primary wide',async()=>{await startChat({id:data.aibot_id});const input=byId('message-input');if(input){input.value='/start';input.focus();}}));
      if (data.creator_id) body.append(button('Write @BotCreator','primary wide',async()=>{await startChat({id:data.creator_id});const input=byId('message-input');if(input){input.value='/start';input.focus();}}));
      body.append(button('Documentation Bot API','button subtle wide',()=>{window.location.href='docs.php';}));
      for (const bot of data.bots) {
        const card = node('div','bot-card');
        const heading = node('div','bot-card-heading');heading.append(avatar(bot.name,bot.id),node('div','',bot.name+' · @'+bot.username));card.append(heading);
        const actions = node('div','bot-card-actions');
        actions.append(button('Open chat','button subtle',()=>startChat(bot)),button('Add to group','button subtle',()=>addDialog(bot)),button('New token','button subtle',async()=>{if(!confirm('The old token will immediately stop working. Replace it?'))return;const changed=await request('rotate',{bot_id:bot.id});showToken('New token @'+bot.username,changed.token);}));
        card.append(actions);body.append(card);
      }
      if (!data.bots.length) body.append(node('p','muted','You haven’t created any bots yet.'));
      const form = document.createElement('form');form.className='bot-create-form';
      const [nameLabel,name] = inputLabel('Bot name',2,60,'For example, Assistant');
      const [userLabel,username] = inputLabel('Bot login',6,32,'For example, helperbot');
      const submit = document.createElement('button');submit.type='submit';submit.className='primary wide';submit.textContent='Create a bot';
      const aiSubmit = document.createElement('button');aiSubmit.type='submit';aiSubmit.className='button subtle wide';aiSubmit.textContent='✨ Create an AI bot';
      form.append(nameLabel,userLabel,node('p','muted small','Login must end with bot. AI-You host the bot yourself.'),submit,aiSubmit);
      form.onsubmit=async event=>{event.preventDefault();const ai=event.submitter===aiSubmit;submit.disabled=true;aiSubmit.disabled=true;try{const created=await request(ai?'create-ai':'create',{name:name.value,username:username.value});showToken('Token @'+created.username,created.token,ai);}catch(error){report(error);}finally{submit.disabled=false;aiSubmit.disabled=false;}};
      body.append(form);
    });
  }
  byId('bots-button')?.addEventListener('click',()=>openBots().catch(report));
})();
