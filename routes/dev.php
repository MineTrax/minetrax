<?php

use App\Jobs\AccountLinkAfterSuccessCommandJob;
use App\Jobs\AccountUnlinkAfterSuccessCommandJob;
use App\Jobs\GeneratePunishmentInsightsJob;
use App\Jobs\RunAwaitingCommandQueuesJob;
use App\Models\Player;
use App\Models\Server;
use App\Services\AiService;
use App\Services\AskDbService;
use App\Services\MinecraftApiService;
use App\Settings\GeneralSettings;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use GuzzleHttp\Client;

Route::get('time', function () {
    $timestamp = 1644663318245;
    dd(\Carbon\Carbon::createFromTimestampMs($timestamp));
});

// Route::middleware('auth:sanctum')->get('token', function () {
//     dd(auth()->user()->createToken('test')->plainTextToken);
// });

Route::get('cal', function () {
    \App\Jobs\CalculatePlayersJob::dispatch();

    return 'Fetching MC Players';
});

Route::get('cals', function () {
    \App\Jobs\CalculatePlayersScoreJob::dispatch();

    return 'Calculating Players Score';
});

Route::get('calr', function () {
    \App\Jobs\CalculatePlayersRatingJob::dispatch();

    return 'Calculating Players Rating';
});

Route::get('/status', function () {

    $Query = null;
    try {
        $Query = new xPaw\MinecraftPing('144.76.224.57', 25565);

        dd($Query->Query());
    } catch (xPaw\MinecraftPingException $e) {
        echo $e->getMessage();
    } finally {
        if ($Query) {
            $Query->Close();
        }
    }
});

Route::get('query', function () {
    $Query = new App\Utils\MinecraftQuery\MinecraftQuery();

    try {
        $Query->Connect('', 25565);

        dump($Query->GetInfo());
        dump($Query->GetPlayers());
    } catch (App\Utils\MinecraftQuery\MinecraftQueryException $e) {
        echo $e->getMessage();
    }
});

Route::get('crypt', function () {
    $string = [
        'secret' => '0ha4afOPiRPkcbo8PWUVqs4WZtdbsmk81lreZiWErg6qCDuaV2RlBJgKh0MzDuZo',
        'type' => 'command',
        'params' => 'stop',
    ];

    $string = json_encode($string);

    $theOtherKey = 'tMeoi56X4GkRwFBGcg2n6mrn5D0lKPfT';

    $newEncrypter = new \Illuminate\Encryption\Encrypter(($theOtherKey), 'AES-256-CBC');
    $es2 = $newEncrypter->encrypt($string);
    $decrypted = $newEncrypter->decrypt($es2);
    dump($es2);
    dump($decrypted);
    $PORT = 4000;
    $HOST = '127.0.0.1';
    $sock = socket_create(AF_INET, SOCK_STREAM, 0)
        or exit("error: could not create socket\n");
    $succ = socket_connect($sock, $HOST, $PORT)
        or exit("error: could not connect to host\n");
    $text = "Minecraft is getting a\n";
    socket_send($sock, $text, strlen($text), 0);

    $buf = null;
    socket_recv($sock, $buf, 2048, MSG_WAITALL);
    echo $buf;
    socket_close($sock);
});

Route::get('/encryptstring', function () {
    $query = new \App\Utils\MinecraftQuery\MinecraftWebQuery('127.0.0.1', 1123);
    $string = $query->makeEncryptedString('console_cmd', 'Xinecraft');

    dump($string);

    $dyc = $query->decryptEncryptedString($string);

    dump($dyc);
});

Route::get('test-log', function () {
    Log::info('test');
    Log::warning('warning!!');

    return 'test';
});

Route::get('username-to-uuid', function () {
    return MinecraftApiService::playerUsernameToUuid('xinecraft');
});

//Route::get('db-schema', function() {
//    $tables = \DB::getDoctrineSchemaManager()->listTables();
//
//    foreach ($tables as $table) {
//        echo $table->getName() . " has columns: " . collect($table->getColumns())->map(fn($column) => $column->getName() . ' ('.$column->getType()->getName().')')->implode(', ') . "<br><br>";
//    }
//});

