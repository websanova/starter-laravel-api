# Stripe Subscription Flow

## Deferred Flow

With deferred flow the payment element needs to get it's amount constantly updated to match up with the intent it will eventually create. This can be a bit cumbersome when taxes and promo codes are involved since it requires always fetching the appropriate amount from the API (API should be source of truth, do not calculate locally) to ensure it will match the intent amount later. Note that the intent is auto computing this amount on it's end.

The below does not even consider trials which adds additional complications with how the intent is setup. I've left it out since it's already quite complicated as it is.

* Element mounts with amount & currency in "payment" mode. For example `stripe.elements({ mode: 'payment', amount: 3000, currency: 'usd' })`.
  * `loadStripe()` downloads `js.stripe.com/v3` if it isn't already on the page.
  * `stripe.elements({ mode, amount, currency })` which builds the Elements object locally (no network calls here).
  * `paymentElement.mount(target)` creates the iframe / payment element.
* If there was a tax amount we would have to fetch the proper total from the API and then update it with `elements.update({ amount: <new price> })`. Or just do the call before the `stripe.elements` call as a frist step to avoid the update. However, note that getting the tax amount can be quite complicated and will likely require a call to Stripe since it can depend on multiple factors (more below).
* Also if there are promo codes. This would be sorted out here as well. The promo code would be entered and validated on API side (with the plan and interval included in the request). The response would included the updated amount (with tax) and then call `elements.update({ amount: 3300 })`.
* At this point though, no intent or subscription or anything has been created on the API side. Everything gets initiated when the user his the "submit" button on the page.
* Note that you can get fancy all you like with how the stripe amount gets updated. For instance you could do some kind of "get amount" call only once with any promo codes or additional amount modifiers, do the `elements.update` and then get the intent with the same amount, etc. Regardless, they have to match.
* Assuming the proper amount has been set in above steps, `elements.submit()` runs first, before anything else. This sends the card data to Stripe's servers, returns ok or an error. If it errors, stop here, nothing else runs.

* Then fetch the intent, sending `{ plan, interval, promo_code, etc }` to the API. The amount will get auto computed on Stripe's side and will have to match the amount that was manually set/updated in the steps above. This follows like so:

  * Loads the user's Stripe customer id from your DB.
  * If there isn't one, creates the customer on Stripe. Saves the returned customer id to your users table.
  * Creates the Subscription on Stripe with customer (stripe id), the plan's price id and payment_behavior as `default_incomplete`. Stripe returns the subscription with an id, status incomplete, its first invoice which auto computes the amount, and a PaymentIntent on that invoice.
  * If there is tax here is where it gets complicated since it's computed on a few different variables:
    * Your registrations - which jurisdictions you've told Stripe you collect tax in, set in the Dashboard.
    * The customer's location - address on the Stripe customer object. Stripe can also infer from IP (customer.tax.ip_address) when there's no address, but that's a weaker signal and some jurisdictions won't accept it.
    * The product's tax code - `tax_code` on the Stripe product, which decides the rate category (SaaS is taxed differently to physical goods).
  * Writes your local subscription row now that the Stripe id exists - stripe sub id, plan, interval, status incomplete.
  * Returns the PaymentIntent's client secret to the client app. The amount on it must be exactly matching what the element was mounted/updated with.
* Then `stripe.confirmPayment({ elements, clientSecret })` fires immediately, same click handler, next line after the secret comes back. No second button press, no user interaction in between, the whole chain from the subscribe click runs uninterrupted with the button held in its pending state.
* Response is success / error / 3DS. 3DS either runs in a dialog and resolves inline, or sends the browser away to the bank and back to your return url. Either way you end up at the same place - a settled intent.
* On success, the user needs reloading with their new subscription. But your API doesn't know about it yet, nothing in the chain above told it the payment landed. Only the webhook does.
* Assuming success, Stripe fires off the webhook. Your backend looks up the local row by Stripe sub id and flips it to active. This is asynchronous and has no fixed timing, it can land before confirmPayment even resolves in the browser, or seconds after.
* Reload the auth user and check for the subscription. oll this, a hit (check for is_subscribed true or something) means the webhook arrived and the API picked it up. Give up after a ceiling rather than spinning forever.
* Note taht you are at the mercy of the webhook running correctly. If it's late, fails, or never arrives, the user sits in a pending state. Show a pending state for that, and if it fails outright you need a manual sync command to reconcile against Stripe. This should be rare, but it may happen if your API has an issue and drops webhook attempts (or other scenarios).
* Assuming the webhook finally processes and we get our flag (is_subscribed or whatever), take the success action - redirect to billing, a success page, wherever.