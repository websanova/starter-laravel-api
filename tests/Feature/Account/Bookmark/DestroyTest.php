<?php

uses()->group('account.bookmark.destroy');

use App\Models\Bookmark;
use App\Models\User;

test('user can delete their bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/account/bookmarks/{$bookmark->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
});

test('user cannot delete another user bookmark', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->deleteJson("/account/bookmarks/{$bookmark->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a bookmark', function () {
    $bookmark = Bookmark::factory()->create();

    $response = $this->deleteJson("/account/bookmarks/{$bookmark->id}");

    $response->assertStatus(401);
});
