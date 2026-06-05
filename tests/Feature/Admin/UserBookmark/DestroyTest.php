<?php

uses()->group('admin.user-bookmark.destroy');

use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\User;

test('admin can delete a user\'s bookmark', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/bookmarks/{$bookmark->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
});

test('super can delete a user\'s bookmark', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$target->id}/bookmarks/{$bookmark->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('bookmarks', ['id' => $bookmark->id]);
});

test('regular user cannot delete a user\'s bookmark', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/bookmarks/{$bookmark->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a user\'s bookmark', function () {
    $target = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $target->id]);

    $response = $this->deleteJson("/admin/users/{$target->id}/bookmarks/{$bookmark->id}");

    $response->assertStatus(401);
});

test('cannot delete a bookmark belonging to a different user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/bookmarks/{$bookmark->id}");

    $response->assertStatus(404);
});
