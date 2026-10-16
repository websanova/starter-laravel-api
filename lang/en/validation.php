<?php

return [

    'bookmark' => [
        'url_duplicate' => 'You have already saved this URL.',
    ],
    'price' => [
        'interval_mismatch' => 'The Stripe price bills on a different interval.',
        'missing_lookup_key' => 'A Stripe lookup key is required before syncing.',
        'not_found' => 'No active Stripe price matches this lookup key.',
        'sync_failed' => 'The Stripe prices could not be retrieved.',
    ],
    'promotion_code' => [
        'expired' => 'The promotion code has expired.',
        'invalid' => 'The promotion code is invalid.',
        'max_redemptions' => 'The promotion code has reached its maximum number of redemptions.',
    ],
    'tag' => [
        'name_duplicate' => 'You already have a tag with this name.',
        'name_format' => 'Must start with a letter or number. Allowed: letters, numbers, spaces, dots (.), hyphens (-), plus (+), hash (#).',
    ],

];
