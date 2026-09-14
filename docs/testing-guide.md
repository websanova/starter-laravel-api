# Testing

Most of the suite runs offline against an in-memory SQLite database and needs no setup beyond `php artisan test`.

The exception is the `stripe` group. Those tests talk to a real Stripe sandbox rather than a fake, because the things worth testing here are state changes that a fake cannot prove: a card being attached, a default moving, an old card being detached. They are excluded from the default run and only execute when asked for explicitly.

```bash
php artisan test --group stripe
```

## Sandbox setup

A sandbox is a full isolated copy of a Stripe account with its own keys, data and webhook endpoints. Use one dedicated to this suite rather than the test mode you develop against, so a cleanup bug can never touch data you created by hand. When it fills up, delete the whole sandbox and make another.

1. Create a sandbox from the Stripe dashboard.
2. Copy its publishable key, secret key and webhook signing secret.
3. Create one product in that sandbox with two recurring prices, monthly and yearly.

## Lookup Keys

Two prices need lookup keys set on the Stripe side, `pro_monthly` and `pro_yearly`. Those are the same keys `PlanSeeder` writes, and the tests resolve prices by looking them up rather than by id, so nothing works out of the box until they match. Anything else in the sandbox is ignored.

## Environment

The `.env.testing` is gitignored here because this starter is open source and sandbox credentials cannot go into a public repo. On a project built from it that lives in a private repo, drop the entry from `.gitignore` and commit the file so everyone works against the same sandbox.

Copy `.env.example` to `.env.testing`. Laravel loads that file instead of `.env` when `APP_ENV` is `testing`, which `phpunit.xml` already sets, so it replaces `.env` rather than merging with it and needs everything the app boots with, `APP_KEY` included. The `<env>` entries in `phpunit.xml` still win over the file, so the database, cache and mail drivers stay as they are configured there.

The values that matter for the sandbox:

```
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

## Cleanup

Tests create a customer per test and delete it afterwards, which takes its cards and subscriptions with it. Customers are tagged with a `suite` metadata key, so a run that crashes before cleanup can be swept afterwards by searching the sandbox on that key.
