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
    ];

    protected $dates = ['last_scanned_at'];

    public function minecraftPlayerStats(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JsonMinecraftPlayerStat::class);
    }

    public function minecraftPlayerAdvancements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JsonMinecraftPlayerAdvancement::class);
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
        return $this->minecraftPlayerStats()
            ->selectRaw("SUM(total_used) as total_used")
            ->selectRaw("SUM(total_mined) as total_mined")
            ->selectRaw("SUM(total_picked_up) as total_picked_up")
            ->selectRaw("SUM(total_dropped) as total_dropped")
            ->selectRaw("SUM(total_broken) as total_broken")
            ->selectRaw("SUM(total_crafted) as total_crafted")
            ->selectRaw("SUM(total_mob_kills) as total_mob_kills")
            ->selectRaw("SUM(total_player_kills) as total_player_kills")
            ->selectRaw("SUM(total_deaths) as total_deaths")
            ->selectRaw("SUM(total_walk_one_cm) as total_walk_one_cm")
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
        $max['max_used'] = $this->minecraftPlayerStats()->maxRowForCol("total_used", $this->id)->first();
        $max['max_mined'] = $this->minecraftPlayerStats()->maxRowForCol("total_mined", $this->id)->first();
        $max['max_picked_up'] = $this->minecraftPlayerStats()->maxRowForCol("total_picked_up", $this->id)->first();
        $max['max_dropped'] = $this->minecraftPlayerStats()->maxRowForCol("total_dropped", $this->id)->first();
        $max['max_broken'] = $this->minecraftPlayerStats()->maxRowForCol("total_broken", $this->id)->first();
        $max['max_crafted'] = $this->minecraftPlayerStats()->maxRowForCol("total_crafted", $this->id)->first();
        $max['max_mob_kills'] = $this->minecraftPlayerStats()->maxRowForCol("total_mob_kills", $this->id)->first();
        $max['max_player_kills'] = $this->minecraftPlayerStats()->maxRowForCol("total_player_kills", $this->id)->first();
        $max['max_deaths'] = $this->minecraftPlayerStats()->maxRowForCol("total_deaths", $this->id)->first();
        $max['max_walk_one_cm'] = $this->minecraftPlayerStats()->maxRowForCol("total_walk_one_cm", $this->id)->first();
        $max['max_fish_caught'] = $this->minecraftPlayerStats()->maxRowForCol("total_fish_caught", $this->id)->first();
        $max['max_enchant_item'] = $this->minecraftPlayerStats()->maxRowForCol("total_enchant_item", $this->id)->first();
        $max['max_play_one_minute'] = $this->minecraftPlayerStats()->maxRowForCol("total_play_one_minute", $this->id)->first();
        $max['max_sleep_in_bed'] = $this->minecraftPlayerStats()->maxRowForCol("total_sleep_in_bed", $this->id)->first();
        $max['max_jumps'] = $this->minecraftPlayerStats()->maxRowForCol("total_jumps", $this->id)->first();
        $max['max_leave_game'] = $this->minecraftPlayerStats()->maxRowForCol("total_leave_game", $this->id)->first();
        $max['max_money'] = $this->minecraftPlayerStats()->maxRowForCol("money", $this->id)->first();

        return $max;
    }
}
