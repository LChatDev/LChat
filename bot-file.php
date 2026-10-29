<?php
declare(strict_types=1);
define('LCHAT_INTERNAL', true);
require __DIR__ . '/app.php';
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
try {
    ensure_features();
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'HEAD'], true)) fail(405, 'Use GET.');
    $token = bot_request_token();
    if ($token === '') fail(401, 'No bot token was received. Send Authorization: Bearer or X-LChat-Bot-Token.');
    if (!preg_match('/^([0-9]+):[a-f0-9]{48}$/D', $token, $match)) fail(401, 'Invalid bot token.');
    $botId = (int)$match[1];
    $bot = one('SELECT owner_user_id FROM bot_accounts WHERE bot_user_id = ? AND token_hash = ?', [$botId, hash('sha256', $token)]);
    if (!$bot || service_ban($botId) || service_ban((int)$bot['owner_user_id'])) fail(401, 'Invalid bot token.');
    $fileId = filter_var($_GET['file_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($fileId === false || $fileId === null) fail(400, 'Specify file_id.');
    $file = one('SELECT f.*, m.created_at, mem.joined_at FROM message_files f JOIN messages m ON m.id = f.message_id JOIN members mem ON mem.chat_id = m.chat_id AND mem.user_id = ? WHERE f.message_id = ?', [$botId, $fileId]);
    if (!$file || $file['created_at'] < $file['joined_at'] || !preg_match('/^[a-f0-9]{48}\.bin$/D', $file['stored_name'])) fail(404, 'The bot cannot access this file.');
    $path = __DIR__ . '/files/' . $file['stored_name'];
    if (!is_file($path)) fail(404, 'File is no longer available.');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="download"; filename*=UTF-8\'\'' . rawurlencode($file['original_name']));
    header('Content-Length: ' . filesize($path));
    header("Content-Security-Policy: sandbox; default-src 'none'");
    if ($_SERVER['REQUEST_METHOD'] === 'GET') readfile($path);
} catch (Throwable $e) {
    error_log('LChat bot file: ' . get_class($e) . ' ' . $e->getMessage());
    fail(500, 'Could not download the bot file.');
}
