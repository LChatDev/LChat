<?php
declare(strict_types=1);
define('LCHAT_INTERNAL',true);
require __DIR__.'/app.php';
require __DIR__.'/apis/registration-mail.php';
require __DIR__.'/apis/password-reset.php';
header('Cache-Control: no-store, max-age=0');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
$ctx=context(); $message=''; $error=''; $reset=false; $token=''; $success=false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        if (!hash_equals($ctx['csrf'],(string)($_POST['csrf']??''))) throw new DomainException('Refresh the page and try again.');
        $action=(string)($_POST['action']??'');
        if ($action==='request') {
            password_reset_request((string)($_POST['email']??''),(string)($_SERVER['REMOTE_ADDR']??'unknown'));
            $message='If an account with such an address exists, an email with instructions will be sent. Also check your Spam folder».';
        } elseif ($action==='reset') {
            $reset=true; $token=(string)($_POST['token']??'');
            if (!password_reset_allow('reset-complete-ip:'.($_SERVER['REMOTE_ADDR']??'unknown'),30,3600)) throw new DomainException('Too many attempts. Try again later.');
            $password=(string)($_POST['password']??''); $confirm=(string)($_POST['confirm']??'');
            if ($password!==$confirm) throw new DomainException('Passwords do not match.');
            password_reset_complete($token,$password); $token=''; $reset=false; $success=true;
            $message='Password changed. You can now log in with your new password.';
        } else throw new DomainException('Invalid request.');
    } catch (Throwable $e) {
        error_log('LChat password recovery: '.get_class($e));
        $error=$e instanceof DomainException?$e->getMessage():'The request could not be completed. Please try again later.';
    }
}
$esc=static fn(string $v):string=>htmlspecialchars($v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Password recovery · LChat</title><link rel="stylesheet" href="assets/reset-password.css?v=20261109-1"><script defer src="assets/reset-password.js?v=20261109-1"></script><script src="assets/lchatai-secondary-theme.js?v=20261111-1"></script><link rel="stylesheet" href="assets/lchatai-secondary.css?v=20261111-1"></head><body class="lchat-secondary lchat-reset"><main class="reset-card"><a class="reset-back" href="index.php">← Return to entrance</a><div class="reset-logo">L</div><p class="reset-eyebrow">LCHAT · SAFETY</p><h1>Password recovery</h1><p class="reset-subtitle" id="request-description" <?=$reset||$success?'hidden':''?>>Please indicate email account. We will send you a link to create a new password.</p><p class="reset-subtitle" id="reset-description" <?=$reset&&!$success?'':'hidden'?>>Create a new password for your account.</p>
<?php if($message):?><p class="reset-message" role="status"><?=$esc($message)?></p><?php endif;?><?php if($error):?><p class="reset-error" role="alert"><?=$esc($error)?></p><?php endif;?>
<?php if(!$success):?><form method="post" id="request-form" <?=$reset?'hidden':''?>><input type="hidden" name="csrf" value="<?=$esc($ctx['csrf'])?>"><input type="hidden" name="action" value="request"><label>Email<input type="email" name="email" maxlength="190" required autocomplete="email" placeholder="you@example.com"></label><button type="submit">Send link <span>→</span></button></form>
<form method="post" id="reset-form" <?=$reset?'':'hidden'?>><input type="hidden" name="csrf" value="<?=$esc($ctx['csrf'])?>"><input type="hidden" name="action" value="reset"><input type="hidden" name="token" id="reset-token" value="<?=$esc($token)?>"><label>New password<input type="password" name="password" minlength="10" maxlength="72" autocomplete="new-password" required></label><label>Repeat password<input type="password" name="confirm" minlength="10" maxlength="72" autocomplete="new-password" required></label><p class="reset-help">Minimum 10 characters, maximum 72 bytes.</p><button type="submit">Save password <span>→</span></button><a class="request-again" href="reset-password.php">Request new link</a></form><?php else:?><a class="reset-button" href="index.php">Go to entrance →</a><?php endif;?>
<noscript><p class="reset-help">To open the recovery link you need JavaScript.</p></noscript><p class="reset-foot">If you did not request a password change, you do not need to do anything.</p></main></body></html>
