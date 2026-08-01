# Starter Laravel API

A Laravel API starter with a full auth system, account management, and production-ready integrations out of the box. No frontend, no Blade, no Vite. Just a clean JSON API.

## Docs

Full documentation at [websanova.com/docs/starter-api](https://websanova.com/docs/starter-api).

**Dev:**

- [Docker Setup](docs/docker-setup.md)
- [Stripe Setup](docs/stripe-setup.md)
- [Dev Commands](docs/dev-commands.md)
- [Markdown Viewer](docs/markdown-viewer.md)

**Subscriptions:**

- [Susbcription Summary](docs/subscription-summary.md)
- [Susbcription Flows](docs/subscription-flows.md)


## Features

**Token Auth (Sanctum)**
- Register, login, logout, token refresh
- Configurable token expiration
- Soft-deleted accounts auto-restore on login during grace period

**Account Verification**
- Code-based, not signed URLs, works with any client
- Per-channel modes: each channel independently disabled, auto, or required
- Optional per-channel grace period before enforcement
- Email fully wired, SMS scaffolded (needs a phone-capture flow)
- Channels toggle on/off on the fly without locking out existing users. Gating tracks whether the user actually has the identifier, so a previously registered user with no phone bypasses a newly-required phone channel. Email is always collected at registration so it enforces immediately. Forcing existing phone-less users to add a number is out of scope, add a phone-required interrupt later if you need it.

**Password Reset and Email Change**
- Token-based two-step flows with frontend URL redirect
- All sessions revoked on password reset
- Email change confirmed via new inbox, old stays active until confirmed

**Account Self-Management (`/account`)**
- Profile, password, avatar (S3-compatible storage)
- Soft delete with configurable grace period
- Scheduled prune with anonymize or hard delete strategy

**Delete & Restore**
- Soft delete, anonymize, and hard delete strategies
- `TrashedFilter` enum with `HasTrashedScope` trait for filtering trashed records
- Grace period with auto-restore on login
- Scheduled prune command with configurable strategy

**Search (Fulltext)**
- Searchable trait with MySQL fulltext index on a denormalized keywords column
- Observer auto-syncs keywords when searchable fields change
- Models override `forSearch` to add column-specific clauses (e.g. email)

**Sample CRUD (Bookmarks)**
- Bookmarks with optional flat categories
- Demonstrates: relationships, ownership scoping, filtering, nullable foreign keys

**Rate Limiting**
- Global throttle on all routes
- Stricter throttle on auth routes, keyed by email + IP

**Route Structure**
- `/auth` for authentication, `/account` for self-management, `/admin` for resource management
- API Resources for all responses
- JSON-only, lang files for all user-facing strings

**Localization (i18n)**
- Full multi-locale support out of the box, with a sample Canadian French translation included
- Per-user locale preference that localizes emails and notifications automatically, even inside queued jobs where there's no request to read from
- Request responses localized from the client's `Accept-Language` header, falling back to the base language when a region isn't translated
- Add a language by dropping in a lang folder, no code changes
- Translating an app is far easier now that AI can generate a full, accurate translation set in minutes, so supporting locales out of the box is more worthwhile than it used to be

**Roles and Permissions (Spatie)**
- Two roles: super (god mode), admin (manages users)
- Coarse permissions with policy-based target checks
- Admin can't touch super users, can't delete other admins
- Forced password reset flow with temp password email
- Seeded super user on first migrate

**Plans & Subscriptions**
- Flexible plans with usage limits and feature flags
- Multiple billing intervals per plan (monthly, yearly)
- Prices kept in sync with the provider, no hardcoded amounts
- Three subscription modes: freemium, trial, required
- Subscribe, swap, cancel, resume for both users and admins
- Embedded provider checkout, so tax, discounts and card authentication stay provider-side
- Complimentary plans for granting access without billing
- Feature limits enforced automatically

**Billing Providers (Cashier/Stripe)**
- Stripe through Laravel Cashier out of the box, one provider installed at a time
- Provider contracts so a swap touches the services and migration, not controllers
- Provider-namespaced services and webhook listener (`App\Services\Stripe`)
- Webhook is the single commit path, with idempotent writes throughout
- Entitlement committed only once the provider confirms payment

**Promotion Codes**
- Collected and validated inside the provider's checkout, no endpoint needed
- Admins can apply or clear discounts on an existing subscription
- Nothing stored locally, the provider owns amounts, expiry, and limits

**Notifications**
- Laravel's database notification channel for in-app notifications (bell icon)
- Email + database delivery for plan lifecycle events (subscribe, change, cancel, resume)
- List, filter by read/unread, mark individual read/unread, bulk mark all read
- Consistent payload shape across all notification types

**Mail (Resend)**
- Resend as the default mail transport (free tier, community Laravel driver)
- Zero-cost email for a starter project, easily swappable to Mailgun, Postmark, or SES
- For inbound forwarding, consider Cloudflare Email Routing (free, unlimited)
- For "send as" replies from a custom address, Gmail supports Resend's SMTP credentials

**File Storage (S3)**
- Off-server storage, AWS and DigitalOcean Spaces ready
- Image processing via Intervention
- `StoragePath` enum for centralized path management

**Stats**
- Scheduled stat calculation with date range breakdowns (all, today, yesterday, day before)
- Grouped by resource type (subscriptions, bookmarks, categories, tags)
- Subscription stats per plan and billing interval
- Admin endpoint to retrieve stats with optional group filter

**Backups (Spatie)**
- Database-only daily backups via `spatie/laravel-backup`
- Scheduled cleanup of old backups

**Dev Environment**
- Dockerized (PHP-FPM + MySQL)
- `./run` script for container commands
- Pest test suite