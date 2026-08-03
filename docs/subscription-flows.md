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

## New Subscription (Stripe)

A new subscritpion can be created on a fresh account and also other scenarios, like a cancel with grace period finished. The idea is to be gated in any scenario where there is some viable subscription even if it's in some past_due or unpaid status. Regardless assuming a new subscription creation allowed they will all follow this flow.

```mermaid
flowchart LR
    A["POST /subscription<br/>{plan_id, interval}"] --> B{Existing<br/>subscription?}

    B -->|none| F1["Create Stripe subscription<br/>payment_behavior: default_incomplete"]
    B -->|branch 2| X[TBD]
    B -->|branch 3| Y[TBD]

    F1 --> F2["Local row saved<br/>status: incomplete"]
    F2 --> F3["Return client_secret<br/>from first invoice PI"]
    F3 --> F4["Client mounts Payment Element"]
    F4 --> F5["User submits card<br/>stripe.confirmPayment, 3DS if required"]
    F5 --> F6["Stripe charges first invoice"]
    F6 --> F7["Webhook: status -> active"]
    F7 --> F8["Promote card to customer default<br/>fills brand + last four"]
```

## Plan Selection

The plan cards resolve their own button label from the user's state, and the mode never enters into it. Mode decides what state the user turns up in, whether they hold a plan at all and whether the free plan is even in the list, and from there the resolution is the same everywhere. Worth knowing when reading the client, because the mode checks you would expect to find in the resolver are not there.

Identity comes from the plan id and direction comes from the tier. Moving between monthly and yearly on the same plan is a billing change rather than a tier move, so it gets its own action instead of being folded into an upgrade.

```mermaid
flowchart LR
    A[Plan card] --> B{Same plan<br/>as the user's?}

    B -->|yes| C{On grace<br/>period?}
    B -->|no| F{Has a<br/>subscription?}

    C -->|yes| R[Resume]
    C -->|no| D{Interval<br/>differs?}

    D -->|yes| S[Switch]
    D -->|no| E["Current<br/>(button disabled)"]

    F -->|yes| J{Card tier vs<br/>user plan tier}
    F -->|no| G{Card tier > 0?}

    J -->|higher| K[Upgrade]
    J -->|lower, tier 0| L[Cancel]
    J -->|lower| M[Downgrade]
    J -. equal<br/>(different plan) .-> I@{ shape: text, label: "Unreachable" }

    G -->|yes| H[Subscribe]
    G -. no .-> G2@{ shape: text, label: "Unreachable" }

    R --> R2[[Payment]]
    S --> S2[[Payment]]
    K --> K2[[Payment]]
    L --> L2[[Cancel]]
    M --> M2[[Payment]]
    H --> H2[[Payment]]
```

The subscription check sits ahead of the tier comparison on purpose. It catches the freemium user sitting on the free plan and the required-mode user carrying no plan at all in one branch, neither of whom has ever paid, so neither should be told they are upgrading. Upgrade then only ever means moving between two paid plans.

Having a subscription implies a paid tier, so the right-hand branch never sees a free user. Both dead ends need two plans sharing a tier, which nothing in the product creates but the schema does not forbid either. The resolver returns `select` there rather than guessing at a direction, which is why the branches stay in the code.

### Trial banner

The trial pitch above the cards is the only place the mode is read on this page. A trial is offered once, and the trial object stays set after it ends, so its absence is what marks a user as never having taken one.

```mermaid
flowchart LR
    A[Plans page] --> B{subscription.mode}

    B -->|freemium| C[Standard note]
    B -->|required| C

    B -->|trial| D{Has a<br/>subscription?}
    D -->|yes| C
    D -->|no| E{trial set?}
    E -->|yes| C
    E -->|no| F[Trial banner<br/>with day count]
```