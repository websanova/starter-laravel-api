---
paths:
  - "app/Services/**/*.php"
---

# Services

- Used when business logic spans multiple models or has complex orchestration that doesn't belong in a single model (e.g., `VerificationService` coordinates codes, hashing, notifications, throttling; `EmailChangeService` coordinates tokens, notifications, email swaps).
- Not used for simple CRUD that a model or controller can handle directly.
