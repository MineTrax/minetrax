<?php

namespace App\Http\Controllers;

use App\Enums\ServerType;
use App\Models\Player;
use App\Models\Server;
use App\Services\GeolocationService;
use App\Services\MinecraftServerPingService;
use App\Services\MinecraftServerQueryService;
use Illuminate\Support\Facades\Cache;

class ServerController extends Controller
{
    public function pingServer(Server $server, MinecraftServerPingService $pingService)
    {
        // Check if we got cache
        $hasCache = Cache::get('server:ping:' . $server->id);
        if ($hasCache) {
            return json_decode($hasCache, true);
        }

        // Decide what address to use to ping the server.
        $pingProxyServerUsingIPAddress = config('minetrax.ping_proxy_server_using_ip_address');
        if ($pingProxyServerUsingIPAddress && $server->type->value == ServerType::Bungee) {
            $pingAddress = $server->ip_address;
        } else {
            $pingAddress = $server->type->value == ServerType::Bungee ? $server->hostname : $server->ip_address;
        }
        // Get Ping Info of the server using MinecraftPingService
        $pingData = $pingService->pingServer($pingAddress, $server->join_port);

        if ($pingData) {
            Cache::put('server:ping:' . $server->id, json_encode($pingData), 60);
        }

        if (!$pingData) {
            return response()->json([
                'status' => 'error',
                'message' => __('Failed to ping server'),
            ], 500);
        }

        return $pingData;
    }

    public function queryServer(Server $server, MinecraftServerQueryService $queryService)
    {
        // Check if we got cache
        $hasCache = Cache::get('server:query:' . $server->id);
        if ($hasCache) {
            return json_decode($hasCache, true);
        }

        $queryProxyServerUsingIPAddress = config('minetrax.query_proxy_server_using_ip_address');
        $queryAddress = $server->ip_address;
        if(!$queryProxyServerUsingIPAddress && $server->type->value == ServerType::Bungee) {
            $queryAddress = $server->hostname;
        }
        // Get Query for the server using MinecraftQueryService
        $queryData = $queryService->getServerStatusWithPlayerUuid($server->ip_address, $server->query_port);

        if ($queryData) {
            Cache::put('server:query:' . $server->id, json_encode($queryData), 60);
        }

        if (!$queryData) {
            return response()->json([
                'status' => 'error',
                'message' => __('Failed to query server'),
            ], 500);
        }

        // Return Data
        return $queryData;
    }

    public function queryServerWithWebQueryProtocol(Server $server, MinecraftServerQueryService $queryService, GeolocationService $geolocationService)
    {
        // Temp for testing....
//        $response = '[{"is_in_db":true,"username":"xinecraft","id":"2d9070a8-8731-40a5-bf73-052b6e55b708","is_op":true,"ping":0,"country":{"id":251,"name":"India","iso_code":null,"flag":null,"photo_path":"http:\/\/minetrax.test\/images\/flags\/flags-iso\/shiny\/48\/IN.png"}},{"is_in_db":false,"username":"kakamora","id":"2d9070a8-8731-40a5-bf73-052b6e55b704","is_op":false,"ping":0,"country":{"id":251,"name":"India","iso_code":null,"flag":null,"photo_path":"http:\/\/minetrax.test\/images\/flags\/flags-iso\/shiny\/48\/US.png"}}]';
//        $response = '[{"username":"App_X_Gaming","id":"1f8e9e24-097e-4814-be18-4c49097003e2","is_op":true,"ping":24,"country":{"id":169,"name":"Netherlands","iso_code":"NL","flag":"\ud83c\uddf3\ud83c\uddf1","photo_path":"http:\/\/minetrax.test\/images\/flags\/flags-iso\/shiny\/48\/NL.png"},"is_in_db":true},{"username":"Sgwonderer","id":"fd19d4a3-e889-4f10-9648-c007c49f0638","is_op":false,"ping":159,"country":{"id":236,"name":"United States","iso_code":"US","flag":"\ud83c\uddfa\ud83c\uddf8","photo_path":"http:\/\/minetrax.test\/images\/flags\/flags-iso\/shiny\/48\/US.png"},"is_in_db":true}]';
//        $response = json_decode($response);
//        return ($response);

        try {
            // Check if we got cache
            $hasCache = Cache::get('server:webquery:' . $server->id);
            if ($hasCache) {
                return json_decode($hasCache, true);
            }

            $queryData = $queryService->getServerStatusWithPluginWebQueryProtocol($server->ip_address, $server->webquery_port);

            $queryData = $queryData->map(function ($player) use ($geolocationService) {
                $player->country = $geolocationService->getCountryFromIP($player->ip_address);
                $player->is_in_db = Player::whereUuid($player->id)->exists();
                unset($player->ip_address);
                return $player;
            });

            if ($queryData) {
                Cache::put('server:webquery:' . $server->id, json_encode($queryData), 60);
            }

            return ($queryData);
        } catch (\Exception $exception) {
            return response()->json(['message' => __('Web Query Failed')], 500);
        }
    }
}
