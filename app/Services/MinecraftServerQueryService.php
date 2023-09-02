<?php


namespace App\Services;


use App\Models\Player;
use App\Settings\PluginSettings;
use App\Utils\MinecraftQuery\MinecraftQuery;
use App\Utils\MinecraftQuery\MinecraftQueryException;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class MinecraftServerQueryService
{
    public function getServerStatusWithPlayerUuid($serverHost, $serverPort = 25565)
    {
        $playerList = [];
        $serverInfo = null;

        // Query the server
        $Query = new MinecraftQuery( );
        try
        {
            $Query->Connect( $serverHost, $serverPort );
            $serverInfo = $Query->GetInfo();
            // Get list of players
            $playerList = $Query->GetPlayers();
        }
        catch (MinecraftQueryException $e)
        {
            \Log::error($e->getMessage());
            // Return if offline
            return false;
        }

        // Try fetching player UUID from username.

        // 1. Try to get from table.
        $playerList = $this->getPlayersUuidFromDatabase($playerList);

        // 2. if not found get from third party api like https://playerdb.co/ or https://api.minetools.eu/uuid/
        foreach ($playerList as $username => $uuid) {
            if ($uuid) {
                continue;
            }
            $uuid = $this->getPlayerUuidFromThirdPartyAPI($username);
            $playerList[$username] = $uuid;
        }

        // Format the data and return
        return [
            'server_info' => $serverInfo,
            'players_list' => $playerList
        ];
    }

    private function getPlayersUuidFromDatabase($playerList): array
    {
        if (!$playerList) {
            return [];
        }

        $playerListModified = [];
        $playersFromDatabase = Player::whereIn('username', $playerList)->get();

        foreach ($playerList as $player)
        {
            $playerUuid = $playersFromDatabase->firstWhere('username', $player);
            $playerUuid = $playerUuid ? $playerUuid->uuid : null;
            $playerListModified[$player] = $playerUuid;
        }

        return $playerListModified;
    }

    private function getPlayerUuidFromThirdPartyAPI($username)
    {
        // We cache username to uuid in redis for maybe like 1 day so that
        // third party api dont hit them every minute.
        $uuidCache = Redis::get('player:uuid:'.$username);
        if ($uuidCache) {
            return $uuidCache;
        }

        // https://api.minetools.eu
        $response = Http::get('https://api.minetools.eu/uuid/'.$username);
        if ($response->successful())
        {
            $response = $response->json();
            if ($response && $response['status'] == 'OK')
            {
                $uuid = $response['id'];
                Redis::set('player:uuid:'.$username, $uuid, 'EX', 86400);   // 1 Day Expire
                return $uuid;
            }
        }

        // https://playerdb.co/
        $response = Http::get('https://playerdb.co/api/player/minecraft/'.$username);
        if ($response->successful())
        {
            $response = $response->json();
            if ($response && $response['code'] == 'player.found')
            {
                $uuid = $response['data']['player']['id'];
                Redis::set('player:uuid:'.$username, $uuid, 'EX', 86400);   // 1 Day Expire
                return $uuid;
            }
        }

        return null;
    }

    public function getServerStatusWithPluginWebQueryProtocol($serverHost, $serverPort)
    {
        $pluginSettings = app(PluginSettings::class);

        $webQuery = new MinecraftWebQuery($serverHost, $serverPort);
        $data = $webQuery->getStatusQuery();

        $apiKey = $pluginSettings->plugin_api_key;
        $newEncrypter = new Encrypter( ($apiKey), "AES-256-CBC" );
        $data = $newEncrypter->decryptString($data['status']);
        return collect(json_decode($data));
    }

    public function getPlayerGroupWithPluginWebQueryProtocol($serverHost, $serverPort, $playerUuid)
    {
        $pluginSettings = app(PluginSettings::class);

        $webQuery = new MinecraftWebQuery($serverHost, $serverPort);
        $data = $webQuery->getPlayerGroups($playerUuid);

        $apiKey = $pluginSettings->plugin_api_key;
        $newEncrypter = new Encrypter( ($apiKey), "AES-256-CBC" );
        $data = $newEncrypter->decryptString($data['status']);
        return json_decode($data, true);
    }
}
