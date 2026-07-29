<?php

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can list polls', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->get(route('admin.poll.index'));

    $response->assertStatus(200);
});

test('admin can view create form', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->get(route('admin.poll.create'));

    $response->assertStatus(200);
});

test('admin can create new poll', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->post(route('admin.poll.store'), [
        'question' => 'Some Question',
        'options' => [
            ['name' => 'Option 1'],
            ['name' => 'Option 2'],
        ],
    ]);

    $this->assertDatabaseCount('polls', 1);
    $this->assertDatabaseCount('poll_options', 2);
    $response->assertRedirect(route('admin.poll.index'));
});

test('admin can delete a poll', function () {
    $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();

    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $this->delete(route('admin.poll.delete', $poll->id));

    $this->assertDatabaseCount('polls', 0);
    $this->assertDatabaseCount('poll_options', 0);
});

test('admin can lock poll', function () {
    $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();

    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $this->put(route('admin.poll.lock', $poll->id));
    $poll->refresh();

    expect($poll->is_closed)->toBeTrue();
});
