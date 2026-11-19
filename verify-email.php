<?php
declare(strict_types=1);
define('LCHAT_INTERNAL', true);
require __DIR__.'/app.php';
require __DIR__.'/apis/registration-mail.php';
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
$message=''; $token=(string)($_POST['token'] ?? $_GET['token'] ?? ''); $ctx=context();
try {
    if (!preg_match('/^[a-f0-9]{64}$/D',$token)) throw new RuntimeException('Link invalid.');
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if (!hash_equals($ctx['csrf'],(string)($_POST['csrf'] ?? ''))) throw new RuntimeException('Refresh the page and try again.');
        registration_table();
        transaction(function() use ($token) {
            $row=one('SELECT * FROM pending_registrations WHERE token_hash = ? AND expires_at >= ? FOR UPDATE',[hash('sha256',$token),stamp()]);
            if (!$row) throw new RuntimeException('The link has already been used or has expired. Register again.');
            query('INSERT INTO users (username,email,name,password_hash,created_at,last_seen) VALUES (?,?,?,?,?,?)',[$row['username'],$row['email'],$row['name'],$row['password_hash'],stamp(),stamp()]);
            query('DELETE FROM pending_registrations WHERE token_hash = ?',[$row['token_hash']]);
        });
        $message='Address confirmed. You can now log in LChat.'; $token='';
    }
} catch (Throwable $e) { error_log('LChat verify email: '.$e->getMessage()); $message=$e instanceof PDOException?'Failed to create account. Perhaps login or email already taken.':$e->getMessage(); $token=''; }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Mail confirmation · LChat</title><link rel="stylesheet" href="assets/legal.css?v=1"></head><body><main class="page"><article><h1>Mail confirmation</h1><?php if ($message): ?><p><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></p><a href="index.php">Go to entrance</a><?php else: ?><p>Click the button to confirm the address and complete registration.</p><form method="post"><input type="hidden" name="token" value="<?=htmlspecialchars($token,ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="csrf" value="<?=htmlspecialchars($ctx['csrf'],ENT_QUOTES,'UTF-8')?>"><button type="submit">Confirm email</button></form><?php endif; ?></article></main></body></html>
