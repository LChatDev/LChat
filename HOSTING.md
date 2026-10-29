# Hosting LChat on PHP and MySQL

This guide uses Apache and a hosting control panel such as REG.RU. LChat uses PHP for the website and signaling, and MySQL or MariaDB for accounts and messages. Calls between users also require HTTPS and, for many networks, a separate [TURN service](CALLS.md).

## 1. Prepare hosting and a domain

1. Point a domain or subdomain to your hosting using the DNS values supplied by your host. Wait for DNS to resolve and attach the domain to the site in the hosting panel.
2. Enable PHP 8.2+ with `pdo_mysql`, `mbstring`, `fileinfo`, `curl` and `gd`. Apache must allow `.htaccess` and `mod_rewrite`.
3. Issue and enable a trusted TLS certificate through the hosting panel, and verify that `https://your-domain/` opens without certificate warnings. A browser cannot use the microphone on an ordinary remote HTTP site.
4. Create a MySQL/MariaDB database and a database user in the hosting panel; grant that user access to this database. Note the exact host, port, database name, username and password. The database host is provided by your host; it is not necessarily the website domain. The common MySQL port is `3306`.

## 2. Upload and configure

Upload the repository's **web files** to the site's document root so `index.php`, `api.php`, `app.php`, `admin.php`, `apis/`, `assets/`, `data/`, and `files/` are at that root. Include hidden `.htaccess` files. Android sources, tests and Markdown guides do not need to be served. Do not upload local `config.php`, private signing keys, Git metadata, or anyone else's data.

Copy `config.example.php` to `config.php` on the server. Edit your copy; do not replace an existing working `config.php` during an update. For example, replace the placeholders in:

```php
'database' => [
    'dsn' => 'mysql:host=YOUR_DB_HOST;port=3306;dbname=YOUR_DATABASE;charset=utf8mb4',
    'username' => 'YOUR_DATABASE_USER',
    'password' => 'YOUR_DATABASE_PASSWORD',
],
'install_key' => 'A_LONG_RANDOM_SECRET_AT_LEAST_32_CHARACTERS',
'site_name' => 'My Messenger',
'initial_admin_username' => 'myadmin',
'public_url' => 'https://chat.example.com',
```

Use a unique, unpredictable installation key, e.g. generate one locally with `php -r "echo bin2hex(random_bytes(32));"`. Set `initial_admin_username` to the username you intend to register; it must contain 3–32 lowercase Latin letters, digits or underscores. If the key is omitted, existing installations use `leosmleol`. The value is used only while no owner has been established; changing it later does not transfer ownership.

Keep `session_domain` empty unless you know you need cross-subdomain cookies. Keep `data/` and `files/` writable by the PHP process and their supplied `.htaccess` files in place. The root `.htaccess` denies access to private files. Confirm the host actually applies Apache rules; do not use this configuration unmodified on a server that ignores them. Never publish `config.php` or database backups.

## 3. Install and create the owner

1. Open `https://your-domain/install.php`. Enter the installation key in the form and create the tables.
2. Open the site and register the exact username configured in `initial_admin_username` promptly. Registration must be open during this step. Open `admin.php` while signed in as that user. The owner can add other administrators from there.
3. Remove `install.php` from the public server and clear `install_key` in the private config after installation. Back up your config and database separately.
4. Optionally close public registration in the admin panel. Configure calls as described in [CALLS.md](CALLS.md).

The owner can change the messenger name, registration, call limit, transport and TURN settings in `admin.php`. Those values are saved in the database and take precedence over the config and the existing `selfhost/settings.php` overrides. **Reset to config.php** removes the database overrides. TURN secrets are stored server-side and are not shown after saving. Database credentials, session settings, `public_url`, STUN entries and the initial owner are managed in `config.php`, not through the form. If config contains several TURN URLs, the admin form displays one; saving it replaces that list with the single displayed URL.

## Updates, backups and troubleshooting

- Back up the database, private `config.php`, `data/` and `files/`. Upload updated web code and preserve those private files and directories. Follow release instructions if a schema update is required. Verify login and calls afterwards.
- A database connection error usually means an incorrect host, port, database name, user, password, permissions, or missing `pdo_mysql`. Inspect the hosting PHP error log; do not share credentials.
- Login returning to the sign-in page can indicate broken PHP session storage, an incorrect session domain, or inconsistent HTTP/HTTPS. Use the same HTTPS hostname, verify session storage is writable, and review the PHP error log.
- The browser must accept session cookies. Updating a cached site may require a hard reload or a service-worker refresh.
- Optional cleanup: schedule `php /absolute/path/to/cleanup.php` hourly using the hosting scheduler, selecting the same PHP version and extensions as the website. It removes expired call signaling and rate-limit data.

See [PHP installation requirements](https://www.php.net/manual/en/install.requirements.php) and [MDN's secure-context documentation](https://developer.mozilla.org/en-US/docs/Web/Security/Secure_Contexts) for background.
