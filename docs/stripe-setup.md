# Stripe Setup

Billing is handled through Laravel Cashier with Stripe setup out of the box.

```env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
```

## Plans & Prices

Plans should map to products in Stripe along with it's prices and intervals accordingly. They map via the Stripe `lookup_key` which avoids any issues while prices are being managed in Stripe and not yet synced app side. it also means we don't have to map any actual Stripe product or pirce ids manually.

Archiving prices before a sync should be avoided as there are edge cases were a user may happen to have loaded up an old price before a sync. This should be quite rare but a minimum 10 minute window should completley avoid this issue. They can then be archived to be fully phased out of usage.

The initial migrations contain a plan seed, however this will only run once in production. After that any additional plans and prices that are added through migrations or other means can be synced via artisan commands.

```bash
./dev artisan plans:sync
```

## Webhooks

The API registers a webhook endpoint at `POST /stripe/webhook`. You'll need to create a matching webhook endpoint in the Stripe dashboard, point it at that URL, and copy the signing secret into your `.env`:

```env
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

To test in local dev you will need to setup a listener via the stripe cli.
