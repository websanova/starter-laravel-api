<?php

namespace App\Services\Stripe;

use App\Contracts\SyncSubscriptionProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Subscription as StripeSubscription;

class SyncSubscriptionService implements SyncSubscriptionProvider
{
    public function __construct(private ReplacePaymentMethodService $replacePaymentMethod)
    {
    }

    /**
     * Write every local row a completed checkout session accounts for, all of
     * it read off the one session. The subscription and the card that paid for
     * it are only known to Stripe until this runs, since neither existed before
     * the confirm.
     *
     * Shared by the client call and the webhook, which differ only in what
     * prompts them. Everything is an updateOrCreate against ids Stripe already
     * settled, so whichever lands second is inert rather than a second write.
     */
    public function handle(User $user, string $sessionId): ServiceResult
    {
        if (!$user->hasStripeId()) {
            return ServiceResult::error('nothing_to_sync');
        }

        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription.default_payment_method'],
            ]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        /**
         * The id came off the request, so it is only trustworthy once it is
         * shown to belong to this customer. A session that does not is treated
         * as nothing to sync rather than named, since the caller has no
         * business knowing whether it exists.
         */
        if ($session->customer !== $user->stripe_id) {
            return ServiceResult::error('nothing_to_sync');
        }

        /**
         * The subscription and its invoice do not exist until the session
         * completes, so an open one has nothing to read yet. The user is still
         * on the page with the elements mounted in that case, and confirming
         * again is what moves it.
         */
        if ($session->status !== Session::STATUS_COMPLETE || !$session->subscription) {
            return ServiceResult::error('nothing_to_sync');
        }

        $paymentMethod = $session->subscription->default_payment_method;

        /**
         * Outside the transaction because it reaches Stripe to point the
         * customer at the same card, which a rollback could not undo. It writes
         * the brand and last four on the way through.
         */
        if ($paymentMethod) {
            $user->updateDefaultPaymentMethod($paymentMethod);
        }

        DB::transaction(function () use ($user, $session) {
            $this->commitSubscription($user, $session->subscription);

            $this->commitUser($user);
        });

        /**
         * After the transaction because the repoint reads the subscription row
         * it just wrote, and outside it because it reaches Stripe.
         */
        if ($session->subscription->status === StripeSubscription::STATUS_TRIALING) {
            try {
                $this->commitTrialPaymentMethod($user, $paymentMethod);
            } catch (ApiErrorException $e) {
                return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
            }
        }

        return ServiceResult::success();
    }

    /**
     * Write the subscription row and the item that prices it. Cashier's own
     * webhook writes the same row off customer.subscription.created, this is
     * only what saves the user waiting for it.
     */
    protected function commitSubscription(User $user, StripeSubscription $stripeSubscription): void
    {
        $item = $stripeSubscription->items->data[0];

        $subscription = $user->subscriptions()->updateOrCreate(
            ['stripe_id' => $stripeSubscription->id],
            [
                'type' => 'default',
                'stripe_status' => $stripeSubscription->status,
                'stripe_price' => $item->price->id,
                'quantity' => $item->quantity ?? null,
                'trial_ends_at' => $stripeSubscription->trial_end ? Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                'ends_at' => null,
            ],
        );

        $subscription->items()->updateOrCreate(
            ['stripe_id' => $item->id],
            [
                'stripe_product' => $item->price->product,
                'stripe_price' => $item->price->id,
                'quantity' => $item->quantity ?? null,
            ],
        );
    }

    /**
     * Point the customer and the subscription at the card a trial was confirmed
     * with. Nothing paid the $0 invoice, so there is no charge for Stripe to
     * make either of them default off the back of, and they are set by hand.
     *
     * The subscription's own card is used when it carries one. Otherwise the
     * card the user just entered is the newest one attached to the customer.
     */
    protected function commitTrialPaymentMethod(User $user, ?StripePaymentMethod $paymentMethod): void
    {
        $paymentMethodId = $paymentMethod?->id ?? $user->paymentMethods()->first()?->id;

        if (!$paymentMethodId) {
            return;
        }

        $this->replacePaymentMethod->handle($user, $paymentMethodId);
    }

    /**
     * Write the plan the new subscription entitles the user to. Nothing about
     * the address is stored here, the customer at the provider holds it.
     */
    protected function commitUser(User $user): void
    {
        $user->load('subscriptions');

        $user->fillPlan()->save();
    }
}
