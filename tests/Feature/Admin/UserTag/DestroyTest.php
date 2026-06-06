<?php

uses()->group('admin.user-tag.destroy');

use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Tag;
use App\Models\User;

test('admin can delete a user\'s tag', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/tags/{$tag->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('super can delete a user\'s tag', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$target->id}/tags/{$tag->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('deleting a tag removes its bookmark pivot rows', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $target->id]);
    $bookmark = Bookmark::factory()->create(['user_id' => $target->id]);
    $bookmark->tags()->attach($tag);

    $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/tags/{$tag->id}");

    $this->assertDatabaseMissing('bookmark_tag', ['tag_id' => $tag->id]);
    $this->assertDatabaseHas('bookmarks', ['id' => $bookmark->id]);
});

test('regular user cannot delete a user\'s tag', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/tags/{$tag->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a user\'s tag', function () {
    $target = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $target->id]);

    $response = $this->deleteJson("/admin/users/{$target->id}/tags/{$tag->id}");

    $response->assertStatus(401);
});

test('cannot delete a tag belonging to a different user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/tags/{$tag->id}");

    $response->assertStatus(404);
});
