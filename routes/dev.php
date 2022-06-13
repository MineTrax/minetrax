<?php

use App\Jobs\FetchStatsFromAllServersJob;
use App\Services\MinecraftServerQueryService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('time', function() {
    $timestamp = 1644663318245;
    dd(\Carbon\Carbon::createFromTimestampMs($timestamp));
});

Route::middleware('auth:sanctum')->get('token', function () {
    dd(auth()->user()->createToken('test')->plainTextToken);
});

Route::get('fetch', function () {
    FetchStatsFromAllServersJob::dispatch();
    return 'Fetching MC Players';
});

Route::get('cal', function () {
    \App\Jobs\CalculatePlayersJob::dispatch();
    return 'Calculating Players';
});

Route::get('calr', function () {
    \App\Jobs\CalculatePlayersRatingJob::dispatch();
    return 'Calculating Players Rating';
});

Route::get('/ftptest', function () {
//    $data = [
//        'host' => '',
//        'port' => 21,
//        'username' => '',
//        'password' => '',
//        'root' => '/',
//    ];
    $ftp_connection = ftp_connect($data['host'], $data['port']);
    ftp_login($ftp_connection, $data['username'], $data['password']);
//    ftp_pasv($ftp_connection, true);
    dd(ftp_rawlist($ftp_connection, '-aln /', false));
});

Route::get('servertestb', function () {
    $data = [
        'driver' => 'ftp',
        'host' => '',
        'port' => 21,
        'username' => '',
        'password' => '',
        'root' => '/',
    ];

    $data['port'] = (int)$data['port'];
    $serverDisk  = Storage::build($data);
    $userCache = $serverDisk->listContents('/')->toArray();
    dd($userCache);
});

Route::get('/servertest', function () {

//    $server = \App\Models\Server::get()->where('id', 7)->first();
//    $data = decrypt($server->storage_login);

    $data = [
        'driver' => 'ftp',
        'host' => '',
        'port' => 21,
        'username' => '',
        'password' => '',
        'root' => '/',
    ];
//    dd($data);



    // The internal adapter
    $adapter = new League\Flysystem\Ftp\FtpAdapter(
    // Connection options
        League\Flysystem\Ftp\FtpConnectionOptions::fromArray($data),
        new League\Flysystem\Ftp\FtpConnectionProvider(),
        new League\Flysystem\Ftp\NoopCommandConnectivityChecker(),
        new League\Flysystem\UnixVisibility\PortableVisibilityConverter()
    );

// The FilesystemOperator
    $filesystem = new League\Flysystem\Filesystem($adapter);

    $contents = $filesystem->listContents('/')->toArray();
    dd($contents);



    // make connection to server storage if required

    $data['port'] = (int)$data['port'];
    $serverDisk  = Storage::build($data);
    $userCache = $serverDisk->listContents('/wildxlgaming/stats')->toArray();
    dd($userCache);
    // $fileList = Storage::disk('server')->listContents($this->server->level_name.'/stats');
    // $userCache = Symfony\Component\Yaml\Yaml::parse($userCache);


    // Validate if it has stats folder to get data from.
    if (!$isExists) {
        // Logger Log Invalid Path or Path not found
        return;
    }

    // Get list of Objects
    $fileList = Storage::disk('server')->files('world/stats');

    dd($fileList);
});

Route::get('/status', function () {

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
        "secret" => "0ha4afOPiRPkcbo8PWUVqs4WZtdbsmk81lreZiWErg6qCDuaV2RlBJgKh0MzDuZo",
        "type" => "command",
        "params" => "stop"
    ];

    $string = json_encode($string);

    $theOtherKey = "tMeoi56X4GkRwFBGcg2n6mrn5D0lKPfT";

    $newEncrypter = new \Illuminate\Encryption\Encrypter(($theOtherKey), "AES-256-CBC");
    $es2 = $newEncrypter->encrypt($string);
    $decrypted = $newEncrypter->decrypt($es2);
    dump($es2);
    dump($decrypted);
    $PORT = 4000;
    $HOST = "127.0.0.1";
    $sock = socket_create(AF_INET, SOCK_STREAM, 0)
    or die("error: could not create socket\n");
    $succ = socket_connect($sock, $HOST, $PORT)
    or die("error: could not connect to host\n");
    $text = "Minecraft is getting a\n";
    socket_send($sock, $text, strlen($text), 0);

    $buf = null;
    socket_recv($sock, $buf, 2048, MSG_WAITALL);
    echo $buf;
    socket_close($sock);
});

Route::get('webquery/{uuid}', function(\Illuminate\Http\Request $request) {

    $server = \App\Models\Server::whereId(2)->first();

    $minecraftQueryService = app(MinecraftServerQueryService::class);
    $playerGroups = $minecraftQueryService->getPlayerGroupWithPluginWebQueryProtocol($server->ip_address, $server->webquery_port, $request->uuid);

    dd($playerGroups);
});
