<?php

use App\Services\MinecraftApiService;
use App\Services\MinecraftServerQueryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

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
