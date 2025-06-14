<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Log;
use Cache;

class MinecraftApiService
{
    public static function playerUsernameToUuid($username)
    {
        try {
            $uuid = Cache::remember('minecraft:uuid:' . $username, 3600, function () use ($username) {
                $httpResponse = Http::timeout(5)->get("https://api.minecraftservices.com/minecraft/profile/lookup/name/{$username}");
                $json = $httpResponse->json();
                return $json ? $json['id'] : null;
            });
            return $uuid;
        } catch (Exception $e) {
            Log::warning($e);
            return null;
        }
    }
}
