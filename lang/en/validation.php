<?php

return [

    'tag_name_format' => 'Must start with a letter or number. Allowed: letters, numbers, spaces, dots (.), hyphens (-), plus (+), hash (#).',

    'promotion_code.invalid' => 'The promotion code is invalid.',
    'promotion_code.expired' => 'The promotion code has expired.',
    'promotion_code.max_redemptions' => 'The promotion code has reached its maximum number of redemptions.',

    'price.missing_product' => 'A Stripe product ID is required before syncing.',
    'price.sync_failed' => 'The Stripe product could not be retrieved.',
    'price.no_default_price' => 'The Stripe product has no default price set.',

];
