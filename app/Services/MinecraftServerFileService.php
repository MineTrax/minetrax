<?php


namespace App\Services;


use Illuminate\Support\Facades\Storage;

class MinecraftServerFileService
{
    public function getServerPropertiesFromRemote($serverLoginString): bool|array
    {
        if (array_key_exists('port', $serverLoginString)){
            $serverLoginString['port'] = (int)$serverLoginString['port'];
        }
        $serverDisk = Storage::build($serverLoginString);

        // This workaround just try to list the directories so it throws error unable to connect instead of server.properties not found.
        $serverDisk->directories('/');

        $isExists = $serverDisk->exists('server.properties');
        // Validate if it has stats folder to get data from.
        if (!$isExists) {
            return false;
        }
        $fileContents = $serverDisk->read('server.properties');
        $fileContents = preg_split('/\r\n|\r|\n/', $fileContents);
        $newArray = [];
        foreach ($fileContents as $line) {
            if($line && !str_starts_with($line, '#')) {
                $exploded = explode('=', $line);
                $newArray[$exploded[0]] = $exploded[1];
            }
        }
        return $newArray;
    }
}
