# Startup Guide

These are the minimal steps to get the API going with Stripe and running tests. Check the individual guides for more details.

## API

Copy the example env file.

```bash
cp .env.example .env
```

Build, start and install dependencies.

```bash
./dev build
./dev up
./dev install
./dev restart
```

Generate the app key and migrate.

```bash
./dev artisan key:generate
./dev artisan migrate
```

The API runs at `http://localhost:8000`. Migration seeds `super@starter.com`, `admin@starter.com` and `user@starter.com`, all with `testtest`. None of them require a password change on first login, to keep things simple. After a production deploy, update the super password right away, or set `is_password_reset_required` to `true` in `RoleAndPermissionSeeder` before migrating to force it.

## Docker

After the first run, starting the containers is all you need. The app serves on its own once `vendor/` exists.

```bash
./dev up
./dev down
./dev restart
```

The stripe container won't authenticate until the Stripe section is done, which doesn't affect the API.

Follow logs for all services or just one, and run commands inside the php container.

```bash
./dev logs
./dev logs php
./dev artisan <args>
./dev composer <args>
```

See [Docker Setup](docker-setup.md) for services and the full list of shortcuts.

## Stripe

Add your test mode keys, then restart so the stripe container picks up the secret.

```env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
```

```bash
./dev restart
```

Grab the webhook signing secret from the stripe container logs.

```bash
./dev logs stripe
```

```env
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

Create a product in Stripe with prices using the lookup keys `pro_monthly` and `pro_yearly`, then sync.

```bash
./dev artisan plans:sync
```

See [Stripe Setup](stripe-setup.md) for lookup keys, archiving prices and deploy webhooks.

## Testing

```bash
./dev artisan test
```

The `stripe` group is excluded by default and runs against a dedicated sandbox configured in `.env.testing`.

```bash
./dev artisan test --group stripe
```

See [Testing](testing.md) for sandbox setup, lookup keys and cleanup.
