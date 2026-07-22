<?php

use App\Support\Timezone;

uses()->group('support.timezone');

test('identifiers returns the supported timezone list', function () {
    expect(Timezone::identifiers())
        ->toBeArray()
        ->not->toBeEmpty()
        ->toContain('America/New_York');
});

test('options returns a value/label set with underscores converted', function () {
    expect(Timezone::options())
        ->toBeArray()
        ->not->toBeEmpty()
        ->toContain(['value' => 'America/New_York', 'label' => 'America / New York']);
});
