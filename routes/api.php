<?php

use App\Http\Controllers\Api\ApiAccountLinkController;
use App\Http\Controllers\Api\ApiBanWardenController;
use App\Http\Controllers\Api\ApiMinecraftPlayerIntelController;
use App\Http\Controllers\Api\ApiMinecraftServerIntelController;
use App\Http\Controllers\Api\ApiPlayerController;
use App\Http\Controllers\Api\ApiServerChatlogController;
use App\Http\Controllers\Api\ApiServerConsolelogController;
use App\Http\Controllers\Api\ApiStoreWebhookController;
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

// Store payment gateway callbacks. Deliberately outside the auth.api-key group: a gateway cannot
// compute MineTrax's own HMAC, so each driver verifies the vendor's signature over the raw body
// instead. One dynamic {gateway} segment means a new driver needs no route of its own.
Route::post('webhooks/store/{gateway}', [ApiStoreWebhookController::class, 'handle'])
    ->middleware('throttle:store-webhook')
    ->name('api.store.webhook');

Route::middleware(['auth.api-key'])->group(function () {
    Route::post('v1/server/chat', [ApiServerChatlogController::class, 'store'])->name('api.server.chat');
    Route::post('v1/server/console', [ApiServerConsolelogController::class, 'store'])->name('api.server.console');

    Route::post('v1/account-link/verify', [ApiAccountLinkController::class, 'verify'])->name('api.account-link.verify');

    Route::post('v1/player/whois', [ApiPlayerController::class, 'postWhoisPlayer'])->name('api.player.whois');
    Route::post('v1/player/data', [ApiPlayerController::class, 'postFetchPlayerData'])->name('api.player.data');

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
