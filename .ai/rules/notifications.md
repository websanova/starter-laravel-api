---
paths:
  - "app/Notifications/**/*.php"
---

# Notifications

- All emails route through notifications (`app/Notifications/`), not raw `Mail::send()`. This keeps a single interface that can be extended to SMS via the `$channelMap` pattern (maps config channel names like `'sms'` to Laravel channels like `'vonage'`).
- Verification notifications use `config('verification.channels')` to determine delivery channels.
- Flat folder structure in `app/Notifications/`. No sub-namespacing.
- Database channel for user-facing notifications (bell icon). Database payload uses a consistent shape: `{ title, body }`.
- Notification classes that serve both email and database define `toMail()` and `toArray()`.
- User model overrides `notifications()` to use `App\Models\Notification` (extends `DatabaseNotification`) for custom scopes like `forRead`.
- `ManagesSubscription` dispatches plan notifications (`PlanSubscribedNotification`, `PlanChangedNotification`, `PlanCancelledNotification`, `PlanResumedNotification`) directly from trait methods.
