<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Cashier;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\SetupIntent as StripeSetupIntent;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Helpers for the tests in the stripe group, which run against a real Stripe
 * sandbox rather than a fake. See docs/testing.md for the sandbox setup.
 */
function stripeSandboxUser(): User
{
    $user = User::factory()->create();

    $user->createAsStripeCustomer(['metadata' => ['suite' => 'feature']]);

    stripeSandboxTrack($user->stripe_id);

    return $user;
}

function stripeSandboxCard(User $user, string $card = 'pm_card_visa'): StripePaymentMethod
{
    return Cashier::stripe()->paymentMethods->attach($card, ['customer' => $user->stripe_id]);
}

/**
 * A setup intent in the state the client leaves one in, confirmed and carrying
 * a card. The browser does the confirming in the real flow, so it is done here
 * on the create rather than in two calls.
 */
function stripeSandboxSetupIntent(User $user, StripePaymentMethod $paymentMethod, bool $confirm = true): StripeSetupIntent
{
    return Cashier::stripe()->setupIntents->create([
        'customer' => $user->stripe_id,
        'payment_method' => $paymentMethod->id,
        'usage' => 'off_session',
        'confirm' => $confirm,
    ]);
}

/**
 * Customers created during a test, held until it ends. Deleting a customer
 * takes its cards and subscriptions with it, so this is the whole cleanup.
 */
function stripeSandboxTrack(?string $customer = null): array
{
    static $customers = [];

    if ($customer) {
        $customers[] = $customer;

        return $customers;
    }

    $tracked = $customers;

    $customers = [];

    return $tracked;
}

function stripeSandboxFlush(): void
{
    foreach (stripeSandboxTrack() as $customer) {
        Cashier::stripe()->customers->delete($customer);
    }
}
