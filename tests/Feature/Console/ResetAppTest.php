<?php

uses()->group('console.reset-app');

use Illuminate\Support\Facades\Process;

test('reset is refused outside the local environment', function () {
    Process::fake();

    $this->artisan('app:reset')
        ->expectsOutputToContain('app:reset can only run in the local environment.')
        ->assertFailed();

    Process::assertNothingRan();
});
