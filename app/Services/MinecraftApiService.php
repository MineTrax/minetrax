<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class MinecraftApiService
{
	public static function playerUsernameToUuid($username)
	{
		try {
			$httpResponse = Http::get("https://api.mojang.com/users/profiles/minecraft/${username}");
			return $httpResponse->json()["id"];
		} catch (Exception $e) {
			return null;
		}
	}
}
