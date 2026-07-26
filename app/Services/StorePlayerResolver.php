<?php

namespace App\Services;

use App\Models\Player;
use App\Models\User;
use App\Settings\StoreSettings;
use App\Utils\Helpers\MinecraftUuidUtils;
use Illuminate\Validation\ValidationException;

/**
 * Works out which Minecraft identity a purchase is for.
 *
 * Orders snapshot a uuid and username rather than requiring a Player row, so a purchase can be
 * made for someone who has never visited the website. When a Player row does exist it is linked
 * as well, which is what lets purchase limits and grants be looked up later.
 */
class StorePlayerResolver
{
    public function __construct(private StoreSettings $settings) {}

    /**
     * @return array{player: Player|null, uuid: string, username: string}
     *
     * @throws ValidationException
     */
    public function resolve(string $username, ?User $user = null): array
    {
        $username = trim($username);

        if ($username === '') {
            $this->fail(__('Enter the Minecraft username to deliver to.'));
        }

        // A logged-in buyer purchasing for one of their own linked players is authoritative:
        // the link was already proven in-game via the OTP flow, so no lookup is needed.
        if ($user) {
            $linked = $user->players->first(fn (Player $player) => strcasecmp($player->username ?? '', $username) === 0);

            if ($linked) {
                return ['player' => $linked, 'uuid' => $linked->uuid, 'username' => $linked->username];
            }
        }

        $existing = Player::whereRaw('LOWER(username) = ?', [mb_strtolower($username)])->first();

        if ($existing) {
            return ['player' => $existing, 'uuid' => $existing->uuid, 'username' => $existing->username];
        }

        return $this->settings->mojang_username_verification
            ? $this->resolveViaMojang($username)
            : $this->resolveOffline($username);
    }

    /**
     * @return array{player: Player|null, uuid: string, username: string}
     */
    private function resolveViaMojang(string $username): array
    {
        $undashed = MinecraftApiService::playerUsernameToUuid($username);

        if (! $undashed) {
            $this->fail(__('No Minecraft account named ":username" was found.', ['username' => $username]));
        }

        // Mojang returns an undashed 32-char id; players.uuid is a dashed char(36) and every API
        // validates it with the uuid rule, so it must be normalised at this boundary.
        $uuid = MinecraftUuidUtils::toDashed($undashed);

        if (! $uuid) {
            $this->fail(__('Could not verify that Minecraft username. Please try again.'));
        }

        // The lookup may resolve to a player already known under a previous name.
        $player = Player::where('uuid', $uuid)->first();

        return ['player' => $player, 'uuid' => $uuid, 'username' => $player->username ?? $username];
    }

    /**
     * Offline/cracked servers have no Mojang account to verify against, so the UUID is derived
     * the same way the server itself derives it.
     *
     * @return array{player: Player|null, uuid: string, username: string}
     */
    private function resolveOffline(string $username): array
    {
        if (! preg_match('/^[A-Za-z0-9_]{3,16}$/', $username)) {
            $this->fail(__('That does not look like a valid Minecraft username.'));
        }

        $uuid = MinecraftUuidUtils::offlineUuid($username);

        return [
            'player' => Player::where('uuid', $uuid)->first(),
            'uuid' => $uuid,
            'username' => $username,
        ];
    }

    /**
     * @throws ValidationException
     */
    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['player_username' => $message]);
    }
}
