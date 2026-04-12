<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_players_page(): void
    {
        $response = $this->get('/stats');
        $response->assertStatus(200);
    }

    public function test_player_index_accepts_search_filter(): void
    {
        $response = $this->get(route('player.index', ['filter' => ['q' => 'testplayer']]));
        $response->assertStatus(200);
    }

    public function test_player_index_accepts_sort_parameter(): void
    {
        $response = $this->get(route('player.index', ['sort' => '-last_seen_at']));
        $response->assertStatus(200);
    }

    public function test_player_index_accepts_positive_sort(): void
    {
        $response = $this->get(route('player.index', ['sort' => 'total_score']));
        $response->assertStatus(200);
    }

    public function test_player_index_accepts_multiple_filters(): void
    {
        $response = $this->get(route('player.index', [
            'filter' => ['q' => 'player'],
            'sort' => '-play_time',
        ]));
        $response->assertStatus(200);
    }

    public function test_player_index_returns_json_when_requested(): void
    {
        $response = $this->getJson(route('player.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function test_player_index_returns_inertia_for_browser(): void
    {
        $response = $this->get(route('player.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Player/IndexPlayer', false));
    }
}
