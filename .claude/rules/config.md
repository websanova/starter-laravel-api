---
paths:
  - "config/**/*.php"
---

# Config

- `env()` is only ever called inside `config/*.php`. Once `config:cache` runs (production deploys run `php artisan optimize`), `.env` is no longer loaded and `env()` returns null everywhere else. Map every env var to a config key here, then read it via `config()` in app code.
- `config/verification.php` - verification mode (`disabled`/`auto`/`required`), channels, grace period, code length/expiry, resend throttle, max attempts. All env-driven.
- `config/auth.php` custom sections:
  - `auth.delete` - account deletion grace period (days) and prune strategy (`delete`/`anonymize`).
  - `auth.activity_throttle` - seconds between `last_active_at` updates.
  - `auth.email_change` - token expiry and throttle for email change flow.
- `config/subscription.php` - subscription mode (`freemium`/`trial`/`required`), trial days, require card upfront, automatic tax. All env-driven. No Stripe IDs live in config or env; plan prices are matched by lookup keys hardcoded in `PlanSeeder`.
