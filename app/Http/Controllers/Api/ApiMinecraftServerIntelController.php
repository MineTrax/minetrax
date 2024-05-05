<?php

namespace App\Http\Controllers\Api;

use App\Models\MinecraftServerLiveInfo;
use App\Models\MinecraftServerWorld;
use App\Models\MinecraftWorldLiveInfo;
use App\Models\Server;
use DB;
use Illuminate\Http\Request;

class ApiMinecraftServerIntelController extends ApiController
{
    public function postReport(Request $request)
    {
        $this->validate($request, [
            'data' => 'required|array',
            'data.server_id' => 'required|int|exists:servers,id',
            'data.online_players' => 'required|int',
            'data.max_players' => 'required|int',
            'data.tps' => 'required|numeric',
            'data.max_memory' => 'required|int',
            'data.total_memory' => 'required|int',
            'data.free_memory' => 'required|int',
            'data.available_cpu_count' => 'required',
            'data.cpu_load' => 'required|numeric',
            'data.uptime' => 'required|int',
            'data.free_disk_in_kb' => 'required|numeric',
            'data.world_data' => 'required|array',
            'data.motd' => 'nullable|string',
            'data.server_version' => 'required|string',
            'data.server_session_id' => 'present|nullable|string',
        ]);

        $server = Server::where('id', $request->input('data.server_id'))->firstOrFail();
        if (! $server->is_server_intel_enabled) {
            return $this->error('Server intel is disabled for this server.', 'server_intel_disabled', 403);
        }

        try {
            // Create MinecraftServerLiveInfo
            DB::transaction(function () use ($request, $server) {
                MinecraftServerLiveInfo::create([
                    'server_id' => $request->input('data.server_id'),
                    'online_players' => $request->input('data.online_players'),
                    'max_players' => $request->input('data.max_players'),
                    'tps' => $request->input('data.tps'),
                    'max_memory' => $request->input('data.max_memory'),
                    'total_memory' => $request->input('data.total_memory'),
                    'free_memory' => $request->input('data.free_memory'),
                    'used_memory' => $request->input('data.total_memory') - $request->input('data.free_memory'),
                    'available_cpu_count' => $request->input('data.available_cpu_count'),
                    'cpu_load' => $request->input('data.cpu_load'),
                    'uptime' => $request->input('data.uptime'),
                    'free_disk_in_kb' => $request->input('data.free_disk_in_kb'),
                    'motd' => $request->input('data.motd') ?? null,
                    'server_version' => $request->input('data.server_version'),
                    'chunks_loaded' => $request->input('data.chunks_loaded'),
                    'server_session_id' => $request->input('data.server_session_id'),
                ]);

                // Create MinecraftServerWorld if not exists and record its MinecraftWorldLiveInfo
                $worlds = $request->input('data.world_data');
                foreach ($worlds as $world) {
                    $worldEntity = MinecraftServerWorld::updateOrCreate([
                        'server_id' => $request->input('data.server_id'),
                        'world_name' => $world['world_name'],
                    ], [
                        'server_id' => $request->input('data.server_id'),
                        'world_name' => $world['world_name'],
                        'world_border' => $world['world_border'],
                        'environment' => $world['environment'],
                    ]);

                    MinecraftWorldLiveInfo::create([
                        'minecraft_server_world_id' => $worldEntity->id,
                        'online_players' => $world['online_players'],
                        'chunks_loaded' => $world['chunks_loaded'],
                        'game_time' => $world['game_time'],
                        'server_session_id' => $request->input('data.server_session_id'),
                    ]);
                }

                $server->update([
                    'last_scanned_at' => now(),
                ]);
            }, 3);

            return $this->success(null, 'Server Intel successfully reported.');
        } catch (\Exception $e) {
            \Log::error($e);

            return $this->error(
                __('Server Intel failed to report: :message', ['message' => $e->getMessage()]),
                'internal_server_error',
                500,
            );
        }
    }
}
