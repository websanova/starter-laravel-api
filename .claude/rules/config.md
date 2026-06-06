---
paths:
  - "config/**/*.php"
---

# Config

- `config/verification.php` - verification mode (`disabled`/`auto`/`required`), channels, grace period, code length/expiry, resend throttle, max attempts. All env-driven.
- `config/auth.php` custom sections:
  - `auth.delete` - account deletion grace period (days) and prune strategy (`delete`/`anonymize`).
  - `auth.activity_throttle` - seconds between `last_active_at` updates.
  - `auth.email_change` - token expiry and throttle for email change flow.
