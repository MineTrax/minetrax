<?php

use App\Models\Shout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can list shouts', function () {
    Shout::factory()->count(3)->create();

    $response = $this->getJson(route('shout.index'));
    $response->assertStatus(200);
    $response->assertJsonCount(3);
});

test('authenticated user can create a shout', function () {
    $user = User::first();
    $this->actingAs($user);

    $response = $this->postJson(route('shout.store'), [
        'message' => 'Hello everyone!',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('shouts', [
        'message' => 'Hello everyone!',
        'user_id' => $user->id,
    ]);
});

test('shout message is required', function () {
    $user = User::first();
    $this->actingAs($user);

    $response = $this->postJson(route('shout.store'), [
        'message' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('message');
});

test('shout message max 200 chars', function () {
    $user = User::first();
    $this->actingAs($user);

    $response = $this->postJson(route('shout.store'), [
        'message' => str_repeat('a', 201),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('message');
});

test('muted user cannot create shout', function () {
    $user = User::first();
    $user->muted_at = now();
    $user->save();

    $this->actingAs($user);
    $response = $this->postJson(route('shout.store'), [
        'message' => 'I am muted.',
    ]);

    $response->assertStatus(403);
});

test('user can delete own shout', function () {
    $user = User::first();
    $shout = Shout::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);
    $response = $this->delete(route('shout.delete', $shout->id));

    $this->assertDatabaseMissing('shouts', ['id' => $shout->id]);
});

test('user cannot delete another users shout', function () {
    $user = User::factory()->create();
    $otherUser = User::first();
    $shout = Shout::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user);
    $response = $this->delete(route('shout.delete', $shout->id));

    $response->assertStatus(403);
    $this->assertDatabaseHas('shouts', ['id' => $shout->id]);
});

test('admin can delete any shout', function () {
    $admin = User::whereId(1)->first();
    $otherUser = User::factory()->create();
    $shout = Shout::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($admin);
    $response = $this->delete(route('shout.delete', $shout->id));

    $this->assertDatabaseMissing('shouts', ['id' => $shout->id]);
});
