# Docker Setup

Build the images.

```bash
./dev build
```

Build through `./dev` rather than `docker compose build` directly. It passes your `USER_ID` and `GROUP_ID` into the build, so files written inside the container stay owned by you. The raw command bakes the 1000 defaults instead.

Start the containers.

```bash
./dev up
```

The container boots but won't serve the app yet. Dependencies aren't installed automatically to avoid modifying `composer.lock` without your say-so. Install them and restart.

```bash
./dev install
./dev restart
./dev artisan migrate
./dev artisan plans:sync
```

Migration creates roles, permissions, and three users, `super@starter.com`, `admin@starter.com` and `user@starter.com`, all with `testtest`. None require a password change on first login. See [Startup Guide](startup-guide.md) for production.

On subsequent runs, just start the containers. The entrypoint detects `vendor/` and serves automatically.

```bash
./dev up
```

App runs at `http://localhost:8000`.

## Services

| Service | Detail |
| --- | --- |
| `php` | The app, on port 8000 |
| `mysql` | MySQL 8.0 on port 3306, database `laravel`, user `laravel`, password `password` |
| `redis` | Redis 7 on port 6379 |
| `mailpit` | Mailpit, catching outgoing mail on port 1025, inbox at `http://localhost:8025` |
| `stripe` | Stripe CLI, forwarding webhooks to `php:8000/stripe/webhook` |

The Stripe container needs `STRIPE_SECRET` in your `.env` or it won't authenticate. See [Stripe Guide](stripe-guide.md).

## Viewing logs

The dev server runs inside the container, so its output goes to the container logs, not your terminal. Follow everything at once.

```bash
./dev logs
```

Or narrow it to one service, which is usually what you want.

```bash
./dev logs php
./dev logs stripe
```

That gets you the PHP server output, Stripe CLI webhook forwarding, and MySQL and Redis startup. What it does not get you is Laravel's own log. That still writes to `storage/logs/laravel.log` on disk, since `LOG_CHANNEL` is `stack` with `LOG_STACK=single`. So a failed request shows up as a 500 in the container logs and the actual trace lives in the file.

If you'd rather have both in one place, set `LOG_STACK=stderr` and Laravel's log folds into the container output alongside everything else.

## Shortcuts

| Command | Runs |
| --- | --- |
| `./dev build` | Build the images with your user and group ids |
| `./dev up` | Start the containers, recreating them |
| `./dev down` | Stop the containers |
| `./dev restart` | Down, then up |
| `./dev install` | `composer install` |
| `./dev update` | `composer update` |
| `./dev dump` | `composer dump-autoload` |
| `./dev composer <args>` | Any other composer command |
| `./dev artisan <args>` | Any artisan command |
| `./dev php <args>` | Any php command |
| `./dev mysql <args>` | The mysql client in the mysql container |
| `./dev sh <service>` | A shell in the given service |
| `./dev logs [service]` | Follow logs, all services or one |
