<?php

namespace App\Jobs;

use App\Models\JsonMinecraftPlayerStat;
use App\Models\Player;
use App\Models\Server;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Symfony\Component\Yaml\Yaml;

class FetchStatsFromOneServerJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected Server $server;
    protected $hasEssentials;

    public function __construct(Server $server)
    {
        $this->onQueue('longtask');
        $this->server = $server->withoutRelations();
        $this->hasEssentials = $this->server->settings['plugins']['essentials'];
    }

    public function uniqueId()
    {
        return $this->server->id;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }

        $mimeDetector = new ExtensionMimeTypeDetector();

        // Decrypt the Connection Data;
        $serverLogin = decrypt($this->server->storage_login);
        if (isset($serverLogin['port'])) {
            $serverLogin['port'] = (int)$serverLogin['port'];
        }
        $serverDisk  = Storage::build($serverLogin);

        // Try to get UserCache
        $userCache = $serverDisk->exists('usercache.json');
        if ($userCache) {
            $userCache = $serverDisk->get('usercache.json');
            $userCache = json_decode($userCache, true);
            $userCache = collect($userCache);
        }

        $isExists = $serverDisk->exists($this->server->level_name.'/stats');
        // Validate if it has stats folder to get data from.
        if (!$isExists) {
            // Logger Log Invalid Path or Path not found
            return;
        }

        // Get list of Objects
        $fileList = $serverDisk->listContents($this->server->level_name.'/stats');

        // Start getting JSON and create/update the minecraft_player_stats table
        foreach ($fileList as $file) {
            $fileType = $mimeDetector->detectMimeTypeFromPath($file['path']);
            // If this is a not file or not a json file
            if ($file['type'] != 'file' || $fileType != 'application/json') {
                continue;
            }

            /**
             * path is: world/stats/2d9070a8-8731-40a5-bf73-052b6e55b708.json
             * fileNameWithoutExt need to be 2d9070a8-8731-40a5-bf73-052b6e55b708
             */
            $fileNameWithoutExt = explode('/', $file['path']);
            $fileNameWithoutExt = explode('.', $fileNameWithoutExt[count($fileNameWithoutExt) - 1])[0];
            $playerUuid = $fileNameWithoutExt;

            // Check if we already have this player, and it has not changed.
            // Right now its kinda Jugad with fileSize in case of FTP since it doesn't return lastModified.
            // SFTP does return lastModified, so we use that if file has lastModified instead of fileSize.
            $currentPlayerObj = JsonMinecraftPlayerStat::where(['uuid' => $playerUuid, 'server_id' => $this->server->id])->first();

            $lastUniqueHash = $file['fileSize'];
            if ($file['lastModified']) {
                $lastUniqueHash = $file['lastModified'];
            }
            if ($currentPlayerObj && $currentPlayerObj->hash == $lastUniqueHash) {
                // Log::debug('Skip for:: ' . $playerUuid. " Server:: ". $this->server->id);
                continue;
            }

            $fileContent = $serverDisk->get($file['path']);
            $fileContent = json_decode($fileContent, true);

            $mcKilledBy = 'minecraft:killed_by';
            $mcUsed = 'minecraft:used';
            $mcMined = 'minecraft:mined';
            $mcPickedUp = 'minecraft:picked_up';
            $mcCustom = 'minecraft:custom';
            $mcDropped = 'minecraft:dropped';
            $mcBroken = 'minecraft:broken';
            $mcCrafted = 'minecraft:crafted';
            $mcKilled = 'minecraft:killed';

            $forSaving = [];
            $forSaving['data_version'] = $fileContent['DataVersion'];
            $forSaving['hash'] = $lastUniqueHash;
            // $forSaving['uuid'] = $playerUuid;
            $forSaving['last_modified'] = $file['lastModified'] ?? $currentPlayerObj->last_modified;
            $forSaving['killed_by'] = $fileContent['stats'][$mcKilledBy] ?? null;
            $forSaving['used'] = $fileContent['stats'][$mcUsed] ?? null;
            $forSaving['mined'] = $fileContent['stats'][$mcMined] ?? null;
            $forSaving['picked_up'] = $fileContent['stats'][$mcPickedUp] ?? null;
            $forSaving['custom'] = $fileContent['stats'][$mcCustom] ?? null;
            $forSaving['dropped'] = $fileContent['stats'][$mcDropped] ?? null;
            $forSaving['broken'] = $fileContent['stats'][$mcBroken] ?? null;
            $forSaving['crafted'] = $fileContent['stats'][$mcCrafted] ?? null;
            $forSaving['killed'] = $fileContent['stats'][$mcKilled] ?? null;
            $forSaving['server_id'] = $this->server->id;

            // Calculate total of categories
            $forSaving['total_used'] = $this->calculateTotalForCategory($forSaving['used']);
            $forSaving['total_mined'] = $this->calculateTotalForCategory($forSaving['mined']);
            $forSaving['total_picked_up'] = $this->calculateTotalForCategory($forSaving['picked_up']);
            $forSaving['total_dropped'] = $this->calculateTotalForCategory($forSaving['dropped']);
            $forSaving['total_broken'] = $this->calculateTotalForCategory($forSaving['broken']);
            $forSaving['total_crafted'] = $this->calculateTotalForCategory($forSaving['crafted']);

            // Get all custom data if there is
            if ($forSaving['custom']) {
                $forSaving['total_mob_kills'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:mob_kills') ?? 0;
                $forSaving['total_player_kills'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:player_kills') ?? 0;
                $forSaving['total_deaths'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:deaths') ?? 0;
                $forSaving['total_damage_dealt'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom],'minecraft:damage_dealt' ) ?? 0;
                $forSaving['total_damage_dealt_absorbed'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:damage_dealt_absorbed') ?? 0;
                $forSaving['total_damage_dealt_resisted'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:damage_dealt_resisted') ?? 0;
                $forSaving['total_damage_absorbed'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:damage_absorbed') ?? 0;
                $forSaving['total_damage_blocked_by_shield'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:damage_blocked_by_shield') ?? 0;
                $forSaving['total_damage_resisted'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:damage_resisted') ?? 0;
                $forSaving['total_damage_taken'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:damage_taken') ?? 0;
                $forSaving['total_walk_one_cm'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:walk_one_cm') ?? 0;
                $forSaving['total_fish_caught'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:fish_caught') ?? 0;
                $forSaving['total_enchant_item'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:enchant_item') ?? 0;
                $forSaving['total_play_one_minute'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:play_one_minute', 'minecraft:play_time') ?? 0;
                $forSaving['total_sleep_in_bed'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:sleep_in_bed') ?? 0;
                $forSaving['total_jumps'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:jump') ?? 0;
                $forSaving['total_leave_game'] = $this->getFirstValidValueFromArray($fileContent['stats'][$mcCustom], 'minecraft:leave_game') ?? 0;
            }

            // If there is usercache then try to get username of this user
            if ($userCache) {
                $forSaving['username'] = $this->findUsernameFromCache($playerUuid, $userCache);
            }

            // If This MC Player has object in Players table then link it.
            $hasInPlayer = Player::whereUuid($playerUuid)->first();
            if ($hasInPlayer) {
                $forSaving['player_id'] = $hasInPlayer->id;
            }

            // @TODO PLUGINS
            // Eg: Get IP and Nickname from Essentials if Installed and Enabled.
            if ($this->hasEssentials) {
                try {
                    $essentialsData = $serverDisk->get('plugins/Essentials/userdata/'. $playerUuid. '.yml');
                    $essentialsData = Yaml::parse($essentialsData);
                    $forSaving['essentials'] = $essentialsData;
                    $forSaving['ip_address'] = $this->getFirstValidValueFromArray($essentialsData, 'ip-address', 'ipAddress');
                    $forSaving['last_modified'] = $essentialsData['timestamps']['logout'] ?? null;
                    $forSaving['money'] = $essentialsData['money'] ?? null;
                    // If username is null then try fetching from essentials lastAccountName
                    if (!$forSaving['username']) {
                        $forSaving['username'] = $this->getFirstValidValueFromArray($essentialsData, 'last-account-name', 'lastAccountName') ?? null;
                    }

                    Log::debug("Log IP: ". $forSaving['ip_address']. ' for Player: '. $playerUuid);
                } catch (\Exception $exception) {
                    Log::debug("Failed to get Essentials Info for: ". $playerUuid);
                }
            }

            JsonMinecraftPlayerStat::updateOrCreate(
                ['uuid' => $playerUuid, 'server_id' => $this->server->id],
                $forSaving
            );

            Log::debug('Logged for:: ' . $playerUuid. " Server:: ". $this->server->id);
        }

        // Update scanned time for this server
        $this->server->last_scanned_at = now();
        $this->server->save();
        Log::debug('Completed for :: ' . $this->server->id);
    }

    public function failed(\Throwable $exception)
    {
        Log::debug("SOMETHING WEIRD:". $exception);
    }

    private function findUsernameFromCache($uuid, $cache): string|null
    {
        $userObject = $cache->firstWhere('uuid', $uuid);
        return $userObject['name'] ?? null;
    }

    private function calculateTotalForCategory($data): int
    {
        if (!$data || $data <= 0) {
            return 0;
        }
        return array_sum($data);
    }

    private function getFirstValidValueFromArray($array, ...$keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
        }
        return null;
    }
}
