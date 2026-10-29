<?php
declare(strict_types=1);
define('LCHAT_INTERNAL', true);

$testDb = new PDO('sqlite::memory:');
$testDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$testDb->exec('PRAGMA foreign_keys = ON');
$testDb->exec('CREATE TABLE chats (id INTEGER PRIMARY KEY)');
$testDb->exec('CREATE TABLE users (id INTEGER PRIMARY KEY)');
$testDb->exec('CREATE TABLE messages (id INTEGER PRIMARY KEY, chat_id INTEGER NOT NULL, user_id INTEGER NOT NULL, FOREIGN KEY(chat_id) REFERENCES chats(id), FOREIGN KEY(user_id) REFERENCES users(id))');
$testDb->exec('CREATE TABLE members (chat_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(chat_id,user_id))');
$testDb->exec('CREATE TABLE bot_message_markup (message_id INTEGER PRIMARY KEY, markup_json TEXT NOT NULL, FOREIGN KEY(message_id) REFERENCES messages(id) ON DELETE CASCADE)');
$testDb->exec('CREATE TABLE bot_chat_keyboards (chat_id INTEGER PRIMARY KEY, bot_user_id INTEGER NOT NULL, message_id INTEGER NOT NULL, markup_json TEXT NOT NULL, FOREIGN KEY(chat_id) REFERENCES chats(id) ON DELETE CASCADE, FOREIGN KEY(bot_user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(message_id) REFERENCES messages(id) ON DELETE CASCADE)');
$testDb->exec('CREATE TABLE bot_keyboard_dismissals (chat_id INTEGER NOT NULL, user_id INTEGER NOT NULL, message_id INTEGER NOT NULL, PRIMARY KEY(chat_id,user_id), FOREIGN KEY(chat_id) REFERENCES chats(id) ON DELETE CASCADE, FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(message_id) REFERENCES messages(id) ON DELETE CASCADE)');
$testDb->exec('INSERT INTO chats VALUES (1)');
$testDb->exec('INSERT INTO users VALUES (1),(2)');
$testDb->exec('INSERT INTO members VALUES (1,1),(1,2)');
$testDb->exec('INSERT INTO messages VALUES (10,1,2),(11,1,2),(12,1,2),(13,1,2)');

function db(): PDO { global $testDb; return $testDb; }
function query(string $sql, array $params = []): PDOStatement { $statement = db()->prepare($sql); $statement->execute($params); return $statement; }
function one(string $sql, array $params = []): ?array { return query($sql, $params)->fetch(PDO::FETCH_ASSOC) ?: null; }
function stamp(int $offset = 0): string { return gmdate('Y-m-d H:i:s', time() + $offset); }
function transaction(callable $action): mixed { db()->beginTransaction(); try { $value = $action(); db()->commit(); return $value; } catch (Throwable $error) { db()->rollBack(); throw $error; } }
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }

require dirname(__DIR__) . '/apis/bot_platform.php';
$reply = bot_platform_markup(['keyboard' => [[['text' => 'Help'], ['text' => 'Profile']]], 'resize_keyboard' => true, 'one_time_keyboard' => true]);
bot_platform_save_markup(10, $reply);
$state = bot_platform_keyboard(1, 1);
check($state !== null && $state['markup']['keyboard'][0][0]['text'] === 'Help' && !$state['dismissed'], 'Reply keyboard was not saved.');
bot_platform_dismiss_keyboard(1, 1);
check(bot_platform_keyboard(1, 1)['dismissed'], 'One-time keyboard dismissal was not saved.');
check(!bot_platform_keyboard(1, 2)['dismissed'], 'Dismissal leaked to another user.');
bot_platform_save_markup(11, $reply);
check(!bot_platform_keyboard(1, 1)['dismissed'], 'New keyboard inherited old dismissal.');
bot_platform_save_markup(12, bot_platform_markup(['remove_keyboard' => true]));
check(bot_platform_keyboard(1, 1) === null, 'remove_keyboard did not clear chat state.');
$inline = bot_platform_markup(['inline_keyboard' => [[['text' => 'Done', 'callback_data' => 'done']]]]);
bot_platform_save_markup(13, $inline);
check(bot_platform_keyboard(1, 1) === null && one('SELECT markup_json FROM bot_message_markup WHERE message_id = 13') !== null, 'Inline keyboard state is incorrect.');
echo "Bot keyboard SQLite smoke test OK\n";
