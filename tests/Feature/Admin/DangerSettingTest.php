<?php

use App\Jobs\TruncatePlayerIntelJob;
use App\Jobs\TruncatePlayerPunishmentJob;
use App\Jobs\TruncateServerChatlogsJob;
use App\Jobs\TruncateServerConsolelogsJob;
use App\Jobs\TruncateServerIntelJob;
use App\Jobs\TruncateShoutsJob;
use App\Models\Shout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first(); // Super admin from seeder
});

test('super admin can view dangerzone settings page', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.setting.danger.show'))
        ->assertStatus(200);
});

test('truncate shouts dispatches job without before_date', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.shouts'))
        ->assertRedirect();

    Queue::assertPushed(TruncateShoutsJob::class, function ($job) {
        return $job->beforeDate === null;
    });
});

test('truncate shouts dispatches job with before_date', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.shouts'), [
            'before_date' => '2025-01-15',
        ])
        ->assertRedirect();

    Queue::assertPushed(TruncateShoutsJob::class, function ($job) {
        return $job->beforeDate === '2025-01-15';
    });
});

test('truncate shouts validates before_date format', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.shouts'), [
            'before_date' => 'not-a-date',
        ])
        ->assertSessionHasErrors('before_date');

    Queue::assertNotPushed(TruncateShoutsJob::class);
});

test('truncate consolelogs dispatches job with before_date', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.consolelogs'), [
            'before_date' => '2025-06-01',
        ])
        ->assertRedirect();

    Queue::assertPushed(TruncateServerConsolelogsJob::class, function ($job) {
        return $job->beforeDate === '2025-06-01';
    });
});

test('truncate chatlogs dispatches job with before_date', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.chatlogs'), [
            'before_date' => '2025-03-01',
        ])
        ->assertRedirect();

    Queue::assertPushed(TruncateServerChatlogsJob::class, function ($job) {
        return $job->beforeDate === '2025-03-01';
    });
});

test('truncate player intel dispatches job with before_date', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.playerintel'), [
            'before_date' => '2025-02-01',
        ])
        ->assertRedirect();

    Queue::assertPushed(TruncatePlayerIntelJob::class, function ($job) {
        return $job->beforeDate === '2025-02-01';
    });
});

test('truncate server intel dispatches job with before_date', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.serverintel'), [
            'before_date' => '2025-04-01',
        ])
        ->assertRedirect();

    Queue::assertPushed(TruncateServerIntelJob::class, function ($job) {
        return $job->beforeDate === '2025-04-01';
    });
});

test('truncate player punishments dispatches job with before_date', function () {
    Queue::fake();

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.setting.danger.truncate.playerpunishments'), [
            'before_date' => '2025-05-01',
        ])
        ->assertRedirect();

    Queue::assertPushed(TruncatePlayerPunishmentJob::class, function ($job) {
        return $job->beforeDate === '2025-05-01';
    });
});

test('truncate shouts job deletes only records before date', function () {
    Shout::factory()->create(['created_at' => '2025-01-01']);
    Shout::factory()->create(['created_at' => '2025-01-10']);
    Shout::factory()->create(['created_at' => '2025-02-01']);

    $job = new TruncateShoutsJob('2025-01-15');
    $job->handle();

    expect(Shout::count())->toBe(1);
    expect(Shout::first()->created_at->format('Y-m-d'))->toBe('2025-02-01');
});

test('truncate shouts job deletes all records without date', function () {
    Shout::factory()->count(3)->create();

    $job = new TruncateShoutsJob();
    $job->handle();

    expect(Shout::count())->toBe(0);
});
