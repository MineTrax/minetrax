<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FailedJobAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_failed_jobs_listing()
    {
        $response = $this->get(route('admin.failed-job.index'));
        $response->assertStatus(302);
    }

    public function test_admin_can_view_failed_jobs_listing()
    {
        $this->actingAs(User::whereId(1)->first());
        $response = $this->get(route('admin.failed-job.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_view_failed_jobs_with_entries()
    {
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
    }

    public function test_admin_can_clear_a_failed_job()
    {
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
    }

    public function test_admin_can_flush_all_failed_jobs()
    {
        DB::table('failed_jobs')->insert([
            ['uuid' => fake()->uuid(), 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Error 1', 'failed_at' => now()],
            ['uuid' => fake()->uuid(), 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Error 2', 'failed_at' => now()],
        ]);

        $admin = User::whereId(1)->first();
        $this->actingAs($admin);

        $response = $this->delete(route('admin.failed-job.clear'));

        $response->assertRedirect(route('admin.failed-job.index'));
        $this->assertDatabaseCount('failed_jobs', 0);
    }

    public function test_non_admin_cannot_access_failed_job_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.failed-job.index'))->assertStatus(302);
    }
}
