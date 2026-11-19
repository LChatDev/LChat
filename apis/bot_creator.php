<?php
declare(strict_types=1);
if (!defined('LCHAT_INTERNAL')) { http_response_code(404); exit; }

function bot_creator_install(): void {
    if (one('SELECT user_id FROM system_bots WHERE code = ?', ['botcreator'])) return;
    $occupied = one('SELECT id FROM users WHERE username = ?', ['BotCreator']);
    if ($occupied) {
        error_log('LChat BotCreator: username BotCreator is occupied; reserve or rename it before provisioning.');
        return;
    }
    try {
        transaction(static function (): void {
            query('INSERT INTO users (username, email, name, password_hash, created_at, last_seen) VALUES (?, ?, ?, ?, ?, ?)', ['BotCreator', 'system-botcreator@lchat.invalid', 'BotCreator', password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT), stamp(), stamp()]);
            query('INSERT INTO system_bots (user_id, code) VALUES (?, ?)', [(int)db()->lastInsertId(), 'botcreator']);
        });
    } catch (PDOException $e) {
        if (!str_starts_with((string)$e->getCode(), '23')) throw $e;
        if (!one('SELECT user_id FROM system_bots WHERE code = ?', ['botcreator'])) error_log('LChat BotCreator: account creation collided with another user.');
    }
}

function bot_creator_id(): int {
    return (int)(one('SELECT user_id FROM system_bots WHERE code = ?', ['botcreator'])['user_id'] ?? 0);
}

function bot_creator_state(int $uid, string $stage = '', string $draftName = ''): void {
    query('DELETE FROM bot_creator_sessions WHERE user_id = ?', [$uid]);
    if ($stage !== '') query('INSERT INTO bot_creator_sessions (user_id, stage, draft_name, updated_at) VALUES (?, ?, ?, ?)', [$uid, $stage, $draftName, stamp()]);
}

function bot_creator_help(): string {
    return "Hello! I @BotCreator. I’ll create a bot LChat and will give it to him API-token.\n\n/newbot — create a regular bot\n/newaibot — create a bot with Kilo AI\n/mybots — list of your bots\n/token @namebot - replace API-token\n/setname @bot name New name - change name\n/cancel — cancel creation\n/help — this hint\n\nAI-The bot is launched and hosted by its creator. Documentation: https://chat.lbs.su/docs.php";
}

