# Stripe Setup

Billing is handled through Laravel Cashier with Stripe. Add your Stripe API keys to `.env`:

```env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
```

## Subscriptions

Create a product in the Stripe dashboard (e.g. "Pro") with two recurring prices, one monthly and one yearly. Copy the price IDs into your `.env`:

```env
STRIPE_PRICE_PRO_MONTHLY=price_xxx
STRIPE_PRICE_PRO_YEARLY=price_xxx
```

Then run the plan seeder to create the local plan records:

```bash
./dev artisan db:seed --class=PlanSeeder
```

## Coupons

Coupons and promotion codes are optional. Create them in the Stripe dashboard under Products > Coupons. The API validates promotion codes against Stripe on the fly, so no local configuration is needed beyond having a valid `STRIPE_SECRET`.

## Webhooks

Webhooks keep local subscription data in sync when changes happen directly in Stripe (cancellations, payment failures, trial expirations, etc.). The API registers a webhook endpoint at `POST /stripe/webhook`. You'll need to create a matching webhook endpoint in the Stripe dashboard, point it at that URL, and copy the signing secret into your `.env`:

```env
STRIPE_WEBHOOK_SECRET=whsec_xxx
```
