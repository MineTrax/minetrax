<?php

namespace App\Jobs;

use App\Models\Server;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangePlayerSkinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Server $server, public string $playerUuid, public string $actionType, public $param = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webQuery = new MinecraftWebQuery($this->server->ip_address, $this->server->webquery_port);

        switch ($this->actionType) {
            case 'upload':
            case 'url':
            case 'username':
                $webQuery->setPlayerSkin($this->playerUuid, $this->actionType, $this->param);
                break;
            case 'reset':
                $webQuery->setPlayerSkin($this->playerUuid, 'clear', null);
                break;
            default:
                throw new \Exception('Invalid action type');
        }
    }
}
