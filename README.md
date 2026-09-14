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
- Per-channel modes: each channel independently disabled, auto, or required
- Optional per-channel grace period before enforcement
- Email fully wired, SMS scaffolded (needs a phone-capture flow)
- Channels toggle on/off on the fly without locking out existing users. Gating tracks whether the user actually has the identifier, so a previously registered user with no phone bypasses a newly-required phone channel. Email is always collected at registration so it enforces immediately. Forcing existing phone-less users to add a number is out of scope, add a phone-required interrupt later if you need it.

**Account Self-Management**
- Profile, password, avatar
- Email change via new inbox confirmation (old stays active until confirmed)
- Locale and timezone preferences
- Soft delete with configurable grace period
- Scheduled prune with anonymize or hard delete strategy

**Delete & Restore**
- Soft delete, anonymize, and hard delete strategies
- Admin restore and force delete
- Grace period with auto-restore on login
- Scheduled prune with configurable strategy

**Search (Fulltext)**
- Fulltext user search
- Search stays in sync automatically as records change
- Exact matching on fields like email

**Sample CRUD (Bookmarks)**
- Bookmarks with tags
- Some filters, sorting, and pagination
- Demonstrates: relationships, ownership scoping, filtering
- Admins can view and remove a user's bookmarks and tags

**Rate Limiting**
- Global throttle on all routes
- Stricter throttle on auth routes, keyed by email + IP

**Route Structure**
- Separate user and admin APIs, each with its own login
- Consistent response shapes
- JSON-only, all user-facing strings translatable
- Public settings for clients to read

**Localization (i18n)**
- Full multi-locale support out of the box, with a sample Canadian French translation included
- Per-user locale preference that localizes emails and notifications automatically, even inside queued jobs where there's no request to read from
- Responses in the client's requested language, falling back to the base language when a region isn't translated
- Add a language by dropping in a lang folder, no code changes
- Translating an app is far easier now that AI can generate a full, accurate translation set in minutes, so supporting locales out of the box is more worthwhile than it used to be

**Roles and Permissions (Spatie)**
- Two roles: super (god mode), admin (manages users, plans, stats)
- Coarse permissions with per-target checks
- Admin can't touch super users, can't delete other admins
- User management with search and some filters
- Forced password reset flow with temp password email
- Seeded super user on first migrate

**Plans & Subscriptions**
- Flexible plans with usage limits
- Multiple billing intervals per plan (monthly, yearly)
- Prices kept in sync with the provider, no hardcoded amounts
- Three subscription modes: freemium, trial, required
- Users subscribe, swap, cancel, resume, admins cancel and resume
- Embedded checkout collecting address, card, and promo code in one step, with card authentication handled provider-side
- Optional automatic tax, off by default since it has to be switched on provider-side too
- Saved card management
- Billing address management
- Feature limits enforced automatically
- Admin plan and price management

**Billing Providers (Cashier/Stripe)**
- Stripe through Laravel Cashier out of the box, one provider installed at a time
- Swappable provider without touching the rest of the app

**Webhooks**
- Local webhooks work out of the box, no tunnel, no ngrok, no dashboard endpoint for dev
- Webhooks are the source of truth, including changes made outside the app like dunning and failed renewals
- Access granted only once payment is confirmed by the provider, never on a client reporting its own success
- Safe against provider retries
- Billing state can always be rebuilt from the provider, so a dropped event is recoverable
- Client-triggered sync after checkout so changes show immediately

**Promotion Codes**
- Validated against the provider, nothing stored locally, so it owns amounts, expiry, and limits
- Codes checked as the user enters them
- Admins can apply or clear discounts on an existing subscription

**Notifications**
- In-app notifications (bell icon)
- Email + in-app delivery for plan lifecycle events (subscribe, change, cancel, resume)
- Account emails for welcome, verification, password reset and change, email change
- List with some filters, mark read/unread, mark all read
- Unread count for polling
- Consistent payload shape across all notification types

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
- Admin stats with some filters

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
