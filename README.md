# LChat

**Windows и доступ к портам / Windows and port access:** [Инструкция для Windows](WINDOWS_HOST_RU.md) · [Windows guide](WINDOWS_HOST_EN.md) · [Программы и настройка](PORT_TOOLS_RU.md) · [Tools and setup](PORT_TOOLS_EN.md)


> **Notice:** LChat was created entirely with the help of artificial intelligence. The project includes third-party components identified in its documentation and licenses.

LChat is a self-hosted PHP messenger with private chats, groups, channels, file and voice messages, voice/video calls, and a Bot API. This repository contains the English web version for Apache hosting, including REG.RU shared hosting.

The web interface uses selected components from the open-source Telegram Web A interface. LChat uses its own accounts and server. See [source attribution](TELEGRAM_SOURCE.md) and the [GPL-3.0 license](LICENSE).

## Android app

Download the installable [LChat 2.2.0 test APK](https://github.com/LChatDev/LChat/releases/download/android-v2.2.0/LChat-2.2.0-universal.apk) (Android 8.0+). Native Android source, build instructions and testing limitations are in [android/README.md](android/README.md). Release assets also include a SHA-256 checksum. The native app has its own interface; web styling changes do not automatically change the APK.

## Server requirements

- PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, and `gd` (for profile photos)
- MySQL or MariaDB
- Apache with `mod_rewrite`
- HTTPS for browser calls, voice recording, and service workers
- An optional TURN server for calls across networks

## Installation

1. Upload the web files into one site directory, with `index.php` at its root. Keep the included `.htaccess` files.
2. Create a MySQL database and user. Copy `config.example.php` to a private `config.php` and set the database, `public_url`, `site_name`, `initial_admin_username`, and a random installation key of at least 32 characters. **Never commit or publish `config.php`.**
3. Enable HTTPS. Open `https://your-domain/install.php`, enter your installation key into the form, and create the tables. Remove `install.php` from the public server and clear the key after setup.
4. Ensure `data/` and `files/` are writable by PHP. Their contents are private and are not part of the GitHub source.
5. Register the configured `initial_admin_username` promptly and sign in as that user to open `admin.php`. Configure TURN before testing calls across networks.

Follow the complete [hosting and installation guide](HOSTING.md) and the separate [calls and TURN guide](CALLS.md) for configuration, ports, troubleshooting, and updates.
For a possible free full-stack deployment, see the [free hosting walkthrough](FREE_HOSTING.md). For options to expose a local server, see [free tunnels and private networking](TUNNELS.md).

The web app checks browser cookies and PHP sessions during login. If a login returns to the sign-in page, verify the configured session domain, HTTPS/proxy settings, and PHP session storage. Browser calls require a secure context even when the site itself is accessible over HTTP.

## Features

- Private chats, groups, channel links, invitations, contacts, and presence
- Text, file, photo, voice messages, reactions, replies, edits, and deletion
- Group calls, private calls, and call links with browser WebRTC
- Multiple-tab call status and an incoming call interface
- Profile photos, themes, notification sounds, and microphone switching
- Admin panel at `admin.php` for authorized administrators
- Bot creation through `@BotCreator`, polling and webhooks, inline buttons, and reply keyboards

See [SERVER.md](SERVER.md) to choose the hosting server or a backend on your PC.

## Server settings

The owner can edit the messenger name, registration, call participant limit, transport and TURN settings in `admin.php`. Saved settings override `config.php`; reset them in the panel to use config values again. TURN secrets are not returned by the admin form. Database credentials, `public_url`, session settings, STUN servers and the initial owner are configured in `config.php`. See [HOSTING.md](HOSTING.md) for precedence and multi-URL details.

## Bot API

Open `docs.php` on the site or read [BOT_API.md](BOT_API.md). Create a bot by messaging `@BotCreator` with `/newbot`, then pass its token using `Authorization: Bearer YOUR_TOKEN` when calling `bot-api.php?method=getMe`. Store bot tokens on your own server, never in browser code or a public repository.

## Updating

Back up your private `config.php`, `data/`, and `files/` before replacing the site files. Keep the private config and uploaded data when deploying a new version. Run the protected installer again if the update adds database tables or columns, then remove it from the public server. Clear the browser cache or refresh the service worker if the interface still shows an older version.

## Source and secrets

The repository ignores `config.php`, local auth configuration, runtime data, and uploaded files. Do not manually upload those files to GitHub. If a secret was exposed, replace it in the service where it is used.

## Руководства / Guides

- Windows: [Русский](WINDOWS_HOST_RU.md) · [English](WINDOWS_HOST_EN.md)
- Открытие портов и туннели / Port forwarding and tunnels: [Русский](PORT_TOOLS_RU.md) · [English](PORT_TOOLS_EN.md)
- Звонки и TURN / Calls and TURN: [Русский](CALLS_RU.md) · [English](CALLS_EN.md)
- Бесплатный хостинг / Free hosting: [Русский](FREE_HOSTING_RU.md) · [English](FREE_HOSTING_EN.md)

Дополнительные репозитории / Dedicated guides: [LChatCalls](https://github.com/LChatDev/LChatCalls) · [LChatFree](https://github.com/LChatDev/LChatFree).

- Бесплатный домен / Free hostname: [Русский](FREE_DOMAIN_RU.md) · [English](FREE_DOMAIN_EN.md)

Message text: up to **10 MiB in UTF-8**. Back up your database before upgrading: the MySQL migration widens message and bot-update columns. Check PHP and web-server request limits and MySQL `max_allowed_packet` (at least 32 MiB). Attachment limits are separate.
