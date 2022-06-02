<?php


namespace App\Services;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class MinecraftServerFileService
{
    public function getServerPropertiesFromRemote($serverLoginString): bool|array
    {
        $serverLoginString['port'] = (int)$serverLoginString['port'];
        $serverDisk = Storage::build($serverLoginString);
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
