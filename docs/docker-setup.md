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
./dev artisan db:seed
./dev artisan plan:sync
```

Migration creates roles, permissions, and a super user (`super@starter.com` / `initinit`). The super account requires a password change on first login. Seeding adds dev users (`admin@starter.com`, `user@starter.com`, both `testtest`).

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
| `stripe` | Stripe CLI, forwarding webhooks to `php:8000/stripe/webhook` |

The Stripe container needs `STRIPE_SECRET` in your `.env` or it won't authenticate. See [Stripe Setup](stripe-setup.md).

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
