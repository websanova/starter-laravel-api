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
