<?php

uses()->group('console.prune-deleted-users');

use App\Enums\AccountPruneStrategy;
use App\Models\User;

test('delete strategy hard-deletes users past grace period', function () {
    config(['auth.delete.grace_period' => 30]);
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Delete]);

    $user = User::factory()->create();
    $user->delete();
    $user->forceFill(['deleted_at' => now()->subDays(31)])->save();

    $this->artisan('users:prune-deleted')
        ->expectsOutputToContain("Pruned 1 user(s)")
        ->assertSuccessful();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('anonymize strategy anonymizes users past grace period', function () {
    config(['auth.delete.grace_period' => 30]);
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Anonymize]);

    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    $user->delete();
    $user->forceFill(['deleted_at' => now()->subDays(31)])->save();

    $this->artisan('users:prune-deleted')
        ->expectsOutputToContain("Pruned 1 user(s)")
        ->assertSuccessful();

    $pruned = User::withTrashed()->find($user->id);

    expect($pruned->first_name)->toBe('Deleted');
    expect($pruned->last_name)->toBe('User');
    expect($pruned->email)->toContain('@anonymized.local');
    expect($pruned->email)->not->toBe('john@example.com');
});

test('users within grace period are not pruned', function () {
    config(['auth.delete.grace_period' => 30]);
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Delete]);

    $user = User::factory()->create();
    $user->delete();

    $this->artisan('users:prune-deleted')
        ->expectsOutputToContain('No users to prune.')
        ->assertSuccessful();

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

test('no users to prune outputs info message', function () {
    config(['auth.delete.grace_period' => 30]);
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Delete]);

    $this->artisan('users:prune-deleted')
        ->expectsOutputToContain('No users to prune.')
        ->assertSuccessful();
});
