# Gifts and tickets

The gift catalog contains 170 LChat interpretations of objects visible in the supplied reference video. The catalog uses LChat ticket prices, not Telegram prices. Some objects use generated illustrations; the rest use Unicode emoji. The illustrations are original AI-generated assets, not Telegram assets. They are not exact replicas, and some small objects in the reference could not be identified reliably.

Administrators grant tickets and verification badges at `rewards.php#manage`. There are no payment or automatic top-up features. Users can send a gift from a direct-chat profile or from the catalog. Each purchase creates an individual instance with a unique identifier and deterministic background pattern. Existing gifts and ticket balances are retained on upgrade.

The catalog is a shop for sending gifts, not a resale marketplace. User-to-user listings, bidding and withdrawals are not implemented.

Tables are installed through the existing schema migration on first use. Back up the database before updating. To run the isolated SQLite test with PHP and `pdo_sqlite` enabled:

```sh
php tests/gifts-smoke.php
```
