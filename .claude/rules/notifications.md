---
paths:
  - "app/Notifications/**/*.php"
---

# Notifications

- All emails route through notifications (`app/Notifications/`), not raw `Mail::send()`. This keeps a single interface that can be extended to SMS via the `$channelMap` pattern (maps config channel names like `'sms'` to Laravel channels like `'vonage'`).
- Verification notifications use `config('verification.channels')` to determine delivery channels.
