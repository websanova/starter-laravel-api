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
> docker compose exec php php artisan plans:sync
> ./dev artisan plans:sync
```

## Webhooks (Docker)

The `stripe` container handles the webhooks, and its secret comes from the logs.

```bash
> docker compose logs -f stripe
> ./dev logs stripe
```

```env
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

Clear the cache if necessary.

```bash
> docker compose exec php php artisan config:clear
> ./dev artisan config:clear
```

## Webhooks (Deploy)

The API registers a webhook endpoint at `POST /stripe/webhook`. Stripe needs a matching endpoint pointed at that URL, either with artisan or by hand in the dashboard, ticking off the events listed in `config/cashier.php`. Copy the signing secret into that environment's `.env`.

```bash
> docker compose exec php php artisan cashier:webhook
> ./dev artisan cashier:webhook
```

The event list adds to Cashier's defaults, because the API listens for events Cashier doesn't handle on its own.

The command only ever creates, it never updates an existing endpoint, so this is an init step. Adding an event later means deleting the endpoint and creating it again, and so does a Cashier upgrade, since the API version is fixed when the endpoint is created.
