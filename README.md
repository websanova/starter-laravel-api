# Starter Laravel API

A Laravel API starter with a full auth system, account management, and production-ready integrations out of the box. No frontend, no Blade, no Vite. Just a clean JSON API.

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

- **Integrations**
  - Laravel Cashier (Stripe)
  - S3-compatible file storage

- **Dev Environment**
  - Dockerized (PHP-FPM + MySQL)
  - `./dev` script for container commands
  - Pest test suite

## Setup

Build and start the containers.

```bash
docker compose up -d --build
```

The container boots but won't serve the app yet. Dependencies aren't installed automatically to avoid modifying `composer.lock` without your say-so. Install them and restart.

```bash
./dev composer install
docker compose restart php
./dev artisan migrate
./dev artisan db:seed
```

Migration creates roles, permissions, and a super user (`super@starter.com` / `initinit`). The super account requires a password change on first login. Seeding adds dev users (`admin@starter.com`, `user@starter.com`, both `testtest`).

On subsequent runs, just start the containers. The entrypoint detects `vendor/` and serves automatically.

```bash
docker compose up -d
```

App runs at `http://localhost:8000`.

## Dev Commands

```bash
./dev artisan migrate
./dev artisan make:model Foo -m
./dev composer install
./dev composer require foo/bar
./dev php -v
docker compose down
docker compose logs php
```