# LChat

LChat is a self-hosted PHP messenger with private chats, groups, channels, file and voice messages, voice/video calls, and a Bot API. This repository contains the English web version for Apache hosting, including REG.RU shared hosting.

The web interface uses selected components from the open-source Telegram Web A interface. LChat uses its own accounts and server. See [source attribution](TELEGRAM_SOURCE.md) and the [GPL-3.0 license](LICENSE).

## Android app

Download the installable [LChat 2.0.4 APK](https://github.com/leosmleol-svg/LChat/releases/download/android-v2.0.4/LChat-2.0.4-universal.apk) (Android 8.0+). Native Android source, build instructions and testing limitations are in [android/README.md](android/README.md). Release assets also include a SHA-256 checksum. The native app has its own interface; web styling changes do not automatically change the APK.

## Server requirements

- PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, and `gd` (for profile photos)
- MySQL or MariaDB
- Apache with `mod_rewrite`
- HTTPS for browser calls, voice recording, and service workers
- An optional TURN server for calls across networks

## Installation

1. Upload the project files into one site directory, with `index.php` at its root. Keep the included `.htaccess` files.
2. Create a MySQL database and user. Copy `config.example.php` to a private `config.php` in the site directory and set its database connection, site URL, session settings, and a random installation key. **Never commit or publish `config.php`.** The existing local config is intentionally left unchanged.
3. Open `https://your-domain/install.php?key=YOUR_INSTALL_KEY` to create or update tables. Remove `install.php` from the public server and clear the installation key after setup.
4. Ensure `data/` and `files/` are writable by PHP. Their contents are private and are not part of the GitHub source.
5. Open `index.php` and register the first account. Configure HTTPS and TURN before testing calls with users on different networks.

The web app checks browser cookies and PHP sessions during login. If a login returns to the sign-in page, verify the configured session domain, HTTPS/proxy settings, and PHP session storage. Browser calls require a secure context even when the site itself is accessible over HTTP.

## Features

- Private chats, groups, channel links, invitations, contacts, and presence
- Text, file, photo, voice messages, reactions, replies, edits, and deletion
- Group calls, private calls, and call links with browser WebRTC
- Multiple-tab call status and an incoming call interface
- Profile photos, themes, notification sounds, and microphone switching
- Admin panel at `admin.php` for authorized administrators
- Bot creation through `@BotCreator`, polling and webhooks, inline buttons, and reply keyboards

## Bot API

Open `docs.php` on the site or read [BOT_API.md](BOT_API.md). Create a bot by messaging `@BotCreator` with `/newbot`, then pass its token using `Authorization: Bearer YOUR_TOKEN` when calling `bot-api.php?method=getMe`. Store bot tokens on your own server, never in browser code or a public repository.

## Updating

Back up your private `config.php`, `data/`, and `files/` before replacing the site files. Keep the private config and uploaded data when deploying a new version. Run the protected installer again if the update adds database tables or columns, then remove it from the public server. Clear the browser cache or refresh the service worker if the interface still shows an older version.

## Source and secrets

The repository ignores `config.php`, local auth configuration, runtime data, and uploaded files. Do not manually upload those files to GitHub. If a secret was exposed, replace it in the service where it is used.
