<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use URL;

class ApiAccountLinkController extends Controller
{
    /**
     * Generate a signed account link url
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function init(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'uuid' => 'required|uuid',
            'server_id' => 'required|exists:servers,id'
        ]);

        // Find player with UUID if not found then tell player to wait
        $player = Player::where('uuid', $request->uuid)->first();
        if (!$player) {
            return response()->json([
                'status' => 'error',
                'type' => 'not-found',
                'message' => __('Player not found'),
            ], 200);
        }

        // Check if player is already linked to some user
        if ($player->users()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'type' => 'player-already-linked',
                'message' => __('Player already linked to a user'),
            ], 200);
        }

        $url = URL::temporarySignedRoute(
            'account-link.verify', now()->addMinutes(30), ['uuid' => $request->uuid, 'server' => $request->server_id]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Ok',
            'data' => $url
        ]);
    }
}
