---
paths:
  - "app/Rules/**/*.php"
---

# Validation Rules

- One rules class per resource (`UserRules`, `BookmarkRules`, `CategoryRules`, `TagRules`) plus `SharedRules` for cross-resource fields (`perPage`, `sortDir`, `search`, `trashed`, `id`, `token`, `verificationCode`).
- Methods are generic by default, then more specific by action when needed. Pattern: `password()` (base), `passwordNew()` (with `confirmed` + `Password::defaults()`), `passwordCurrent()` (with `current_password`). Same for `email()` / `emailNew()`, `role()` / `roleUpdate()`.
- Custom validation rule classes (e.g., `TagNameFormat`) for complex regex validation, implementing `ValidationRule`.
