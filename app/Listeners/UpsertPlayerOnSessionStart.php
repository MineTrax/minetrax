<?php

namespace App\Listeners;

use App\Events\MinecraftPlayerSessionCreated;
use App\Models\MinecraftPlayer;
use App\Models\Player;
use DB;

class UpsertPlayerOnSessionStart
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MinecraftPlayerSessionCreated $event): void
    {
        $minecraftPlayerSession = $event->minecraftPlayerSession;

        $minecraftPlayer = MinecraftPlayer::where('player_uuid', $minecraftPlayerSession->player_uuid)
            ->where('server_id', $minecraftPlayerSession->server_id)
            ->first();

        DB::transaction(function () use ($minecraftPlayerSession, $minecraftPlayer) {
            if (! $minecraftPlayer) {

                $player = Player::where('uuid', $minecraftPlayerSession->player_uuid)->first();
                if (! $player) {
                    $maxPlayerPosition = Player::query()->max('position') ?? 0;
                    $player = Player::create([
                        'uuid' => $minecraftPlayerSession->player_uuid,
                        'username' => $minecraftPlayerSession->player_username,
                        'ip_address' => $minecraftPlayerSession->player_ip_address,
                        'first_seen_at' => $minecraftPlayerSession->session_started_at,
                        'last_seen_at' => $minecraftPlayerSession->session_ended_at ?? now(),
                        'country_id' => $minecraftPlayerSession->country_id,
                        'last_minecraft_version' => $minecraftPlayerSession->minecraft_version,
                        'last_join_address' => $minecraftPlayerSession->join_address,
                        'position' => $maxPlayerPosition + 1,
                    ]);
                } else {
                    $player->last_join_address = $minecraftPlayerSession->join_address ?? $player->last_join_address;
                    $player->last_minecraft_version = $minecraftPlayerSession->minecraft_version ?? $player->last_minecraft_version;
                    $player->last_seen_at = $minecraftPlayerSession->session_ended_at ?? now();
                    $player->ip_address = $minecraftPlayerSession->player_ip_address;
                    $player->username = $minecraftPlayerSession->player_username;
                    $player->country_id = $minecraftPlayerSession->country_id;
                    $player->save();
                }

                $minecraftPlayer = MinecraftPlayer::create([
                    'player_uuid' => $minecraftPlayerSession->player_uuid,
                    'server_id' => $minecraftPlayerSession->server_id,
                    'player_username' => $minecraftPlayerSession->player_username,
                    'player_displayname' => $minecraftPlayerSession->player_displayname,
                    'player_ip_address' => $minecraftPlayerSession->player_ip_address,
                    'first_seen_at' => $minecraftPlayerSession->session_started_at,
                    'last_seen_at' => $minecraftPlayerSession->session_ended_at ?? now(),
                    'last_minecraft_version' => $minecraftPlayerSession->minecraft_version,
                    'last_join_address' => $minecraftPlayerSession->join_address,
                    'vault_balance' => $minecraftPlayerSession->vault_balance,
                    'vault_groups' => $minecraftPlayerSession->vault_groups,
                    'country_id' => $minecraftPlayerSession->country_id,
                    'player_id' => $player->id,
                ]);
            } else {
                $minecraftPlayer->last_join_address = $minecraftPlayerSession->join_address ?? $minecraftPlayer->last_join_address;
                $minecraftPlayer->last_minecraft_version = $minecraftPlayerSession->minecraft_version ?? $minecraftPlayer->last_minecraft_version;
                $minecraftPlayer->last_seen_at = $minecraftPlayerSession->session_ended_at ?? now();
                $minecraftPlayer->player_displayname = $minecraftPlayerSession->player_displayname;
                $minecraftPlayer->player_ip_address = $minecraftPlayerSession->player_ip_address;
                $minecraftPlayer->player_username = $minecraftPlayerSession->player_username;
                $minecraftPlayer->vault_balance = $minecraftPlayerSession->vault_balance ?? $minecraftPlayer->vault_balance;
                $minecraftPlayer->vault_groups = $minecraftPlayerSession->vault_groups ?? $minecraftPlayer->vault_groups;
                $minecraftPlayer->country_id = $minecraftPlayerSession->country_id;
                $minecraftPlayer->save();

                $player = Player::where('uuid', $minecraftPlayerSession->player_uuid)->first();
                $player->last_join_address = $minecraftPlayerSession->join_address ?? $player->last_join_address;
                $player->last_minecraft_version = $minecraftPlayerSession->minecraft_version ?? $player->last_minecraft_version;
                $player->last_seen_at = $minecraftPlayerSession->session_ended_at ?? now();
                $player->ip_address = $minecraftPlayerSession->player_ip_address;
                $player->username = $minecraftPlayerSession->player_username;
                $player->country_id = $minecraftPlayerSession->country_id;
                $player->save();
            }

            $minecraftPlayerSession->minecraft_player_id = $minecraftPlayer->id;
            $minecraftPlayerSession->save();
        }, 5);
    }
}
