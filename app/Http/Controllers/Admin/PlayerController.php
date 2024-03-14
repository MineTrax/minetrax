<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MinecraftPlayerSession;
use App\Models\Player;
use DB;

class PlayerController extends Controller
{
    public function destroy(Player $player)
    {
        $this->authorize('destroy', $player);

        DB::transaction(function () use ($player) {
            MinecraftPlayerSession::where('player_uuid', $player->uuid)->delete();
            $player->minecraftPlayers()->delete();
            $player->delete();
        });

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Player Deleted!'), 'body' => __('All intel data related to the player is deleted.')]]);
    }
}
