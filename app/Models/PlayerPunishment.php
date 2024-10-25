<?php

namespace App\Models;

use App\Enums\PlayerPunishmentType;
use App\Traits\HasCommentsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PlayerPunishment extends BaseModel implements HasMedia
{
    use HasFactory, HasCommentsTrait, InteractsWithMedia;

    protected $casts = [
        'type' => PlayerPunishmentType::class,
        'meta' => 'array',
        'insights' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'removed_at' => 'datetime',
        'is_ipban' => 'boolean',
    ];

    protected $appends = [
        'is_active',
        'masked_ip_address',
    ];

    protected $hidden = [
        'ip_address',
        'plugin_name',
        'plugin_punishment_id',
        'meta',
        'origin_server_name',
        'origin_server_id',
    ];

    public function scopeStatus($query, $status)
    {
        if ($status === 'active') {
            return $query->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>', now());
            })
                ->where('removed_at', null)
                ->where('type', '!=', PlayerPunishmentType::KICK);
        }

        if ($status === 'inactive') {
            return $query->where(function ($query) {
                $query->where('type', PlayerPunishmentType::KICK)
                    ->orWhere('end_at', '<=', now());
            })->orWhere('removed_at', '!=', null);
        }

        return $query;
    }

    public function getIsActiveAttribute()
    {
        return $this->type != PlayerPunishmentType::KICK && $this->removed_at === null && ($this->end_at === null || $this->end_at > now());
    }

    public function getMaskedIpAddressAttribute()
    {
        $hideCounty = config('minetrax.hide_country_for_privacy');
        if ($hideCounty) {
            return 'xx.xx.xx.xx';
        }
        return preg_replace('/(\d{1,3})\.\d{1,3}\.\d{1,3}\.\d{1,3}/', '$1.xx.xx.xx', $this->ip_address);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function victimPlayer()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function creatorPlayer()
    {
        return $this->belongsTo(Player::class, 'creator_uuid', 'uuid');
    }

    public function removerPlayer()
    {
        return $this->belongsTo(Player::class, 'remover_uuid', 'uuid');
    }

    public function scopedServer()
    {
        return $this->belongsTo(Server::class, 'scope_server_id');
    }

    public function originServer()
    {
        return $this->belongsTo(Server::class, 'origin_server_id');
    }
}
