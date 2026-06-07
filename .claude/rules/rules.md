---
paths:
  - "app/Rules/**/*.php"
---

# Validation Rules

- One rules class per resource (`UserRules`, `BookmarkRules`, `CategoryRules`, `TagRules`, `SubscriptionRules`) plus `SharedRules` for cross-resource fields (`perPage`, `sortDir`, `search`, `trashed`, `id`, `token`, `verificationCode`).
- Methods are generic by default, then more specific by action when needed. Pattern: `password()` (base), `passwordNew()` (with `confirmed` + `Password::defaults()`), `passwordCurrent()` (with `current_password`). Same for `email()` / `emailNew()`, `role()` / `roleUpdate()`.
- When a field only differs by required/optional, use a `bool $required` flag: `promotionCode(bool $required = false)` returns `['required', ...]` or `['sometimes', 'nullable', ...]` based on the flag. Avoids duplicate methods.
- Custom validation rule classes (e.g., `TagNameFormat`) for complex regex validation, implementing `ValidationRule`.
- Rules handle format validation only (type, length, format). External API validation (e.g., Stripe lookups) belongs in services, not rules.
- Methods within each rules class are ordered alphabetically.
