<?php

use App\Http\Controllers\Api\ApiAccountLinkController;
use App\Http\Controllers\Api\ApiBanWardenController;
use App\Http\Controllers\Api\ApiCommandQueueController;
use App\Http\Controllers\Api\ApiMinecraftPlayerIntelController;
use App\Http\Controllers\Api\ApiMinecraftServerIntelController;
use App\Http\Controllers\Api\ApiPlayerController;
use App\Http\Controllers\Api\ApiRankController;
use App\Http\Controllers\Api\ApiServerChatlogController;
use App\Http\Controllers\Api\ApiServerConsolelogController;
use App\Http\Controllers\Api\ApiUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
// });

Route::middleware(['auth.api-key'])->group(function () {
    Route::post('v1/server/chat', [ApiServerChatlogController::class, 'store'])->name('api.server.chat');
    Route::post('v1/server/console', [ApiServerConsolelogController::class, 'store'])->name('api.server.console');

    Route::post('v1/account-link/verify', [ApiAccountLinkController::class, 'verify'])->name('api.account-link.verify');

    Route::post('v1/player/whois', [ApiPlayerController::class, 'postWhoisPlayer'])->name('api.player.whois');
    Route::post('v1/player/data', [ApiPlayerController::class, 'postFetchPlayerData'])->name('api.player.data');

    // Discord Rank Sync: list users which have a player of given rank linked, with their discord id.
    Route::get('v1/ranks/{rank}/members', [ApiRankController::class, 'getRankMembers'])->name('api.ranks.members');

    // Discord integration: find a MineTrax user and their linked Minecraft users.
    Route::get('v1/users/{discordId}', [ApiUserController::class, 'showByDiscordId'])->name('api.users.discord.show');

    // Command Queue: queue a command for execution.
    Route::post('v1/command-queue', [ApiCommandQueueController::class, 'store'])->name('api.command-queue.store');
    Route::get('v1/command-queue/{requestId}', [ApiCommandQueueController::class, 'show'])->name('api.command-queue.show');

    // Intel APIs: used by Server to Report Player/Server Intelligence Data.
    Route::post('v1/intel/player/session-init', [ApiMinecraftPlayerIntelController::class, 'postSessionInit'])->name('api.intel.player.session-init');
    Route::post('v1/intel/player/report/event', [ApiMinecraftPlayerIntelController::class, 'postReportEvent'])->name('api.intel.player.report.event');
    Route::post('v1/intel/player/report/pvp-kill', [ApiMinecraftPlayerIntelController::class, 'postReportPvpKill'])->name('api.intel.player.report.pvp');
    Route::post('v1/intel/player/report/death', [ApiMinecraftPlayerIntelController::class, 'postReportDeath'])->name('api.intel.player.report.death');
    // Route::post('v1/intel/player/report/pve-kill', [\App\Http\Controllers\Api\ApiMinecraftPlayerIntelController::class, 'postReportPveKill'])->name('api.intel.player.report.pve');  POSTPONED because if a player do xp farming it will cause too much load and create too much data? we will see

    // BanWarden
    Route::post('v1/banwarden/sync/punishment', [ApiBanWardenController::class, 'postSyncPunishments'])->name('api.banwarden.sync.punishment');
    Route::post('v1/banwarden/report/punishment', [ApiBanWardenController::class, 'postReportPunishment'])->name('api.banwarden.report.punishment');

    Route::post('v1/intel/server/report', [ApiMinecraftServerIntelController::class, 'postReport'])->name('api.intel.server.report');
    // Intel APIs ends
});
