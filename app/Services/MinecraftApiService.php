<?php

namespace App\Services;

use Cache;
use Exception;
use Illuminate\Support\Facades\Http;
use Log;

class MinecraftApiService
{
    /**
     * Resolve a Minecraft username to its Mojang UUID.
     *
     * Returns the UUID in Mojang's undashed 32-char form, or null when the username does not
     * exist or the lookup fails. Only successful lookups are cached, so a transient outage or a
     * since-registered username is not remembered as "not found".
     */
    public static function playerUsernameToUuid(string $username): ?string
    {
        $cacheKey = 'minecraft:uuid:'.$username;

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $httpResponse = Http::timeout(5)->get("https://api.minecraftservices.com/minecraft/profile/lookup/name/{$username}");

            if (! $httpResponse->successful()) {
                return null;
            }

            $uuid = $httpResponse->json('id');

            if (! $uuid) {
                return null;
            }

            Cache::put($cacheKey, $uuid, 3600);

            return $uuid;
        } catch (Exception $e) {
            Log::warning($e);

            return null;
        }
    }
}
