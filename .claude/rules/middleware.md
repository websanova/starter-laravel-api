---
paths:
  - "app/Http/Middleware/**/*.php"
---

# Middleware

- `ForceJsonResponse` - global, forces `Accept: application/json` on all requests.
- `track-active` (`TrackLastActive`) - updates `last_active_at` with a configurable throttle (`auth.activity_throttle`) to avoid a write on every request.
- `verified` (`EnsureVerified`) - blocks unverified users when `verification.mode` is `required`. Supports a configurable grace period.
- `password-updated` (`EnsurePasswordUpdated`) - blocks users flagged with `is_password_reset_required` until they update their password.
- `admin` (`EnsureAdmin`) - safety net requiring `admin` or `super` role. Individual admin requests then do finer checks via policies.
- `subscribed` (`EnsureSubscribed`) - enforces subscription access based on `config('subscription.mode')`: freemium always allows, trial requires trial or active subscription, required requires active subscription. Returns 403 when denied.
- Stack order on `/account`: `auth:sanctum`, `track-active`, then `verified` and `password-updated` on inner routes. Gated resources (bookmarks, categories, tags) add `subscribed` middleware.
- Stack order on `/admin`: `auth:sanctum`, `track-active`, `verified`, `password-updated`, `admin`.
