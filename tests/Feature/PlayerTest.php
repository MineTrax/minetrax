<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class PlayerTest extends TestCase
{
	use RefreshDatabase;

	// test a guest can view players page
	public function test_guest_can_view_players_page()
	{
		$response = $this->get('/stats');
		$response->assertStatus(200);
	}
}