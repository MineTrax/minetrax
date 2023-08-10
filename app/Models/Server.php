<?php

namespace App\Models;

use App\Enums\ServerType;
use App\Enums\ServerVersion;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Server extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'settings' => 'json',
        'type' => ServerType::class,
        'minecraft_version' => ServerVersion::class,
        'is_server_intel_enabled' => 'boolean',
        'is_player_intel_enabled' => 'boolean',
        'is_ingame_chat_enabled' => 'boolean',
        'last_scanned_at' => 'datetime',
    ];

    public function minecraftPlayers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MinecraftPlayer::class);
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function serverChatlog(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServerChatlog::class);
    }

    public function serverConsolelog(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServerConsolelog::class);
    }

    public function getAggregatedTotalJsonStats()
    {
        return $this->minecraftPlayers()
            ->selectRaw("SUM(items_used) as items_used")
            ->selectRaw("SUM(items_mined) as items_mined")
            ->selectRaw("SUM(items_picked_up) as items_picked_up")
            ->selectRaw("SUM(items_dropped) as items_dropped")
            ->selectRaw("SUM(items_broken) as items_broken")
            ->selectRaw("SUM(items_crafted) as items_crafted")
            ->selectRaw("SUM(mob_kills) as mob_kills")
            ->selectRaw("SUM(player_kills) as player_kills")
            ->selectRaw("SUM(deaths) as deaths")
            ->selectRaw("SUM(distance_traveled) as distance_traveled")
            ->selectRaw("SUM(fish_caught) as fish_caught")
            ->selectRaw("SUM(items_enchanted) as items_enchanted")
            ->selectRaw("SUM(play_time) as play_time")
            ->selectRaw("SUM(times_slept_in_bed) as times_slept_in_bed")
            ->selectRaw("SUM(times_jumped) as times_jumped")
            ->selectRaw("SUM(afk_time) as afk_time")
            ->selectRaw("SUM(raids_won) as raids_won")
            ->selectRaw("SUM(vault_balance) as vault_balance")
            ->first();
    }

    public function getAggregatedMaxJsonStats()
    {
        $max['max_used'] = $this->minecraftPlayers()->maxRowForCol("items_used", $this->id)->first();
        $max['max_mined'] = $this->minecraftPlayers()->maxRowForCol("items_mined", $this->id)->first();
        $max['max_picked_up'] = $this->minecraftPlayers()->maxRowForCol("items_picked_up", $this->id)->first();
        $max['max_dropped'] = $this->minecraftPlayers()->maxRowForCol("items_dropped", $this->id)->first();
        $max['max_broken'] = $this->minecraftPlayers()->maxRowForCol("items_broken", $this->id)->first();
        $max['max_crafted'] = $this->minecraftPlayers()->maxRowForCol("items_crafted", $this->id)->first();
        $max['max_mob_kills'] = $this->minecraftPlayers()->maxRowForCol("mob_kills", $this->id)->first();
        $max['max_player_kills'] = $this->minecraftPlayers()->maxRowForCol("player_kills", $this->id)->first();
        $max['max_deaths'] = $this->minecraftPlayers()->maxRowForCol("deaths", $this->id)->first();
        $max['max_distance_traveled'] = $this->minecraftPlayers()->maxRowForCol("distance_traveled", $this->id)->first();
        $max['max_fish_caught'] = $this->minecraftPlayers()->maxRowForCol("fish_caught", $this->id)->first();
        $max['max_items_enchanted'] = $this->minecraftPlayers()->maxRowForCol("items_enchanted", $this->id)->first();
        $max['max_play_time'] = $this->minecraftPlayers()->maxRowForCol("play_time", $this->id)->first();
        $max['max_sleep_in_bed'] = $this->minecraftPlayers()->maxRowForCol("times_slept_in_bed", $this->id)->first();
        $max['max_times_jumped'] = $this->minecraftPlayers()->maxRowForCol("times_jumped", $this->id)->first();
        $max['max_afk_time'] = $this->minecraftPlayers()->maxRowForCol("afk_time", $this->id)->first();
        $max['max_raids_won'] = $this->minecraftPlayers()->maxRowForCol("raids_won", $this->id)->first();
        $max['max_vault_balance'] = $this->minecraftPlayers()->maxRowForCol("vault_balance", $this->id)->first();
        return $max;
    }
}
