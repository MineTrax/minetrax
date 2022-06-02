<?php

namespace App\Models;

use App\Settings\PlayerSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JetBrains\PhpStorm\Pure;
use Spatie\Searchable\Searchable;

class Player extends BaseModel implements Searchable
{
    use HasFactory;

    protected $dates = ['last_mps_updated_at', 'last_seen_at', 'first_seen_at'];

    protected $hidden = ['ip_address', 'account_link_after_success_command_run_count'];

    protected $appends = ['avatar_url', 'is_active'];

    protected $with = ['rank:id,shortname,name', 'country:id,iso_code,flag,name'];

    public function getSearchResult(): \Spatie\Searchable\SearchResult
    {
        $url = route('player.show', $this->uuid);

        return new \Spatie\Searchable\SearchResult(
            $this,
            $this->username,
            $url
        );
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function minecraftPlayerStats(): HasMany
    {
        return $this->hasMany(JsonMinecraftPlayerStat::class);
    }

    public function minecraftPlayerAdvancements(): HasMany
    {
        return $this->hasMany(JsonMinecraftPlayerAdvancement::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'player_user')->withTimestamps();
    }

    public function getAvatarUrlAttribute(): string
    {
        return route('player.avatar.get', [$this->uuid, 'size' => 100]);
    }

    public function getIsActiveAttribute(): bool
    {
        $playerSettings = app(PlayerSettings::class);
        return $playerSettings->last_seen_day_for_active == -1 || $this->last_seen_at >= now()->subDays($playerSettings->last_seen_day_for_active);
    }

    public function getRatingAttribute($value)
    {
        if($value === 0 || $value != null)
            return  (int)round($value);

        return null;
    }
}
