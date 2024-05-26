<?php

namespace App\Jobs;

use App\Enums\CommandQueueStatus;
use App\Models\Command;
use App\Models\CommandQueue;
use App\Models\Player;
use App\Models\Server;
use App\Settings\PluginSettings;
use App\Utils\Helpers\Helper;
use App\Utils\MinecraftQuery\MinecraftWebQuery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AccountLinkAfterSuccessCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Player $player, private $userId, private Server $server)
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

        // Notify accountlink webquery
        $this->notifyAccountLinkSuccess();

        $commandIds = $pluginSettings->account_link_after_success_commands;
        if (! $commandIds) {
            return;
        }

        $commands = Command::with('servers')->enabled()->whereIn('id', $commandIds)->get();
        // for each commands.
        foreach ($commands as $command) {
            // If command is only for first link and player has already run this command then skip.
            $isRunOnlyFirstLink = $command->config['is_run_only_first_link'];
            if ($isRunOnlyFirstLink && $this->player->account_link_after_success_command_run_count > 0) {
                continue;
            }

            if ($command->is_run_on_all_servers) {
                $servers = Server::whereNotNull('webquery_port')->get();
            } else {
                $servers = $command->servers;
            }

            foreach ($servers as $server) {
                // create & run command on server.
                $this->createAndDispatchJob($command, $server);
            }
        }

        $this->player->account_link_after_success_command_run_count += 1;
        $this->player->save();
    }

    private function notifyAccountLinkSuccess()
    {
        if (! $this->server->webquery_port) {
            return;
        }

        try {
            $webQuery = new MinecraftWebQuery($this->server->ip_address, $this->server->webquery_port);
            $webQuery->notifyAccountLinkSuccess($this->player->uuid, $this->userId);
        } catch (\Exception $e) {
            \Log::warning('AccountLinkAfterSuccessCommandJob:Notify failed: '.$e->getMessage().' Server: '.$this->server->id.' Player: '.$this->player->uuid);
        }
    }

    private function createAndDispatchJob(Command $command, Server $server)
    {
        $params = [
            'player_uuid' => $this->player->uuid,
            'player_username' => $this->player->username,
        ];
        $parsedCommandString = Helper::replacePlaceholders($command->command, $params);
        $commandQueue = CommandQueue::create([
            'server_id' => $server->id,
            'command_id' => $command->id,
            'parsed_command' => $parsedCommandString,
            'config' => $command->config,
            'params' => $params,
            'status' => CommandQueueStatus::PENDING,
            'max_attempts' => $command->max_attempts,
            'player_uuid' => $this->player->uuid,
            'user_id' => $this->userId,
            'player_id' => $this->player->id,
            'tag' => $command->tag,
        ]);
        RunCommandQueueJob::dispatch($commandQueue);
    }
}
