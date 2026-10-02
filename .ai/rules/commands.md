---
paths:
  - "app/Console/Commands/**/*.php"
  - "routes/console.php"
---

# Commands

- Signature format: `{resource}:{action}` (e.g., `users:prune-deleted`).
- `users:prune-deleted` runs daily, applies the configured `AccountPruneStrategy` (`delete` calls `purge()` which hard-deletes + cleans up storage/tokens, `anonymize` strips PII but keeps the row).
- Scheduled commands registered in `routes/console.php`. Also runs `sanctum:prune-expired --hours=1` daily.
- `plans:sync` syncs each plan's prices from their Stripe product default prices. Run at deploy time, not scheduled (not in `routes/console.php`).
