<?php

namespace App\Utils\MinecraftQuery;

use App\Settings\PluginSettings;
use App\Utils\Helpers\CryptoUtils;
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
        try {
            $payload = [
                'player_uuid' => $playerUuid,
                'change_command_type' => $changeCommandType,
                'value' => $value,
            ];
            $status = $this->sendQuery('set-player-skin', $payload);

            return $status;
        } catch (\Exception $e) {
            throw new \Exception(__('Error setting player skin. Please make sure provided skin is valid.'));
        }
    }

    public function sendChat($username, $message)
    {
        $payload = [
            'username' => $username,
            'message' => $message,
        ];
        $status = $this->sendQuery('user-say', $payload);

        return $status;
    }

    public function notifyAccountLinkSuccess($playerUuid, $userId)
    {
        $payload = [
            'player_uuid' => $playerUuid,
            'user_id' => $userId,
        ];
        $status = $this->sendQuery('account-link-success', $payload);

        return $status;
    }

    public function sendBroadcast($message)
    {
        $payload = [
            'message' => $message,
        ];
        $status = $this->sendQuery('broadcast', $payload);

        return $status;
    }

    public function runCommand($command, $params = null)
    {
        $payload = [
            'command' => $params ? $command.' '.$params : $command,
        ];
        $status = $this->sendQuery('command', $payload);

        return $status;
    }

    public function getStatusQuery()
    {
        $status = $this->sendQuery('status');

        return $status;
    }

    public function getPing()
    {
        $data = $this->sendQuery('ping');

        return $data;
    }

    public function checkPlayerOnline($playerUuid): bool
    {
        if (! Str::isUuid($playerUuid)) {
            throw new \Exception(__('Provided UUID is not valid.'));
        }

        $payload = [
            'player_uuid' => $playerUuid,
        ];
        $status = $this->sendQuery('check-player-online', $payload);

        return $status['data'];
    }

    public function sendQuery($type, array $data = [])
    {
        $encrypted = $this->makePayload($type, $data);

        $factory = new \Socket\Raw\Factory();
        $socket = $factory->createClient("tcp://{$this->HOST}:{$this->PORT}", 5);
        $text = $encrypted."\n";
        $socket->write($text);
        // Timeout after 10 seconds for webquery in case of no response
        socket_set_option($socket->getResource(), SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        // read while we get data
        $buf = '';
        while ($read = $socket->read(4096)) {
            $buf .= $read;
        }
        $socket->close();

        $response = json_decode(trim($buf), true);

        if ($response['status'] != 'ok') {
            throw new \Exception(__('WebQuery failed for unknown reasons. Please check minecraft server logs.'));
        }

        $pluginSettings = app(PluginSettings::class);
        $apiKey = $pluginSettings->plugin_api_key;
        $response['data'] = json_decode($this->decryptEncryptedString($response['data'], $apiKey), true);

        return $response;
    }

    public function makePayload($type, array $data = [])
    {
        // Get the API Key and Secret from the plugin settings.
        $pluginSettings = app(PluginSettings::class);
        $apiKey = $pluginSettings->plugin_api_key;
        $apiSecret = Str::substr($pluginSettings->plugin_api_secret, 0, 32);

        // Sign the data using Secret Key.
        $data['timestamp'] = now()->getTimestampMs();
        $data['type'] = $type;
        $stringData = json_encode($data);
        $dataSignature = CryptoUtils::generateHmacSignature($stringData, $apiSecret);
        $payload = [];
        $payload['payload'] = $stringData;
        $payload['signature'] = $dataSignature;

        // Encrypt the payload using the API Key.
        $encrypted = $this->makeEncryptedString($payload, $apiKey);

        return $encrypted;
    }

    public function makeEncryptedString(array $payload, string $apiKey): string
    {
        $stringPayload = json_encode($payload);
        $newEncrypter = new Encrypter(($apiKey), 'AES-256-CBC');

        return $newEncrypter->encryptString($stringPayload);
    }

    public function decryptEncryptedString(string $string, string $apiKey): string
    {
        $newEncrypter = new Encrypter(($apiKey), 'AES-256-CBC');

        return $newEncrypter->decryptString($string);
    }
}
