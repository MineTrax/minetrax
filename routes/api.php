<?php

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware(['auth.api-key'])->group(function () {
    Route::post('v1/server/chat', [\App\Http\Controllers\Api\ApiServerChatlogController::class, 'store'])->name('api.server.chat');
    Route::post('v1/server/console', [\App\Http\Controllers\Api\ApiServerConsolelogController::class, 'store'])->name('api.server.console');

    Route::post('v1/account-link/init', [\App\Http\Controllers\Api\ApiAccountLinkController::class, 'init'])->name('api.account-link.init');

    Route::post('v1/player/whois', [\App\Http\Controllers\Api\ApiPlayerController::class, 'postWhoisPlayer'])->name('api.player.whois');
    Route::post('v1/player/data', [\App\Http\Controllers\Api\ApiPlayerController::class, 'postFetchPlayerData'])->name('api.player.data');

    // Intel APIs: used by Server to Report Player/Server Intelligence Data.
    Route::post('v1/intel/player/session-init', [\App\Http\Controllers\Api\ApiMinecraftPlayerIntelController::class, 'postSessionInit'])->name('api.intel.player.session-init');
    Route::post('v1/intel/player/report/event', [\App\Http\Controllers\Api\ApiMinecraftPlayerIntelController::class, 'postReportEvent'])->name('api.intel.player.report.event');
    Route::post('v1/intel/player/report/pvp-kill', [\App\Http\Controllers\Api\ApiMinecraftPlayerIntelController::class, 'postReportPvpKill'])->name('api.intel.player.report.pvp');
    Route::post('v1/intel/player/report/death', [\App\Http\Controllers\Api\ApiMinecraftPlayerIntelController::class, 'postReportDeath'])->name('api.intel.player.report.death');
    // Route::post('v1/intel/player/report/pve-kill', [\App\Http\Controllers\Api\ApiMinecraftPlayerIntelController::class, 'postReportPveKill'])->name('api.intel.player.report.pve');  POSTPONED because if a player do xp farming it will cause too much load and create too much data? we will see

    Route::post('v1/intel/server/report', [\App\Http\Controllers\Api\ApiMinecraftServerIntelController::class, 'postReport'])->name('api.intel.server.report');
    // Intel APIs ends
});
