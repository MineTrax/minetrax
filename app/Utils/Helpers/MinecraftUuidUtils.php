<?php

namespace App\Utils\Helpers;

use Illuminate\Support\Str;

/**
 * Helpers for converting between the UUID forms used across Minecraft.
 *
 * Mojang's API returns undashed 32-char ids, while the `players.uuid` column is a dashed
 * `char(36)` and every API endpoint validates it with Laravel's `uuid` rule. Anything crossing
 * that boundary must be normalised here.
 */
class MinecraftUuidUtils
{
    /**
     * Convert an undashed 32-char UUID to its canonical dashed 36-char form.
     *
     * Already-dashed input is returned untouched; anything that is not a 32-char hex string
     * returns null rather than producing a malformed UUID.
     */
    public static function toDashed(?string $uuid): ?string
    {
        if (! $uuid) {
            return null;
        }

        if (Str::isUuid($uuid)) {
            return strtolower($uuid);
        }

        $stripped = strtolower(str_replace('-', '', $uuid));

        if (! preg_match('/^[0-9a-f]{32}$/', $stripped)) {
            return null;
        }

        return implode('-', [
            substr($stripped, 0, 8),
            substr($stripped, 8, 4),
            substr($stripped, 12, 4),
            substr($stripped, 16, 4),
            substr($stripped, 20, 12),
        ]);
    }

    /**
     * Convert a dashed UUID to Mojang's undashed 32-char form.
     */
    public static function toUndashed(?string $uuid): ?string
    {
        if (! $uuid) {
            return null;
        }

        $stripped = strtolower(str_replace('-', '', $uuid));

        return preg_match('/^[0-9a-f]{32}$/', $stripped) ? $stripped : null;
    }

    /**
     * Derive the offline-mode UUID for a username, as cracked/offline servers compute it.
     *
     * Mirrors Java's `UUID.nameUUIDFromBytes("OfflinePlayer:<name>".getBytes(UTF_8))` — an MD5
     * hash with the version and variant bits rewritten. Note this is NOT an RFC 4122 v3 UUID:
     * Java hashes the raw bytes with no namespace prefix, so `Str::uuid3()`-style helpers would
     * produce a different (wrong) value.
     */
    public static function offlineUuid(string $username): string
    {
        $hash = md5('OfflinePlayer:'.$username, true);

        $hash[6] = chr((ord($hash[6]) & 0x0F) | 0x30); // version 3
        $hash[8] = chr((ord($hash[8]) & 0x3F) | 0x80); // RFC 4122 variant

        return self::toDashed(bin2hex($hash));
    }
}