function bot_creator_answer(int $uid, string $body): string {
    $body = trim($body);
    $command = mb_strtolower($body, 'UTF-8');
    if (preg_match('~^/(?:start|help)(?:@botcreator)?$~iD', $body)) return bot_creator_help();
    if (preg_match('~^/cancel(?:@botcreator)?$~iD', $body)) {
        bot_creator_state($uid);
        return 'Creation cancelled. Write /newbot, to start again.';
    }
    if (preg_match('~^/newbot(?:@botcreator)?$~iD', $body)) {
        bot_creator_state($uid, 'name');
        return 'What to name the new bot? Send his display name (2-60 characters).';
    }
    if (preg_match('~^/newaibot(?:@botcreator)?$~iD', $body)) {
        bot_creator_state($uid, 'ai_name');
        return "Create a bot with Kilo Auto Free. What to call it?\n\nAfter creation, I will issue a token and ready Python-template. You need to run the bot on your PC or server.";
    }
    if (preg_match('~^/mybots(?:@botcreator)?$~iD', $body)) {
        $bots = query('SELECT u.username, u.name FROM bot_accounts b JOIN users u ON u.id = b.bot_user_id WHERE b.owner_user_id = ? ORDER BY b.bot_user_id DESC', [$uid])->fetchAll();
        if (!$bots) return 'You don’t have bots yet. Write /newbot.';
        $lines = array_map(static fn(array $bot): string => $bot['name'] . ' · @' . $bot['username'], $bots);
        return "Your bots:\n" . implode("\n", $lines) . "\n\nNew token: /token @namebot";
    }
    if (preg_match('~^/(?:token|newtoken)(?:@botcreator)?\s+@?([a-z][a-z0-9_]{2,31})$~iD', $body, $match)) {
        $bot = one('SELECT b.bot_user_id, u.username FROM bot_accounts b JOIN users u ON u.id = b.bot_user_id WHERE b.owner_user_id = ? AND u.username = ?', [$uid, strtolower($match[1])]);
        if (!$bot) return 'Your bot was not found. Look /mybots.';
        $token = bot_account_rotate($uid, (int)$bot['bot_user_id']);
        return "New API-token @{$bot['username']}:\n{$token}\n\nThe old token has already been disabled. Keep the new one as a password and do not publish it.";
    }
    if (preg_match('~^/setname(?:@botcreator)?\s+@?([a-z][a-z0-9_]{2,31})\s+(.+)$~isD', $body, $match)) {
        $name = trim($match[2]);
        if (mb_strlen($name, 'UTF-8') < 2 || mb_strlen($name, 'UTF-8') > 60) return 'New name: from 2 to 60 characters.';
        $bot = one('SELECT b.bot_user_id FROM bot_accounts b JOIN users u ON u.id = b.bot_user_id WHERE b.owner_user_id = ? AND u.username = ?', [$uid, strtolower($match[1])]);
        if (!$bot) return 'Your bot was not found. Look /mybots.';
        query('UPDATE users SET name = ? WHERE id = ?', [$name, $bot['bot_user_id']]);
        return 'Bot name @' . strtolower($match[1]) . ' changed to «' . $name . '».';
    }
    $state = one('SELECT stage, draft_name FROM bot_creator_sessions WHERE user_id = ?', [$uid]);
    if ($state && str_starts_with($body, '/')) return 'A bot is currently being created. Submit the requested value or /cancel.';
    if (($state['stage'] ?? '') === 'name') {
        if (mb_strlen($body, 'UTF-8') < 2 || mb_strlen($body, 'UTF-8') > 60) return 'The name must contain from 2 to 60 characters. Try again.';
        bot_creator_state($uid, 'username', $body);
        return 'Now come up with a login ending with bot. For example, helperbot. Latin letters, numbers and _.';
    }
    if (($state['stage'] ?? '') === 'ai_name') {
        if (mb_strlen($body, 'UTF-8') < 2 || mb_strlen($body, 'UTF-8') > 60) return 'The name must contain from 2 to 60 characters. Try again.';
        bot_creator_state($uid, 'ai_username', $body);
        return 'Create a login AI-bot ending with bot. For example, smart_helper_bot.';
    }
    if (($state['stage'] ?? '') === 'username') {
        try { $bot = bot_account_create($uid, $state['draft_name'], $body); }
        catch (DomainException $e) { return $e->getMessage() . ' Try a different login or /cancel.'; }
        bot_creator_state($uid);
        return "Bot @{$bot['username']} created. API-token:\n{$bot['token']}\n\nCopy it now. Use only on your server; documentation: https://chat.lbs.su/docs.php";
    }
    if (($state['stage'] ?? '') === 'ai_username') {
        try { $bot = bot_account_create($uid, $state['draft_name'], $body); }
        catch (DomainException $e) { return $e->getMessage() . ' Try a different login or /cancel.'; }
        bot_creator_state($uid);
        return "✅ AI-bot @{$bot['username']} created.\n\nAPI-token:\n{$bot['token']}\n\nModel: kilo-auto/free\nTemplate: https://chat.lbs.su/examples/kilo-ai-bot.py\n\nInstall Python and requests, set variable LCHAT_BOT_TOKEN and run the template on your PC or server. LChat does not host user AI-bots. Don’t publish the token.";
    }
    if ($command === '') return bot_creator_help();
    return 'Unknown command. Write /help, /newbot or /newaibot.';
}

function bot_creator_process(int $chatId, int $senderId, int $messageId, string $body): void {
    $creatorId = bot_creator_id();
    if (!$creatorId || $senderId === $creatorId) return;
    $chat = one('SELECT c.kind FROM chats c JOIN members m ON m.chat_id = c.id AND m.user_id = ? WHERE c.id = ?', [$creatorId, $chatId]);
    if (($chat['kind'] ?? '') !== 'direct') return;
    if (one('SELECT bot_user_id FROM bot_accounts WHERE bot_user_id = ?', [$senderId])) return;
    transaction(static function () use ($chatId, $senderId, $messageId, $body, $creatorId): void {
        query('UPDATE chats SET title = title WHERE id = ?', [$chatId]);
        if (one('SELECT message_id FROM bot_creator_handled WHERE message_id = ?', [$messageId])) return;
        $answer = bot_creator_answer($senderId, $body);
        query('INSERT INTO messages (chat_id, user_id, body, nonce, created_at) VALUES (?, ?, ?, ?, ?)', [$chatId, $creatorId, message_store($answer), 'botcreator_' . bin2hex(random_bytes(20)), stamp()]);
        query('INSERT INTO bot_creator_handled (message_id) VALUES (?)', [$messageId]);
    });
}
