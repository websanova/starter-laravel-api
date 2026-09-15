# Starter Laravel API

A Laravel API starter with a full auth system, account management, and production-ready integrations out of the box. No frontend, no Blade, no Vite. Just a clean JSON API.

## Docs

Full documentation at [websanova.com/docs/starter-api](https://websanova.com/docs/starter-api).

- [Startup Guide](docs/startup-guide.md)
- [Docker Guide](docs/docker-guide.md)
- [Stripe Guide](docs/stripe-guide.md)
- [Testing Guide](docs/testing-guide.md)

## Projects

| Project | Repo | Demo |
| ------- | ---- | ---- |
| Starter Flows | [starter-flows](https://github.com/websanova/starter-flows) | [flows](https://starter-flows.websanova.com) |
| Starter Laravel API | [starter-laravel-api](https://github.com/websanova/starter-laravel-api) | [api](https://starter-laravel-api.websanova.com) |
| Starter Vue SPA | [starter-vue-spa](https://github.com/websanova/starter-vue-spa) | [app](https://starter-vue-spa-app.websanova.com), [admin](https://starter-vue-spa-admin.websanova.com) |

## Notes

- The commands are experimental and the format is still being tinkered with. The main idea is to not have the agent run wild without explicit go aheads and to remove reptitive explanations to help streamline workflow. Open to suggestions and feedback.

## Features

**Token Auth (Sanctum)**
- Register, login, logout, token refresh
- Password reset via email link, revokes all sessions
- Configurable token expiration
- Soft-deleted accounts auto-restore on login during grace period
- Last active tracking

**Account Verification**
- Code-based, not signed URLs, works with any client
- Multi-channel verification, each channel configurable as disabled, auto, or required
- Optional per-channel grace period before enforcement
- Email fully wired, SMS scaffolded (needs a phone-capture flow)

**Account Self-Management**
- Profile, password, avatar
- Email change via new inbox confirmation (old stays active until confirmed)
- Locale and timezone preferences
- Soft delete with configurable grace period
- Scheduled prune with anonymize or hard delete strategy

**User Delete & Restore**
- Soft delete, anonymize, and hard delete strategies
- Admin restore and force delete
- Grace period with auto-restore on login
- Scheduled prune with configurable strategy

**Search (Fulltext)**
- Drop-in fulltext search for any model, used on users out of the box
- Search stays in sync automatically as records change
- Models can add exact matching on fields like email

**Sample CRUD (Bookmarks & Tags)**
- Reference resources to copy when building your own
- Relationships, ownership scoping, filtering, sorting, and pagination
- Admin view and removal of user records

**Rate Limiting**
- Global throttle on all routes
- Stricter throttle on auth routes, keyed by email + IP

**API Structure**
- Admin endpoints on `/admin` with their own login, account endpoints at the root
- JSON-only with consistent response shapes
- All user-facing strings translatable

**Public Settings**
- Centralized app settings served by the API for reuse across clients
- Public `/settings` endpoint, no login needed

**Localization (i18n)**
- Full multi-locale support out of the box, with a sample translations included
- Per-user locale preference that localizes emails and notifications automatically, even inside queued jobs where there's no request to read from
- Responses in the client's requested language, falling back to the base language when a region isn't translated
- Add a language by dropping in a lang folder, no code changes

**Roles and Permissions (Spatie)**
- Ships with super (god mode), admin (manages users, plans, stats) out of the box
- Coarse permissions with per-target checks
- Admin can't touch super users, can't delete other admins

**Subscriptions**
- Multiple billing intervals per plan (monthly, yearly)
- Users subscribe, swap, cancel, resume, admins cancel and resume
- Checkout sessions ready for a Stripe Payment Element checkout built into the client, not hosted or embedded
- Payment method management
- Feature limits enforced automatically

**Plans**
- Prices kept in sync with the provider, no hardcoded amounts
- Three subscription modes (freemium, trial, required)
- Billing address always collected, automatic tax on the charge toggled by config (off by default)
- Admin plan editing, with prices synced from Stripe by lookup key

**Billing Providers (Cashier/Stripe)**
- Stripe through Laravel Cashier out of the box, one provider installed at a time
- Swappable provider without touching the rest of the app

**Webhooks (Cashier/Stripe)**
- Local webhooks out of the box, no tunnel or dashboard setup for dev
- Client syncs right after checkout, webhooks back it up if the client never reports back
- Only signal for changes made outside the app, like portal swaps, dunning, and failed payments
- Access granted only after the provider confirms payment
- Safe to process twice, whichever of client sync or webhook lands second does nothing
- Plan change notifications driven by webhooks

**Promotion Codes**
- Validated against the provider, nothing stored locally, so it owns amounts, expiry, and limits
- Codes entered directly in checkout
- Admins can apply or clear discounts on an existing subscription

**Notifications**
- Database-backed in-app notifications for every user
- Email + in-app delivery for plan lifecycle events (subscribe, change, cancel, resume)
- Account emails for welcome, verification, password reset and change, email change
- List with some filters, mark read/unread, mark all read

**Mail (Mailpit)**
- Works with any SMTP provider
- Local mail catcher, no real sends and no credentials needed for dev
- Test email command

**File Storage (S3)**
- Off-server storage, AWS and DigitalOcean Spaces ready
- Avatars cropped and resized on upload

**Stats**
- Scheduled stat calculation with date range breakdowns (all, today, yesterday, day before)
- Grouped by resource type (subscriptions, bookmarks, tags)
- Subscription stats per plan and billing interval

**Backups (Spatie)**
- Daily database backups
- Scheduled cleanup of old backups

**Dev Environment**
- Dockerized with MySQL, Redis, Mailpit, and Stripe CLI
- Helper script for container commands
- Optional query log in responses for debugging
- Pest test suite

## License

MIT - see [LICENSE](LICENSE).

---

Built and maintained by [Rob](https://www.websanova.com/about). I take freelance and contract work, including MVP projects built on the Starters. Check out the [hire page](https://www.websanova.com/hire) for more info.
