<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('guest cannot access failed jobs listing', function () {
    $response = $this->get(route('admin.failed-job.index'));
    $response->assertStatus(302);
});

test('admin can view failed jobs listing', function () {
    $this->actingAs(User::whereId(1)->first());
    $response = $this->get(route('admin.failed-job.index'));
    $response->assertStatus(200);
});

test('admin can view failed jobs with entries', function () {
    DB::table('failed_jobs')->insert([
        'uuid' => fake()->uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['job' => 'TestJob']),
        'exception' => 'RuntimeException: Test failure',
        'failed_at' => now(),
    ]);

    $admin = User::whereId(1)->first();
    $this->actingAs($admin);

    $response = $this->get(route('admin.failed-job.index'));
    $response->assertStatus(200);
});

test('admin can clear a failed job', function () {
    $uuid = fake()->uuid();
    DB::table('failed_jobs')->insert([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['job' => 'TestJob']),
        'exception' => 'RuntimeException: Test failure',
        'failed_at' => now(),
    ]);

    $admin = User::whereId(1)->first();
    $this->actingAs($admin);

    $response = $this->delete(route('admin.failed-job.clear'), [
        'uuid' => $uuid,
    ]);

    $response->assertRedirect(route('admin.failed-job.index'));
    $this->assertDatabaseMissing('failed_jobs', ['uuid' => $uuid]);
});

test('admin can flush all failed jobs', function () {
    DB::table('failed_jobs')->insert([
        ['uuid' => fake()->uuid(), 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Error 1', 'failed_at' => now()],
        ['uuid' => fake()->uuid(), 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Error 2', 'failed_at' => now()],
    ]);

    $admin = User::whereId(1)->first();
    $this->actingAs($admin);

    $response = $this->delete(route('admin.failed-job.clear'));

    $response->assertRedirect(route('admin.failed-job.index'));
    $this->assertDatabaseCount('failed_jobs', 0);
});

test('non admin cannot access failed job routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.failed-job.index'))->assertStatus(302);
});
