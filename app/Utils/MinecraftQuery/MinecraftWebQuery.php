<?php

namespace App\Utils\MinecraftQuery;

use App\Settings\PluginSettings;
use Illuminate\Encryption\Encrypter;
use Str;

class MinecraftWebQuery
{
    public function __construct(public $HOST, public $PORT)
    {
    }

    /**
     * Changes the player's skin.
     *
     * @param  string  $changeCommandType - can be: 'url', 'username', 'upload', 'clear'
     * @param  string  $value - the value can be: url -> 'https://minesk.in/7bd96e58ba7049e6abb4943425fe8766', username -> 'xinecraft', custom -> 'b64_encoded_skin_value:::b64_encoded_skin_signature'
     */
    public function setPlayerSkin(string $playerUuid, string $changeCommandType, $value = null)
    {
        // If commandType is custom we need to send two request to set skin and signature due to WebQuery byte limit.
        if ($changeCommandType === 'upload') {
            $skinValue = $value['value'];
            $param = $playerUuid.'½½½½'.'upload:init'.'½½½½'.$skinValue;
            $status = $this->sendQuery('set-player-skin', $param);
            if ($status['status'] === 'err') {
                throw new \Exception(__('Oh Jeez! Something went wrong. Please try again later.'));
            }
            $value = $value['signature'];
        }

        $param = $playerUuid.'½½½½'.$changeCommandType.'½½½½'.$value;
        $status = $this->sendQuery('set-player-skin', $param);
        if ($status['status'] === 'err') {
            throw new \Exception(__('Error setting player skin. Please make sure provided skin is valid.'));
        }

        return $status;
    }

    public function sendChat($username, $message)
    {
        $param = $username.'½½½½'.$message;
        $status = $this->sendQuery('user-say', $param);

        return $status;
    }

    public function sendBroadcast($message)
    {
        $status = $this->sendQuery('broadcast', $message);

        return $status;
    }

    public function runCommand($command, $params = null)
    {
        if ($params) {
            $status = $this->sendQuery('command', $command.' '.$params);
        } else {
            $status = $this->sendQuery('command', $command);
        }

        return $status;
    }

    public function getStatusQuery()
    {
        $status = $this->sendQuery('status');

        return $status;
    }

    public function getPlayerGroups($playerUuid)
    {
        $status = $this->sendQuery('get-player-groups', $playerUuid);

        return $status;
    }

    public function sendQuery($type, $params = null)
    {
        $factory = new \Socket\Raw\Factory();
        $socket = $factory->createClient("tcp://{$this->HOST}:{$this->PORT}", 2.5);
        $encryptedCommand = $this->makeEncryptedString($type, $params);
        $text = $encryptedCommand."\n";
        $socket->write($text);
        // Timeout after 5 seconds for webquery in case of no response
        socket_set_option($socket->getResource(), SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        $buf = $socket->read(102400);
        $socket->close();

        return [
            'status' => trim($buf),
        ];
    }

    public function makeEncryptedString(string $type, ?string $params = null): string
    {
        $pluginSettings = app(PluginSettings::class);
        $apiKey = $pluginSettings->plugin_api_key;
        $apiSecret = Str::substr($pluginSettings->plugin_api_secret, 0, 32);

        $string = [
            'api_key' => $apiKey,
            'type' => $type,
            'params' => $params,
        ];
        $string = json_encode($string);

        $newEncrypter = new Encrypter(($apiSecret), 'AES-256-CBC');

        return $newEncrypter->encryptString($string);
    }

    public function decryptEncryptedString(string $string): string
    {
        $pluginSettings = app(PluginSettings::class);

        $apiSecret = Str::substr($pluginSettings->plugin_api_secret, 0, 32);
        $newEncrypter = new Encrypter(($apiSecret), 'AES-256-CBC');

        return $newEncrypter->decryptString($string);
    }
}
