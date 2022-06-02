<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PollAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_polls()
    {
        $this->actingAs(User::whereId(1)->first());  // Super admin
        $response = $this->get(route('admin.poll.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_form()
    {
        $this->actingAs(User::whereId(1)->first());  // Super admin
        $response = $this->get(route('admin.poll.create'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_poll()
    {
        $this->actingAs(User::whereId(1)->first()); // Super admin
        $response = $this->post(route('admin.poll.store'), [
            'question' => 'Some Question',
            'options' => [
                ['name' => 'Option 1'],
                ['name' => 'Option 2']
            ],
        ]);

        $this->assertDatabaseCount('polls', 1);
        $this->assertDatabaseCount('poll_options', 2);
        $response->assertRedirect(route('admin.poll.index'));
    }

    public function test_admin_can_delete_a_poll()
    {
        $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();

        $this->actingAs(User::whereId(1)->first()); // Super admin
        $this->delete(route('admin.poll.delete', $poll->id));

        $this->assertDatabaseCount('polls', 0);
        $this->assertDatabaseCount('poll_options', 0);
    }

    public function test_admin_can_lock_poll()
    {
        $poll = Poll::factory()->has(PollOption::factory()->count(3), 'options')->create();

        $this->actingAs(User::whereId(1)->first()); // Super admin
        $this->put(route('admin.poll.lock', $poll->id));
        $poll->refresh();

        $this->assertTrue($poll->is_closed);
    }
}
