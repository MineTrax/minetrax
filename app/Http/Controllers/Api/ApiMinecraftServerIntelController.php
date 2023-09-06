<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MinecraftServerLiveInfo;
use App\Models\MinecraftServerWorld;
use App\Models\MinecraftWorldLiveInfo;
use App\Models\Server;
use DB;
use Illuminate\Http\Request;

class ApiMinecraftServerIntelController extends Controller
{
    public function postReport(Request $request)
    {
        $this->validate($request, [
            'server_id' => 'required|int|exists:servers,id',
            'online_players' => 'required|int',
            'max_players' => 'required|int',
            'tps' => 'required|numeric',
            'max_memory' => 'required|int',
            'total_memory' => 'required|int',
            'free_memory' => 'required|int',
            'available_cpu_count' => 'required',
            'cpu_load' => 'required|numeric',
            'uptime' => 'required|int',
            'free_disk_in_kb' => 'required|numeric',
            'world_data' => 'required|array',
            'motd' => 'nullable|string',
            'server_version' => 'required|string',
            'server_session_id' => 'present|nullable|string'
        ]);

        $server = Server::where('id', $request->server_id)->firstOrFail();
        if (!$server->is_server_intel_enabled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server intel is disabled for this server.',
            ], 403);
        }

        try {
            // Create MinecraftServerLiveInfo
            DB::transaction(function() use ($request, $server) {
                MinecraftServerLiveInfo::create([
                    'server_id' => $request->input('server_id'),
                    'online_players' => $request->input('online_players'),
                    'max_players' => $request->input('max_players'),
                    'tps' => $request->input('tps'),
                    'max_memory' => $request->input('max_memory'),
                    'total_memory' => $request->input('total_memory'),
                    'free_memory' => $request->input('free_memory'),
                    'used_memory' => $request->input('total_memory') - $request->input('free_memory'),
                    'available_cpu_count' => $request->input('available_cpu_count'),
                    'cpu_load' => $request->input('cpu_load'),
                    'uptime' => $request->input('uptime'),
                    'free_disk_in_kb' => $request->input('free_disk_in_kb'),
                    'motd' => $request->input('motd') ?? null,
                    'server_version' => $request->input('server_version'),
                    'chunks_loaded' => $request->input('chunks_loaded'),
                    'server_session_id' => $request->input('server_session_id'),
                ]);

                // Create MinecraftServerWorld if not exists and record its MinecraftWorldLiveInfo
                $worlds = $request->input('world_data');
                foreach ($worlds as $world) {
                    $worldEntity = MinecraftServerWorld::updateOrCreate([
                        'server_id' => $request->input('server_id'),
                        'world_name' => $world['world_name'],
                    ], [
                        'server_id' => $request->input('server_id'),
                        'world_name' => $world['world_name'],
                        'world_border' => $world['world_border'],
                        'environment' => $world['environment'],
                    ]);

                    MinecraftWorldLiveInfo::create([
                        'minecraft_server_world_id' => $worldEntity->id,
                        'online_players' => $world['online_players'],
                        'chunks_loaded' => $world['chunks_loaded'],
                        'game_time' => $world['game_time'],
                        'server_session_id' => $request->input('server_session_id'),
                    ]);
                }

                $server->update([
                    'last_scanned_at' => now(),
                ]);
            }, 3);

            return response()->json([
                'status' => 'success',
                'message' => 'Server Intel successfully reported.',
            ], 201);
        } catch(\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Server Intel failed to report.',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }
}
