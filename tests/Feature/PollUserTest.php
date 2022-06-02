<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_list_polls()
    {
        $response = $this->get(route('poll.index'));

        $response->assertStatus(200);
    }

    public function test_a_user_can_vote_on_poll()
    {
        $user = User::first();
        $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();
        $this->actingAs($user);

        $response = $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]))
        ->assertJson([
            'message' => 'Vote Successful'
        ]);

        $this->assertDatabaseHas('poll_votes', [
            'user_id' => $user->id,
            'poll_option_id' => $poll->options->first()->id
        ]);
    }

    public function test_a_user_cannot_vote_for_a_poll_twice()
    {
        $user = User::first();
        $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();
        $this->actingAs($user);

        $this->postJson(route('poll.vote', [$poll->id, $poll->options->last()->id]));
        $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]))
            ->assertJson([
                'message' => 'User already voted for poll'
            ]);

        $this->assertDatabaseCount('poll_votes', 1);
    }

    public function test_user_cannot_vote_on_non_votable_poll()
    {
        $user = User::first();
        $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create([
            'is_closed' => true
        ]);
        $this->actingAs($user);

        $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]))
            ->assertJson([
                'message' => 'Poll is not votable'
            ]);
    }

    public function test_a_user_cannot_vote_on_poll_if_he_is_banned()
    {
        $user = User::first();
        $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();
        $this->actingAs($user);
        $user->banned_at = now();
        $user->save();
        $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]));
        $this->assertDatabaseCount('poll_votes', 0);
    }
}
