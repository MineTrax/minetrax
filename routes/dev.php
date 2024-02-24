<?php

use App\Services\AskGptService;
use App\Services\MinecraftApiService;
use App\Services\MinecraftServerQueryService;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use League\CommonMark\GithubFlavoredMarkdownConverter;

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

Route::get('webquery/{uuid}', function (Illuminate\Http\Request $request) {

    $server = \App\Models\Server::whereId(2)->first();

    $minecraftQueryService = app(MinecraftServerQueryService::class);
    $playerGroups = $minecraftQueryService->getPlayerGroupWithPluginWebQueryProtocol($server->ip_address, $server->webquery_port, $request->uuid);

    dd($playerGroups);
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

Route::get('test-askdb', function (AskGptService $askDbService) {
    $response = $askDbService->askDb('What is the name of the player with the highest score?');
    $converter = new GithubFlavoredMarkdownConverter();
    $response = $converter->convertToHtml($response);

    return [
        'data' => $response->getContent(),
    ];
});

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
