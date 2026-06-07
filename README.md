# Starter Laravel API

A Laravel API starter with a full auth system, account management, and production-ready integrations out of the box. No frontend, no Blade, no Vite. Just a clean JSON API.

## Docs

Full documentation at [websanova.com/docs/starter-api](https://websanova.com/docs/starter-api).

- [Docker Setup](docs/docker-setup.md)
- [Stripe Setup](docs/stripe-setup.md)
- [Dev Commands](docs/dev-commands.md)

## Features

- **Token Auth (Sanctum)**
  - Register, login, logout, token refresh
  - Configurable token expiration
  - Soft-deleted accounts auto-restore on login during grace period

- **Email Verification**
  - Code-based, not signed URLs, works with any client
  - Three modes: disabled, auto, required
  - Optional grace period before enforcement
  - Multi-channel ready (email now, SMS later)

- **Password Reset and Email Change**
  - Token-based two-step flows with frontend URL redirect
  - All sessions revoked on password reset
  - Email change confirmed via new inbox, old stays active until confirmed

- **Account Self-Management (`/account`)**
  - Profile, password, avatar (S3-compatible storage)
  - Soft delete with configurable grace period
  - Scheduled prune with anonymize or hard delete strategy

- **Delete & Restore**
  - Soft delete, anonymize, and hard delete strategies
  - `TrashedFilter` enum with `HasTrashedScope` trait for filtering trashed records
  - Grace period with auto-restore on login
  - Scheduled prune command with configurable strategy

- **Search (Fulltext)**
  - Searchable trait with MySQL fulltext index on a denormalized keywords column
  - Observer auto-syncs keywords when searchable fields change
  - Models override `forSearch` to add column-specific clauses (e.g. email)

- **Sample CRUD (Bookmarks)**
  - Bookmarks with optional flat categories
  - Demonstrates: relationships, ownership scoping, filtering, nullable foreign keys

- **Rate Limiting**
  - Global throttle on all routes
  - Stricter throttle on auth routes, keyed by email + IP

- **Route Structure**
  - `/auth` for authentication, `/account` for self-management, `/admin` for resource management
  - API Resources for all responses
  - JSON-only, lang files for all user-facing strings

- **Roles and Permissions (Spatie)**
  - Two roles: super (god mode), admin (manages users)
  - Coarse permissions with policy-based target checks
  - Admin can't touch super users, can't delete other admins
  - Forced password reset flow with temp password email
  - Seeded super user on first migrate

- **Plans & Subscriptions (Cashier/Stripe)**
  - Plan model with features JSON (countable limits and boolean flags)
  - Three subscription modes: freemium, trial, required
  - Subscribe, swap, cancel, resume flows for both account and admin
  - Complimentary plans for admin-assigned access without Stripe billing
  - Plan feature limits enforced in request authorization
  - Plans cached with auto-invalidation on mutation

- **File Storage (S3)**
  - Off-server storage, AWS and DigitalOcean Spaces ready
  - Image processing via Intervention
  - `StoragePath` enum for centralized path management

- **Dev Environment**
  - Dockerized (PHP-FPM + MySQL)
  - `./dev` script for container commands
  - Pest test suite