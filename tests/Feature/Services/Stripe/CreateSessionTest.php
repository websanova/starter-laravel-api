<?php

uses()->group('service.stripe.create-session');

use App\Services\Stripe\CreateSessionService;

test('every supported user locale has a stripe locale mapping', function () {
    $map = (new ReflectionClass(CreateSessionService::class))->getConstant('LOCALES');

    foreach (config('user.supported_locales') as $locale) {
        expect($map)->toHaveKey($locale);
    }
});
