<?php
declare(strict_types=1);
if (!defined('LCHAT_INTERNAL')) { http_response_code(404); exit; }
return array (
  'database' => 
  array (
    'dsn' => 'mysql:host=localhost;port=3306;dbname=(DBNAMEHERE);charset=utf8mb4',
    'username' => 'DBUSERHERE',
    'password' => 'DBPASSHERE',
  ),
  'install_key' => '30RANDOMSYMBOLS',
  'registration_open' => true,
  'session_name' => 'lchat_session_v3',
  'session_domain' => '',
  'max_call_participants' => 6,
  'ice_servers' => 
  array (
  ),
  'turn' => 
  array (
    'urls' => 
    array (
      0 => 'turn:209.25.141.16:45209?transport=tcp',
    ),
    'secret' => '2089c0eaed18b0c289e5722d6ea7d8945a5ae9037c2259886743233fef1dee3c',
    'ttl' => 3600,
  ),
  'call_transport' => 'relay',
  'public_url' => 'https://URURL!',
);
# Dont change turn server! Its free for anyone! You can create your server. I dont have guides
