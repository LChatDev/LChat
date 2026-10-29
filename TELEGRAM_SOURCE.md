# Telegram WebK source used in LChat

LChat remains a separate PHP messenger. It does not connect to Telegram accounts or Telegram servers.

The following UI components were adapted from the [Telegram WebK source](https://github.com/TelegramOrg/Telegram-web-k), commit `654ffd809aa402f31357db2a13c462b4e5b69a23`:

- `assets/tgico.woff` is copied from `public/assets/fonts/tgico.woff`.
- `assets/webk-pattern.svg` is copied from `public/assets/img/pattern.svg`.
- Icon code points in `assets/app.js` and `assets/webk-ui.js` follow `src/scss/tgico/_variables.scss`.
- Sidebar width, header height, chat list rows, and bubble measurements in `assets/webk-precise.css` follow `src/scss/variables.scss` and the corresponding sidebar/chat partials. The layout is adapted to LChat's PHP interface and features.

Telegram WebK is distributed under the GNU General Public License version 3. The corresponding license is included in [LICENSE](LICENSE). This LChat source distribution is available under GPL-3.0. Telegram's names and logos are not used for LChat branding.
