<?php

namespace App\Jobs;

use App\Models\Player;
use App\Models\Server;
use App\Settings\PluginSettings;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AccountLinkAfterSuccessCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param Player $player
     * @param Server $server
     */
    public function __construct(private Player $player, private Server $server)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pluginSettings = app(PluginSettings::class);
        $webQuery = new MinecraftWebQuery($this->server->ip_address, $this->server->webquery_port);

        // Send Broadcast
        if ($pluginSettings->account_link_after_success_broadcast_message) {
            $rewardMessageString = \Str::replace('{PLAYER}', $this->player->username, $pluginSettings->account_link_after_success_broadcast_message);
            $webQuery->sendBroadcast($rewardMessageString);
        }

        // If there is no command to send then return
        if (!$pluginSettings->account_link_after_success_command) {
            return;
        }

        // Only give this once per user else they can abuse it by removing and adding player everytime.
        if ($this->player->account_link_after_success_command_run_count > 0) {
            return;
        }

        $rewardCommandString = $pluginSettings->account_link_after_success_command;
        $rewardCommandString = \Str::replace('{PLAYER}', $this->player->username, $rewardCommandString);
        $webQuery->sendQuery('command', $rewardCommandString);

        $this->player->account_link_after_success_command_run_count += 1;
        $this->player->save();
    }
}
