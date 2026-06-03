# Starter Laravel API

A minimal Laravel API boilerplate with authentication, account management, and common integrations pre-wired. No frontend, no Blade, no Vite. Just a clean JSON API.

## Features

- Token authentication (Sanctum)
- Register, login, logout, token refresh
- Email verification (code-based)
- Forgot/reset password
- Email change with confirmation
- `/me` routes for account self-management (profile, password, avatar)
- Role and permission system (Spatie)
- Stripe billing (Cashier)
- S3-compatible file storage
- Pest test suite
- Dockerized development environment

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
```

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