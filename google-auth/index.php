<?php
declare(strict_types=1);
http_response_code(410);
header('Cache-Control: no-store');
header('Content-Type: text/plain; charset=utf-8');
echo 'Login via Google disabled. Open chat.lbs.su and log in with password.';