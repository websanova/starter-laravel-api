# Subscription Flows

The subscription mode can change at any time, so these flows are drawn as chunks rather than one path from registration to a live subscription. Each chunk starts from a state the user is already in, reads the mode fresh, and ends. Nothing carries between them, which is what makes the whole thing survivable in production.

Existing users are grandfathered in. A stamped trial stays stamped, an active subscription keeps running, and no script or migration is needed to flip a mode. The tradeoff is that a handful of users registering around the change may end up with a few free days they would not otherwise have got. That is fine, take it and move on.

## Registration

No plan is assigned at registration in any mode. The `plan` relation falls back to the free plan, so every path below lands on it, freemium is just the only mode where the gate lets you use it.

```mermaid
flowchart LR
    A[Register] --> B{subscription.mode}

    B -->|freemium| C[No stamp]
    B -->|required| C

    B -->|trial| D{subscription<br/>.require_card_upfront}

    D -->|off| E["Stamp users.trial_ends_at<br/>(now + subscription.trial_days)"]
    D -->|on| C

    C -->|freemium| F[Access granted]
    C -->|required| G[Locked<br/>must subscribe]
    C -->|trial| H[Locked<br/>trial starts at first subscribe]

    E --> I[Access granted<br/>for subscription.trial_days]
```

## Subscribe with a Stamp

The mode drops out entirely here, all three converge. A stamped date is the only thing that decides the outcome, so switching modes never takes a trial away from someone who already has one.

```mermaid
flowchart LR
    A["Registered, unsubscribed<br/>users.trial_ends_at set"] --> B{subscription.mode}

    B -->|freemium| C{trial_ends_at<br/>in future?}
    B -->|trial| C
    B -->|required| C

    C -->|yes| D["Stripe trials until trial_ends_at<br/>(remaining days carry over)"]
    C -->|no| E[No trial<br/>charged immediately]
```

## Subscribe without a Stamp

The past subscriptions check is what stops a user farming trials by cancelling and resubscribing.

```mermaid
flowchart LR
    A["Registered, unsubscribed<br/>no stamp"] --> B{subscription.mode}

    B -->|freemium| E[No trial<br/>charged immediately]
    B -->|required| E

    B -->|trial| C{subscription<br/>.require_card_upfront}

    C -->|off| E
    C -->|on| D{Any past<br/>subscriptions?}

    D -->|yes| E
    D -->|no| F["Fresh trial<br/>(subscription.trial_days)"]
```
