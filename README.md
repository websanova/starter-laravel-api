# Starter Laravel API

A Laravel API starter with a full auth system, account management, and production-ready integrations out of the box. No frontend, no Blade, no Vite. Just a clean JSON API.

Part of the Starters, built at [Websanova](https://www.websanova.com).

## Docs

Full documentation at [websanova.com/docs/starter-api](https://www.websanova.com/docs/starter-api).

- [Startup Guide](docs/startup-guide.md)
- [Docker Guide](docs/docker-guide.md)
- [Stripe Guide](docs/stripe-guide.md)
- [Testing Guide](docs/testing-guide.md)
- [Flows Guide](docs/flows-guide.md)

## Projects

| Project | Repo | Demo |
| ------- | ---- | ---- |
| Starter Flows | [starter-flows](https://github.com/websanova/starter-flows) | [flows](https://starter-flows.websanova.com) |
| Starter Laravel API | [starter-laravel-api](https://github.com/websanova/starter-laravel-api) | [api](https://starter-laravel-api.websanova.com) |
| Starter Vue SPA | [starter-vue-spa](https://github.com/websanova/starter-vue-spa) | [app](https://starter-vue-spa-app.websanova.com), [admin](https://starter-vue-spa-admin.websanova.com) |

## Flows

The starter-flows repo is a separate set of feature specs, implementation-agnostic so one spec covers both the API and the app. Nothing here depends on it, but if you point your `CLAUDE.local.md` at a local clone, the `/flow` command will compare any flow against this repo and tell you what's built, what's missing, and what the app expects it to expose.

## Notes

- The commands are experimental and the format is still being tinkered with. The main idea is to not have the agent run wild without explicit go aheads and to remove reptitive explanations to help streamline workflow. Open to suggestions and feedback.

## Features

* **API Structure** - JSON-only with consistent response shapes, admin endpoints on `/admin`, and translatable strings.
* **Public Settings** - Centralized app settings on a public `/settings` endpoint for reuse across clients.
* **Rate Limiting** - Global throttle on all routes, stricter on auth routes keyed by email + IP.
* **Search (Fulltext)** - Drop-in fulltext search for any model that stays in sync automatically, used on users out of the box.
* **Token Auth (Sanctum)** - Register, login, logout, refresh, and password reset with configurable token expiration.
* **Account Verification** - Code-based, multi-channel verification (email wired, SMS scaffolded), each channel set to disabled, auto, or required.
* **Account Self-Management** - Profile, password, avatar, email change, locale/timezone, and self-delete with a grace period.
* **Preferences** - Saved client display state with defaults in config, stored partial so adding or removing one needs no migration, and scoped to keep admin preferences out of the app payload.
* **User Delete & Restore** - Soft delete, anonymize, or hard delete, with admin restore, auto-restore on login, and scheduled prune.
* **Sample CRUD (Bookmarks & Tags)** - Reference resources showing relationships, ownership scoping, filtering, sorting, and pagination.
* **Plans** - Freemium, trial, or required modes with prices synced from the provider and optional automatic tax.
* **Subscriptions** - Subscribe, swap, cancel, and resume with multiple billing intervals, client-built Stripe checkout, and enforced feature limits.
* **Billing Providers (Cashier/Stripe)** - Stripe via Cashier out of the box, swappable without touching the rest of the app.
* **Promotion Codes** - Validated against the provider and entered in checkout, with admin apply/clear on existing subscriptions.
* **Webhooks (Cashier/Stripe)** - Idempotent webhooks that back up client sync and catch outside changes, with local dev needing no tunnel.
* **Roles and Permissions (Spatie)** - Super and admin roles with coarse permissions and per-target checks.
* **Stats** - Scheduled stats grouped by resource with date range breakdowns and per-plan subscription stats.
* **Mail (Mailpit)** - Any SMTP provider, with a local mail catcher and test command for dev.
* **Notifications** - Database-backed in-app notifications plus email for account and plan lifecycle events.
* **Backups (Spatie)** - Daily database backups with scheduled cleanup.
* **Localization (i18n)** - Multi-locale responses and per-user localized emails, add a language by dropping in a lang folder.
* **File Storage (S3)** - Off-server storage for AWS or DigitalOcean Spaces, with avatars cropped and resized on upload.
* **Dev Environment** - Dockerized with MySQL, Redis, Mailpit, and Stripe CLI, plus a helper script and Pest test suite.

For the full breakdown, see the [features overview](https://websanova.com/docs/starter-api/intro/features).

## License

MIT - see [LICENSE](LICENSE).

---

Built and maintained by Rob at [Websanova](https://www.websanova.com). I take freelance and contract work, including MVP projects built on the Starters. Check out the [hire page](https://www.websanova.com/hire) for more info.
