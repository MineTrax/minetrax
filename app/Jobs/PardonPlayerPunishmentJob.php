<?php

namespace App\Jobs;

use App\Models\PlayerPunishment;
use App\Models\Server;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PardonPlayerPunishmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PlayerPunishment $playerPunishment, public $reason, public $admin = null)
    {
        //
    }

    public function handle(): void
    {
        // Decide which server to connect.
        // 1. If we have origin_server_id & that server has webquery, use that server.
        // 2. else, send to all servers that have webquery.
        if ($this->playerPunishment->origin_server_id && $this->playerPunishment->originServer?->webquery_port) {
            $servers = [$this->playerPunishment->originServer];
        } else {
            $servers = Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->get();
        }

        foreach ($servers as $server) {
            $this->pardonPlayerPunishment($server);
        }
    }

    private function pardonPlayerPunishment(Server $server): void
    {
        $webQuery = new MinecraftWebQuery($server->ip_address, $server->webquery_port);

        $victim = $this->playerPunishment->uuid ?? $this->playerPunishment->ip_address;
        $webQuery->banwardenPardon(
            $this->playerPunishment->type,
            $victim,
            $this->reason,
            $this->admin,
        );
    }
}
