<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a guest can list polls', function () {
    $response = $this->get(route('poll.index'));

    $response->assertStatus(200);
});

test('a user can vote on poll', function () {
    $user = User::first();
    $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();
    $this->actingAs($user);

    $response = $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]))
        ->assertJson([
            'message' => 'Vote Successful',
        ]);

    $this->assertDatabaseHas('poll_votes', [
        'user_id' => $user->id,
        'poll_option_id' => $poll->options->first()->id,
    ]);
});

test('a user cannot vote for a poll twice', function () {
    $user = User::first();
    $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();
    $this->actingAs($user);

    $this->postJson(route('poll.vote', [$poll->id, $poll->options->last()->id]));
    $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]))
        ->assertJson([
            'message' => 'User already voted for poll',
        ]);

    $this->assertDatabaseCount('poll_votes', 1);
});

test('user cannot vote on non votable poll', function () {
    $user = User::first();
    $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create([
        'is_closed' => true,
    ]);
    $this->actingAs($user);

    $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]))
        ->assertJson([
            'message' => 'Poll is not votable',
        ]);
});

test('a user cannot vote on poll if he is banned', function () {
    $user = User::first();
    $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();
    $this->actingAs($user);
    $user->banned_at = now();
    $user->save();
    $this->postJson(route('poll.vote', [$poll->id, $poll->options->first()->id]));
    $this->assertDatabaseCount('poll_votes', 0);
});
