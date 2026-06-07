# Stripe Setup

Subscriptions require a Stripe account with products and prices configured.

1. Create a product in the Stripe dashboard (e.g. "Pro")
2. Add two prices: one monthly recurring, one yearly recurring
3. Copy the price IDs (`price_xxx`) into your `.env`:

```env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

STRIPE_PRICE_PRO_MONTHLY=price_xxx
STRIPE_PRICE_PRO_YEARLY=price_xxx
```

4. Run the plan seeder to create the plan records with the Stripe price IDs:

```bash
./dev artisan db:seed --class=PlanSeeder
```

Coupons and promotion codes are optional. If you want discount support, create them in the Stripe dashboard under Products > Coupons. The API validates promotion codes against Stripe on the fly, so no local configuration is needed beyond having a valid `STRIPE_SECRET`.
