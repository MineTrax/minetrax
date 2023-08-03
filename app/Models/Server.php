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
            ->selectRaw("SUM(total_used) as total_used")
            ->selectRaw("SUM(total_mined) as total_mined")
            ->selectRaw("SUM(total_picked_up) as total_picked_up")
            ->selectRaw("SUM(total_dropped) as total_dropped")
            ->selectRaw("SUM(total_broken) as total_broken")
            ->selectRaw("SUM(total_crafted) as total_crafted")
            ->selectRaw("SUM(total_mob_kills) as total_mob_kills")
            ->selectRaw("SUM(total_player_kills) as total_player_kills")
            ->selectRaw("SUM(total_deaths) as total_deaths")
            ->selectRaw("SUM(distance_traveled) as distance_traveled")
            ->selectRaw("SUM(total_fish_caught) as total_fish_caught")
            ->selectRaw("SUM(total_enchant_item) as total_enchant_item")
            ->selectRaw("SUM(total_play_one_minute) as total_play_one_minute")
            ->selectRaw("SUM(total_sleep_in_bed) as total_sleep_in_bed")
            ->selectRaw("SUM(total_jumps) as total_jumps")
            ->selectRaw("SUM(total_leave_game) as total_leave_game")
            ->selectRaw("SUM(money) as total_money")
            ->first();
    }

    public function getAggregatedMaxJsonStats()
    {
        $max['max_used'] = $this->minecraftPlayers()->maxRowForCol("total_used", $this->id)->first();
        $max['max_mined'] = $this->minecraftPlayers()->maxRowForCol("total_mined", $this->id)->first();
        $max['max_picked_up'] = $this->minecraftPlayers()->maxRowForCol("total_picked_up", $this->id)->first();
        $max['max_dropped'] = $this->minecraftPlayers()->maxRowForCol("total_dropped", $this->id)->first();
        $max['max_broken'] = $this->minecraftPlayers()->maxRowForCol("total_broken", $this->id)->first();
        $max['max_crafted'] = $this->minecraftPlayers()->maxRowForCol("total_crafted", $this->id)->first();
        $max['max_mob_kills'] = $this->minecraftPlayers()->maxRowForCol("total_mob_kills", $this->id)->first();
        $max['max_player_kills'] = $this->minecraftPlayers()->maxRowForCol("total_player_kills", $this->id)->first();
        $max['max_deaths'] = $this->minecraftPlayers()->maxRowForCol("total_deaths", $this->id)->first();
        $max['max_walk_one_cm'] = $this->minecraftPlayers()->maxRowForCol("total_walk_one_cm", $this->id)->first();
        $max['max_fish_caught'] = $this->minecraftPlayers()->maxRowForCol("total_fish_caught", $this->id)->first();
        $max['max_enchant_item'] = $this->minecraftPlayers()->maxRowForCol("total_enchant_item", $this->id)->first();
        $max['max_play_one_minute'] = $this->minecraftPlayers()->maxRowForCol("total_play_one_minute", $this->id)->first();
        $max['max_sleep_in_bed'] = $this->minecraftPlayers()->maxRowForCol("total_sleep_in_bed", $this->id)->first();
        $max['max_jumps'] = $this->minecraftPlayers()->maxRowForCol("total_jumps", $this->id)->first();
        $max['max_leave_game'] = $this->minecraftPlayers()->maxRowForCol("total_leave_game", $this->id)->first();
        $max['max_money'] = $this->minecraftPlayers()->maxRowForCol("money", $this->id)->first();

        return $max;
    }
}
