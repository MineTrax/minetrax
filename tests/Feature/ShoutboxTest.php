<?php

namespace Tests\Feature;

use App\Models\Shout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoutboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_shouts()
    {
        Shout::factory()->count(3)->create();

        $response = $this->getJson(route('shout.index'));
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_authenticated_user_can_create_a_shout()
    {
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
    }

    public function test_shout_message_is_required()
    {
        $user = User::first();
        $this->actingAs($user);

        $response = $this->postJson(route('shout.store'), [
            'message' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    public function test_shout_message_max_200_chars()
    {
        $user = User::first();
        $this->actingAs($user);

        $response = $this->postJson(route('shout.store'), [
            'message' => str_repeat('a', 201),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    public function test_muted_user_cannot_create_shout()
    {
        $user = User::first();
        $user->muted_at = now();
        $user->save();

        $this->actingAs($user);
        $response = $this->postJson(route('shout.store'), [
            'message' => 'I am muted.',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_shout()
    {
        $user = User::first();
        $shout = Shout::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->delete(route('shout.delete', $shout->id));

        $this->assertDatabaseMissing('shouts', ['id' => $shout->id]);
    }

    public function test_user_cannot_delete_another_users_shout()
    {
        $user = User::factory()->create();
        $otherUser = User::first();
        $shout = Shout::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);
        $response = $this->delete(route('shout.delete', $shout->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('shouts', ['id' => $shout->id]);
    }

    public function test_admin_can_delete_any_shout()
    {
        $admin = User::whereId(1)->first();
        $otherUser = User::factory()->create();
        $shout = Shout::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($admin);
        $response = $this->delete(route('shout.delete', $shout->id));

        $this->assertDatabaseMissing('shouts', ['id' => $shout->id]);
    }
}
