<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can view players page', function () {
    $response = $this->get('/stats');
    $response->assertStatus(200);
});

test('player index accepts search filter', function () {
    $response = $this->get(route('player.index', ['filter' => ['q' => 'testplayer']]));
    $response->assertStatus(200);
});

test('player index accepts sort parameter', function () {
    $response = $this->get(route('player.index', ['sort' => '-last_seen_at']));
    $response->assertStatus(200);
});

test('player index accepts positive sort', function () {
    $response = $this->get(route('player.index', ['sort' => 'total_score']));
    $response->assertStatus(200);
});

test('player index accepts multiple filters', function () {
    $response = $this->get(route('player.index', [
        'filter' => ['q' => 'player'],
        'sort' => '-play_time',
    ]));
    $response->assertStatus(200);
});

test('player index returns json when requested', function () {
    $response = $this->getJson(route('player.index'));
    $response->assertStatus(200);
    $response->assertJsonStructure(['data']);
});

test('player index returns inertia for browser', function () {
    $response = $this->get(route('player.index'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Player/IndexPlayer', false));
});
