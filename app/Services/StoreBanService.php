<?php

namespace App\Services;

use App\Models\StoreBan;
use App\Models\User;

/**
 * Blocks checkout for banned identities.
 *
 * A ban may target any combination of user, player UUID, IP or email, and any single match is
 * enough. That breadth matters because the usual reason for a store ban is a chargeback, and the
 * same person will happily return as a guest from the same IP.
 */
class StoreBanService
{
    /**
     * The matching ban, if any.
     */
    public function match(?User $user, ?string $playerUuid, ?string $ip, ?string $email): ?StoreBan
    {
        $query = StoreBan::active();

        $query->where(function ($q) use ($user, $playerUuid, $ip, $email) {
            // whereRaw(false) is not needed: at least one identity is always supplied in practice,
            // but each clause is added only when its value exists so a null never matches a null
            // column and bans everybody.
            if ($user) {
                $q->orWhere('user_id', $user->id);
            }

            if ($playerUuid) {
                $q->orWhere('player_uuid', $playerUuid);
            }

            if ($ip) {
                $q->orWhere('ip_address', $ip);
            }

            if ($email) {
                $q->orWhereRaw('LOWER(email) = ?', [mb_strtolower($email)]);
            }
        });

        // Guard against the all-null case producing an unconstrained query.
        if (! $user && ! $playerUuid && ! $ip && ! $email) {
            return null;
        }

        return $query->first();
    }

    public function isBanned(?User $user, ?string $playerUuid, ?string $ip, ?string $email): bool
    {
        return $this->match($user, $playerUuid, $ip, $email) !== null;
    }

    /**
     * Raise an automatic ban, used when a chargeback lands.
     */
    public function banForChargeback(?User $user, ?string $playerUuid, ?string $ip, ?string $email, string $reason): StoreBan
    {
        return StoreBan::create([
            'user_id' => $user?->id,
            'player_uuid' => $playerUuid,
            'ip_address' => $ip,
            'email' => $email,
            'reason' => $reason,
            'is_automatic' => true,
        ]);
    }
}