Route::get('test-player-skin', function (Illuminate\Http\Request $request) {

    $server = \App\Models\Server::whereId(12)->first();
    $player = \App\Models\Player::where('uuid', 'e77bfabe-5a39-32e5-b9c1-dd9b4bbda490')->first();

    $webQuery = new MinecraftWebQuery($server->ip_address, $server->webquery_port);
    // URL or name
    $value = 'ewogICJ0aW1lc3RhbXAiIDogMTcwNzYzMjcxNjUwNSwKICAicHJvZmlsZUlkIiA6ICJiMTRiMjY2NzgxOTU0ZGM1OTUzYTRkYWQ5MjRiZGRjNSIsCiAgInByb2ZpbGVOYW1lIiA6ICJHbm92ZV8iLAogICJzaWduYXR1cmVSZXF1aXJlZCIgOiB0cnVlLAogICJ0ZXh0dXJlcyIgOiB7CiAgICAiU0tJTiIgOiB7CiAgICAgICJ1cmwiIDogImh0dHA6Ly90ZXh0dXJlcy5taW5lY3JhZnQubmV0L3RleHR1cmUvM2I2NDNhN2VmMDBlYTBmY2NiYTZjZDdmMjg0YzJlNGFjZTU2MGRlZTA4ZDZlM2E1MTM4N2ZiZDEzMTEzNTM2OSIKICAgIH0KICB9Cn0=';
    $sig = 'wo0p7uQx0iduQkmJJjR/d/0sUZKKz1QprA0QANaLZVkIzRYwSokob2mItketT6yKUljGoMPDv50WOSZLe504Aoxt+4y2sb2XhpRJ4Qji1m67LrDTFstwYUAtgZfh+qVW+J6qj8m+Dr6NQzkiHzhCAk0GH6YyyBbv5FfiePe1elqFZUUUAvRU8O1FfoXca2QIfyWuu8+7Opc7O+YmJZHyDKSMjR9LcRu767JlPde5v0IlpGrtusMmFIqKVdyKcWMpmnFFGH3OFlJKRsmypjJAi2/142BrOTC/nXVbfSuqrRkAAaU3m9DuZH8ncRuA9O0ko9LPj5Ld0gsfDsC2aAYRggkJN+HMp6k7QH/RiTLrzWJBQyy+RD/vXgkctJA8zQ7avwxMFI2i4O/ydA7C0913RnkoKAesAwrgMh9m7kOwsv6/vRE5yZ4BP91CDYVI53EGBAh6ljIISGVC9umYIQDjiiQMwZMxgf6E00saFHBq5M/NuGBFIyJu+gGJLIQqm1rabti2CJgWVt/ysi/AKqVzVz4EqfmNdn9eB0816XLvd+ZW8hehcl4s76XEs5ycx0JVwsef6vCIVbjnpiGLEY8TS2Fw+G6uWkIrmwRZSs9xBeSJKDxDj6lLdo8fqeeA7ECrlKQpUkYHsXZU3Vq8v1/KsVKWIOrrUjWWv8e6MgBxRL0=';
    $wi = 'ewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXeICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXeICJ0aW1lc3RhbXewogICJ0aW1lc3RhbXW1lc3RhbXewogICJ0aW1labv46345av324532sdf53425234534';

    $data = $webQuery->setPlayerSkin($player->uuid, 'custom', $wi);

    dd($data);
});

Route::get('override', function () {

    dd(trans());
    dd(__('Profile Information'));
});

Route::get('webquery-v2', function () {
    $webQuery = new MinecraftWebQuery('127.0.0.1', 25569);

    dd($webQuery->checkPlayerOnline('5fdf7a97-97db-3328-bc78-814b0bf4bcf1'));
});

Route::get('test-link', function () {
    $player = Player::first();
    $userId = request()->user()->id;
    $server = Server::find(2);
    AccountLinkAfterSuccessCommandJob::dispatch($player, $userId, $server);
});

Route::get('test-unlink', function () {
    $player = Player::first();
    $userId = request()->user()->id;
    AccountUnlinkAfterSuccessCommandJob::dispatch($player, $userId);
});

Route::get('test-runlater', function () {
    RunAwaitingCommandQueuesJob::dispatch();
});

Route::get('test-json-query', function () {
    $originServerName = 'litebans';
    $originServer = Server::where('settings->server_identifier', $originServerName)
        ->orWhere('name', $originServerName)
        ->first();

    dd($originServer);
});

Route::get('ban-insights', function () {

    $punishment = \App\Models\PlayerPunishment::find(1912);

    GeneratePunishmentInsightsJob::dispatchSync($punishment);

    return 'done';
});

Route::get('ai-service', function (AiService $aiService) {
    $response = $aiService->simplePrompt(
        'You are a Steve, web developer with 10 year of experience. When anyone ask you who you are tell them you are Steve.',
        'Write 5 line an poem for virus.',
    );

    return $response;
});

Route::get('askdb-service', function (AskDbService $askDbService) {
    $query = request()->query('q');
    $response = $askDbService->chatWithAskDbForUser($query, request()->user());

    dd($response);
    return [
        'data' => $response,
    ];
});

Route::get('ui-test', function () {
    return Inertia::render('Extra/Dev');
});


// private function forceJoinDiscordServer($socialUser)
// {
//     $serverID = app(GeneralSettings::class)->discord_server_id;
//     $botToken = config('services.discord.token');
//     $forceJoinServer = config('services.discord.force_join_server');

