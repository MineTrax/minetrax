<?php

namespace App\Http\Controllers;

use App\Jobs\AccountLinkAfterSuccessCommandJob;
use App\Models\Player;
use App\Models\Server;
use App\Settings\PluginSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountLinkController extends Controller
{
    public function verify($uuid, Server $server, Request $request, PluginSettings $pluginSettings): \Illuminate\Http\RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect()->home()
                ->with(['toast' => ['type' => 'danger', 'title' => 'Link expired or invalid!', 'body' => 'Please request a fresh link and try again.', 'milliseconds' => 10000]]);
        }

        // Find player with UUID if not found then tell user to wait for sometime till his player become visible in website
        $player = Player::where('uuid', $uuid)->first();
        if (!$player) {
            return redirect()->home()
                ->with(['toast' => ['type' => 'warning', 'title' => 'Player not found in database!', 'body' => 'Please wait for sometime for server to update its player database.', 'milliseconds' => 10000]]);
        }

        // Player found. check if this player is already linked with a user.
        if ($player->users()->count() > 0) {
            return redirect()->home()
                ->with(['toast' => ['type' => 'danger', 'title' => 'Player already linked to a user!', 'body' => 'If you own this user please unlink it from your another account or contact administrator.', 'milliseconds' => 10000]]);
        }

        // Check if current user has enough available slot to link the player.
        $user = $request->user();
        $max_slots = $pluginSettings->max_players_link_per_account; // Total number of players that can be linked to account
        if($user->players()->count() >= $max_slots) {
            return redirect()->home()
                ->with(['toast' => ['type' => 'danger', 'title' => "User already have max {$max_slots} players linked!", 'body' => 'If you want to link this player please unlink a player.', 'milliseconds' => 10000]]);
        }

        // @TODO: All good. return a view to confirm the link.
        // return Inertia::render('User/ConfirmLinkUserToPlayer', ['player' => $player]);

        $user->players()->attach($player);

        // If player username and user's username are same then mark user as verified
        if($user->username == $player->username && $user->email_verified_at != null) {
            $user->verified_at = now();
            $user->save();
        }

        // Run command to give this player the reward according to Plugin setting if enabled
        AccountLinkAfterSuccessCommandJob::dispatch($player, $server);

        return redirect()->home()
            ->with(['toast' => ['type' => 'success', 'title' => "Played linked successfully!", 'body' => 'This player is now linked to your account.', 'milliseconds' => 10000]]);
    }

    public function listMyPlayers(Request $request, PluginSettings $pluginSettings)
    {
        $linkedPlayers = $request->user()->players()->with(['country', 'rank:id,name,shortname'])->get();
        return Inertia::render('User/ListLinkedPlayer', [
            'linkedPlayers' => $linkedPlayers,
            'maxPlayerPerUser' =>  $pluginSettings->max_players_link_per_account
        ]);
    }

    public function unlink(Player $player, Request $request)
    {
        // Make sure the player is linked to current user who is running to unlink
        $userHasPlayer = $request->user()->players()->where('player_id', $player->id)->exists();
        if(!$userHasPlayer) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'danger', 'title' => "Player not found!", 'body' => 'No player with that ID found linked to your account.', 'milliseconds' => 10000]]);
        }

        // Unlink the player
        $request->user()->players()->detach($player->id);

        // Return redirect back with flash
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => "Played unlinked successfully!", 'body' => 'Player has been removed from your account.', 'milliseconds' => 10000]]);
    }
}
