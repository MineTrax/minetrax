<?php

use App\Jobs\DeleteServerJob;
use App\Models\Server;

test('it clears the deleting flag and keeps other settings when the job fails', function () {
    $server = Server::factory()->create([
        'settings' => ['is_deleting' => true, 'is_player_intel_enabled' => true],
    ]);

    (new DeleteServerJob($server))->failed(new RuntimeException('Record has changed since last read'));

    expect($server->fresh()->settings)->toBe(['is_player_intel_enabled' => true]);
});

test('it deletes the server and clears its data when the job succeeds', function () {
    $server = Server::factory()->create(['settings' => ['is_deleting' => true]]);

    (new DeleteServerJob($server))->handle();

    expect(Server::query()->find($server->id))->toBeNull();
});
