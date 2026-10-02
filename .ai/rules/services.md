---
paths:
  - "app/Services/**/*.php"
---

# Services

- Used when business logic spans multiple models, has complex orchestration, or interacts with external APIs (e.g., `VerificationService` coordinates codes, hashing, notifications, throttling; `EmailChangeService` coordinates tokens, notifications, email swaps; `PromotionCodeService` resolves promotion codes against Stripe; `PlanSyncService` resolves plan prices from Stripe by lookup key via `syncAll`, `sync(Plan)`, and `syncPrice(Price)`).
- Not used for simple CRUD that a model or controller can handle directly.
- Services never throw `ValidationException` or any HTTP-layer exception. Return `ServiceResult` instead so services stay reusable outside HTTP context (queues, CLI).
- `ServiceResult` (`app/Support/ServiceResult.php`) is the standard return type for service methods that can fail. `ServiceResult::success($data)` for success, `ServiceResult::error('fragment.key')` for failure. The error string is a lang key fragment; the controller decides the prefix and throws `ValidationException`.
- Controllers check `$result->success`, access `$result->data` on success, and translate `$result->error` into `ValidationException::withMessages()` on failure.
- Injected provider contracts are named after the action, not the resource: the parameter is the contract name minus the `Provider` suffix, lowerCamelCase. `CancelSubscriptionProvider $cancelSubscription`, `DeletePaymentMethodProvider $deletePaymentMethod`. Derived from the type so there is nothing to decide, unique per signature, and the call site says what runs rather than what it acts on.
- Operations that issue multiple database writes wrap them in `DB::transaction` so they commit or roll back together.
- Never wrap an external API call (Stripe, etc.) in `DB::transaction`. A database rollback cannot undo an external side effect. Order the irreversible external call first, then the local write, and reconcile via webhook if needed.
