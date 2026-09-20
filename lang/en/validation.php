<?php

return [

    'tag.name_format' => 'Must start with a letter or number. Allowed: letters, numbers, spaces, dots (.), hyphens (-), plus (+), hash (#).',
    'tag.name_duplicate' => 'You already have a tag with this name.',

    'bookmark.url_duplicate' => 'You have already saved this URL.',

    'promotion_code.invalid' => 'The promotion code is invalid.',
    'promotion_code.expired' => 'The promotion code has expired.',
    'promotion_code.max_redemptions' => 'The promotion code has reached its maximum number of redemptions.',

    'price.missing_lookup_key' => 'A Stripe lookup key is required before syncing.',
    'price.sync_failed' => 'The Stripe prices could not be retrieved.',
    'price.not_found' => 'No active Stripe price matches this lookup key.',
    'price.interval_mismatch' => 'The Stripe price bills on a different interval.',

];
