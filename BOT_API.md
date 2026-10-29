# LChat Bot API

Create a bot in LChat by messaging `@BotCreator` with `/newbot`. The bot creator asks for a display name and a username ending in `bot`, then returns an API token. Keep the token private. `/mybots` lists your bots; `/token @yourbot` rotates a token; `/setname @yourbot New name` changes its display name.

The base endpoint is `https://chat.lbs.su/bot-api.php?method=METHOD`. Set the `Authorization: Bearer YOUR_TOKEN` header, or use `X-LChat-Bot-Token`. When Apache rewrite rules are enabled, `https://chat.lbs.su/bot<TOKEN>/<METHOD>` also works. All responses are JSON with `ok` and either `result` or `error_code` plus `description`.

## Methods

| Method | Purpose |
| --- | --- |
| `getMe`, `getChat` | Read bot and accessible chat information. |
| `getUpdates` | Poll messages and callback queries; use `offset` and `limit`. |
| `setWebhook`, `deleteWebhook`, `getWebhookInfo` | Manage an HTTPS webhook. |
| `sendMessage` | Send text, inline buttons, or a reply keyboard. |
| `sendDocument`, `sendPhoto`, `sendAudio`, `sendVideo`, `sendVoice`, `sendAnimation` | Upload a file or use an accessible `file_id`. |
| `getFile` | Read file metadata and download path. |
| `editMessageText`, `editMessageReplyMarkup`, `deleteMessage` | Edit or delete a bot message. |
| `answerCallbackQuery` | Acknowledge an inline button press. |

Bots receive messages only from chats they have joined, and only after joining. With `getUpdates`, save the last `update_id + 1` as the next offset. Use a delay between polls. An active webhook disables polling.

## Buttons

Pass `reply_markup` to `sendMessage`. For inline buttons:

```json
{"inline_keyboard":[[{"text":"Done","callback_data":"done"},{"text":"Website","url":"https://example.com"}]]}
```

The button press arrives as `callback_query`. Reply with `answerCallbackQuery`. URL buttons require HTTPS.

For buttons above the message field:

```json
{"keyboard":[[{"text":"Help"},{"text":"Profile"}]],"resize_keyboard":true}
```

Pressing a reply-keyboard button sends its text as a message. `one_time_keyboard` and `input_field_placeholder` are supported. Send `{"remove_keyboard":true}` to hide the keyboard.

## Examples

```sh
curl -H "Authorization: Bearer YOUR_TOKEN" "https://chat.lbs.su/bot-api.php?method=getMe"
curl -H "Authorization: Bearer YOUR_TOKEN" "https://chat.lbs.su/bot-api.php?method=getUpdates&offset=0"
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" -H "Content-Type: application/json" -d '{"chat_id":123,"text":"Hello!"}' "https://chat.lbs.su/bot-api.php?method=sendMessage"
curl -H "Authorization: Bearer YOUR_TOKEN" -F "chat_id=123" -F "document=@photo.png" "https://chat.lbs.su/bot-api.php?method=sendDocument"
```

Webhooks require a public HTTPS URL. When setting a webhook, you can provide `secret_token`; verify the `X-Telegram-Bot-Api-Secret-Token` header in your receiver. The site attempts delivery through cURL after requests. For retries when there is no new site traffic, run `php tools/bot-webhook-worker.php` with cron.

Files are limited to 8 MB, with a 200 MB bot storage limit. LChat implements the methods listed above, not the full Telegram Bot API. Payments, stickers, and many other Telegram methods are unavailable.
