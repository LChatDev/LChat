# Telegram WebK source used in LChat

LChat remains a separate PHP messenger. It does not connect to Telegram accounts or Telegram servers.

The following UI components were adapted from the [Telegram WebK source](https://github.com/TelegramOrg/Telegram-web-k), commit `654ffd809aa402f31357db2a13c462b4e5b69a23`:

- `assets/tgico.woff` is copied from `public/assets/fonts/tgico.woff`.
- Icon code points in `assets/app.js` and `assets/webk-ui.js` follow `src/scss/tgico/_variables.scss`.
- Bubble spacing and corner measurements in `assets/webk-ui.css` follow `src/scss/partials/_chatVariables.scss`. The surrounding layout is adapted to LChat's PHP interface and features.

Telegram WebK is distributed under the GNU General Public License version 3. The corresponding license is included in [LICENSE](LICENSE). This LChat source distribution is available under GPL-3.0. Telegram's names and logos are not used for LChat branding.
