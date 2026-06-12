---
paths:
  - "app/Models/**/*.php"
---

# Models

- Ordering: traits, constants, properties (`$fillable`, `$hidden`, `$appends`), `casts()`, boot/initialization, relationships, accessors/mutators, scopes, public methods, protected/private methods.
- Accessors use `Attribute::make()`, not `getFieldAttribute()`.
- Query filters as scopes with `for*` prefix (`forCategory`, `forFavorited`, `forRole`, `forTrashed`). Accept nullable, no-op on null so callers can chain without conditionals.
- Sorting via `scopeSortBy` that accepts typed enum params and applies a default column/direction when null. Every listable model has one.
- `Searchable` trait + `SearchableObserver` pattern: models define `$searchable` fields, observer auto-syncs a `keywords` column on save, `forKeywordsSearch` scope does fulltext (MySQL) or LIKE (SQLite) search. Models can override `scopeForSearch` to add extra clauses (e.g., User adds email search).
- `HasTrashedScope` trait for models with `SoftDeletes` - provides `forTrashed` scope using `TrashedFilter` enum.
- Domain logic (avatar storage, `purge()`, `anonymize()`, tag syncing) lives in models, not controllers.
- Boot-time model events for computed fields (e.g., Tag auto-generates `slug` from `name` in `creating`/`updating`).
- Simple model-specific lifecycle hooks use `boot()` in the model. Cross-cutting concerns use observers registered via `#[ObservedBy]` attribute.
- `Plan` model uses `rememberForever()` cache, auto-invalidated on save/delete. `free()` returns the free plan. `feature()` reads from the features JSON with null meaning unlimited. `priceId(PlanInterval)` resolves the Stripe price ID for the given interval. `is_billable` returns true when Stripe prices are set. `is_complimentary` returns true for non-free plans with no Stripe prices.
- `Price` model belongs to `Plan`, one row per `PlanInterval` (monthly, yearly). `amount` stored in cents, plus `stripe_product_id` and `stripe_price_id`. Stripe syncing lives in `PlanSyncService`, not on the models.
- `ManagesSubscription` trait on User provides `subscribeToPlan()`, `swapPlan()`, `cancelPlan()`, `resumePlan()`, `assignComplimentaryPlan()`, computed attributes (`is_subscribed`, `is_on_trial`, `is_on_grace_period`, `is_complimentary`), `onComplimentary()`, and `canUsePlanFeature()` for limit checks. `assignComplimentaryPlan()` cancels any active Stripe subscription and sets `plan_id` directly. Requires `subscriptions` relation to be eager loaded for attribute access. Subscribe/swap responses include `client_secret` when `stripe_status` is `incomplete` (3D Secure required). Each method dispatches a corresponding notification (`PlanSubscribedNotification`, `PlanChangedNotification`, `PlanCancelledNotification`, `PlanResumedNotification`).
- `Notification` model extends `DatabaseNotification` to add custom scopes (`forRead`). User overrides `notifications()` to return this model.
- `Model::preventLazyLoading(! $this->app->isProduction())` is set in `AppServiceProvider::boot()`. Relations must be eager loaded; accessing an unloaded relation throws in non-production environments. Controllers eager load explicitly via `->with()` / `->load()`.
