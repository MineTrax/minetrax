<?php


namespace App\Services;


use App\Utils\MinecraftQuery\MinecraftPing;
use App\Utils\MinecraftQuery\MinecraftPingException;

class MinecraftServerPingService
{
    public function pingServer($serverIp, $serverPort)
    {
        try
        {
            $Query = new MinecraftPing($serverIp, $serverPort);
            $data = $Query->Query();
            if ($data) {
                return $data;
            }
            return false;
        }
        catch( MinecraftPingException $e)
        {
            \Log::error($e->getMessage());
            return false;
        }
    }

}
