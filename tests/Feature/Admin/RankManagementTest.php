<?php

use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first();
});

test('superadmin can list ranks', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.rank.index'))
        ->assertStatus(200);
});

test('superadmin can view rank create form', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.rank.create'))
        ->assertStatus(200);
});

test('superadmin can create a rank', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.rank.store'), [
            'name' => 'Diamond',
            'shortname' => 'DIA',
            'total_score_needed' => 1000,
            'total_play_time_needed' => 500,
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('rank.png', 100, 100),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('ranks', ['name' => 'Diamond', 'shortname' => 'DIA']);
});

test('rank creation requires name', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.rank.store'), [
            'shortname' => 'DIA',
            'total_score_needed' => 1000,
            'total_play_time_needed' => 500,
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('rank.png', 100, 100),
        ])
        ->assertSessionHasErrors('name');
});

test('superadmin can filter ranks', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.rank.index', ['filter' => ['q' => 'test']]))
        ->assertStatus(200);
});

test('regular user cannot access rank management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.rank.index'))
        ->assertRedirect();
});
