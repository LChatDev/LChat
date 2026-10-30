<?php
declare(strict_types=1);
if (!defined('LCHAT_INTERNAL')) { http_response_code(404); exit; }

return [
    // Backend: host handles requests here; pc forwards to your PC over HTTPS.
    'backend' => ['mode' => 'host', 'url' => '', 'proxy_secret' => ''],
    'database' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=YOUR_DATABASE;charset=utf8mb4',
        'username' => 'YOUR_DATABASE_USER',
        'password' => 'YOUR_DATABASE_PASSWORD',
    ],
    'install_key' => 'REPLACE_WITH_A_LONG_RANDOM_SECRET',
    // Shared secret for authenticated gateway requests to this backend.
    'proxy_secret' => '',
    // Public name shown in the web interface. Existing installations default to LChat.
    'site_name' => 'LChat',
    // The registered user who becomes owner on a fresh installation.
    'initial_admin_username' => 'leosmleol',
    'registration_open' => true,
    'session_name' => 'lchat_session',
    'session_domain' => '',
    // WebRTC uses a mesh connection; keep group calls small.
    'max_call_participants' => 8,
    'ice_servers' => [],
    'turn' => [
        'urls' => 'turn:209.25.141.16:45209?transport=tcp',
        // Set this privately in config.php or the LCHAT_TURN_SECRET environment variable.
        'secret' => getenv('LCHAT_TURN_SECRET') ?: '',
        'ttl' => 3600,
    ],
    'call_transport' => 'relay',
    'public_url' => 'https://your-domain.example',
];