//     if (!$serverID || !$botToken || !$forceJoinServer) {
//         return;
//     }

//     try {
//         $client = new Client();
//         $response = $client->put("https://discord.com/api/v10/guilds/{$serverID}/members/{$socialUser->getId()}", [
//             'headers' => [
//                 'Authorization' => 'Bot ' . $botToken,
//                 'Content-Type' => 'application/json',
//             ],
//             'json' => [
//                 'access_token' => $socialUser->token,
//             ],
//         ]);

//         if ($response->getStatusCode() === 201) {
//             Log::info("User {$socialUser->getId()} joined Discord server {$serverID}");
//         }
//     } catch (\Exception $e) {
//         Log::error("Failed to add user to Discord server: " . $e->getMessage());
//     }
// }

Route::get('refresh-discord-token', function (Illuminate\Http\Request $request) {
    // Get user_id from query param, default to 1
    $userId = $request->query('user_id', 1);
    $user = \App\Models\User::find($userId);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $socialAccount = $user->socialAccounts()->where('provider', 'discord')->first();

    if (!$socialAccount) {
        return response()->json(['error' => 'Discord social account not found for this user'], 404);
    }

    if (!$socialAccount->refresh_token) {
        return response()->json(['error' => 'No refresh token available for this user'], 400);
    }

    try {
        $client = new Client();
        $response = $client->post('https://discord.com/api/oauth2/token', [
            'form_params' => [
                'client_id' => config('services.discord.client_id'),
                'client_secret' => config('services.discord.client_secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $socialAccount->refresh_token,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        // Update the social account with new tokens
        $socialAccount->update([
            'token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        Log::info("Discord token refreshed for user {$userId}");

        return response()->json([
            'success' => true,
            'message' => 'Discord token refreshed successfully',
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in' => $data['expires_in'],
            'token_type' => $data['token_type'],
        ]);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
        Log::error("Failed to refresh Discord token: " . json_encode($errorBody));
        return response()->json([
            'error' => 'Failed to refresh Discord token',
            'details' => $errorBody,
        ], $e->getResponse()->getStatusCode());
    } catch (\Exception $e) {
        Log::error("Failed to refresh Discord token: " . $e->getMessage());
        return response()->json([
            'error' => 'Failed to refresh Discord token',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('test-force-join-discord-server', function (Illuminate\Http\Request $request) {
    // Get user_id from query param, default to 1
    $userId = $request->query('user_id', 1);
    $user = \App\Models\User::find($userId);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $socialAccount = $user->socialAccounts()->where('provider', 'discord')->first();

    if (!$socialAccount) {
        return response()->json(['error' => 'Discord social account not found for this user'], 404);
    }

    $serverID = "508594544598712330"; // app(GeneralSettings::class)->discord_server_id;
    $botToken = config('services.discord.token');

    if (!$serverID) {
        return response()->json(['error' => 'Discord server ID not configured in settings'], 400);
    }

    if (!$botToken) {
        return response()->json(['error' => 'Discord bot token not configured'], 400);
    }

    $discordUserId = $socialAccount->provider_id;
    $userToken = $socialAccount->token;

    if (!$userToken) {
        return response()->json(['error' => 'No Discord access token available for this user'], 400);
    }

    try {
        $client = new Client();
        $url = "https://discord.com/api/v10/guilds/{$serverID}/members/{$discordUserId}";
        $response = $client->put($url, [
            'headers' => [
                'Authorization' => 'Bot ' . $botToken,
                'Content-Type' => 'application/json',
                'User-Agent' => 'DiscordBot (https://crazymc.net, v0.1)',
            ],
            'json' => [
                'access_token' => $userToken,
            ]
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode === 201) {
            Log::info("User {$discordUserId} joined Discord server {$serverID}");
            return response()->json([
                'success' => true,
                'message' => "User successfully added to Discord server",
                'discord_user_id' => $discordUserId,
                'server_id' => $serverID,
            ]);
        } elseif ($statusCode === 204) {
            // User is already a member
            return response()->json([
                'success' => true,
                'message' => "User is already a member of the Discord server",
                'discord_user_id' => $discordUserId,
                'server_id' => $serverID,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Request completed",
            'status_code' => $statusCode,
        ]);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
        Log::error("Failed to add user to Discord server: " . json_encode($errorBody));
        return response()->json([
            'error' => 'Failed to add user to Discord server',
            'details' => $errorBody,
        ], $e->getResponse()->getStatusCode());
    } catch (\Exception $e) {
        Log::error("Failed to add user to Discord server: " . $e->getMessage());
        return response()->json([
            'error' => 'Failed to add user to Discord server',
            'message' => $e->getMessage(),
        ], 500);
    }
});
