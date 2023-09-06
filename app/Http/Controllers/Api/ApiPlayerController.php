<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;

class ApiPlayerController extends Controller
{
    /**
     * Whois Detail of a Player.
     *
     * Username Case:
     * 1. If provided username has exactly one result then return it.
     * 2. If provided username has 0 record then return null
     * 3. If provided username has more than 1 result return first 5-10 matches
     *
     * IP:
     * If there is ip_address provided then also return IP address whois details
     * no matter what is result from above cases.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function postWhoisPlayer(Request $request)
    {
        $request->validate([
            'username' => ['required_without:uuid', 'string', 'min:3'],
            'uuid' => ['required_without:username', 'uuid'],
            'ip_address' => ['sometimes', 'nullable', 'ip'],
        ]);

        $username = $request->uuid ?? $request->username;
        $columnName = $request->uuid ? 'uuid' : 'username';

        // Count the number of matches and return 1 if there is an exact match
        $playerFoundCount = Player::where($columnName, 'LIKE', $username)->whereNotNull($columnName)->count();
        if ($playerFoundCount && $request->only_exact_result) {
            $playerFoundCount = 1;
        }
        if (! $playerFoundCount && ! $request->only_exact_result) {
            $playerFoundCount = Player::where($columnName, 'LIKE', '%'.$username.'%')->whereNotNull($columnName)->count();
        }

        $whoisData['count'] = $playerFoundCount;
        $players = collect();
        $whoisData['players'] = $players;
        switch ($playerFoundCount) {
            case 0:
                break;
            case 1:
                $player = Player::with('users:id,name,username')->where($columnName, 'LIKE', '%'.$username.'%')->whereNotNull($columnName)->orderBy('position')->first();
                if ($player->users->count()) {
                    $player->user = $player->users->first();
                    unset($player->users);
                }
                $players = $players->push($player);
                break;
            default:
                $players = Player::where($columnName, 'LIKE', '%'.$username.'%')->whereNotNull($columnName)->limit(10)->orderBy('position')->get();
                break;
        }

        // If there is player
        if ($playerFoundCount > 0) {
            $whoisData['players'] = $players->map(function ($pl) {
                return [
                    'username' => $pl->username,
                    'uuid' => $pl->uuid,
                    'position' => $pl->position,
                    'rating' => $pl->rating,
                    'total_score' => $pl->total_score,
                    'last_seen_at' => $pl->last_seen_at?->diffForHumans(),
                    'rank' => $pl->rank ? $pl->rank->name : null,
                    'country' => $pl->country ? $pl->country->name : 'Terra Incognita',
                    'user' => $pl->user ? '@'.$pl->user->username : null,
                    'url' => route('player.show', [$pl->username ?? $pl->uuid]),
                ];
            });
        }

        // IF IP IS SENT THEN RETURN ITS WHOIS INFORMATION
        $geoResponse = null;
        if ($request->ip_address) {
            $country = geoip($request->ip_address);
            $geoResponse = [
                'ip' => $country->ip,
                'iso_code' => $country->iso_code,
                'country' => $country->country,
                'city' => $country->city,
                'state_name' => $country->state_name,
            ];
        }
        $whoisData['geo'] = $geoResponse;

        return response()->json([
            'status' => 'success',
            'message' => 'Ok',
            'data' => $whoisData,
        ]);
    }

    public function postFetchPlayerData(Request $request)
    {
        $request->validate([
            'uuid' => ['required', 'uuid'],
            'username' => ['required', 'string', 'min:3'],
        ]);

        $responseData = [
            'uuid' => $request->uuid,
            'username' => $request->username,
            'is_verified' => false,
            'daily_rewards_claimed_at' => null,
            'player_id' => null,
        ];

        $player = Player::where('uuid', $request->uuid)
            ->with(['users:id,name,username,profile_photo_path,verified_at', 'rank:id,shortname,name', 'country:id,name,iso_code'])
            ->first();

        if ($player) {
            $responseData['is_verified'] = $player->users->count() > 0;
            $responseData['player_id'] = $player->id;
            $responseData['rating'] = $player->rating;
            $responseData['total_score'] = $player->total_score;
            $responseData['position'] = $player->position;
            $responseData['first_seen_at'] = $player->first_seen_at?->diffForHumans();
            $responseData['last_seen_at'] = $player->last_seen_at?->diffForHumans();
            $responseData['rank'] = $player->rank ?? null;
            $responseData['country'] = $player->country ?? null;
            $responseData['profile_link'] = route('player.show', [$player->uuid]);
        }

        return response()->json($responseData);
    }
}
