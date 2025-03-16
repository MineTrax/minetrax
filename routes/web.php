<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

/**
 * GENERAL SECTION / NO LOGIN
 * Note: We need forbid-banned-user in no-auth section too so that if user login, it automatically get redirected to banned page
 */
Route::middleware(['forbid-banned-user', 'redirect-uncompleted-user'])->group(function () {
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'home'])->name('home');
    Route::get('news', [\App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
    Route::get('news/{news:slug}', [\App\Http\Controllers\NewsController::class, 'show'])->name('news.show');
    Route::get('news/{news}/comment', [\App\Http\Controllers\NewsController::class, 'indexComment'])->name('news.comment.index');
    Route::get('post', [\App\Http\Controllers\PostController::class, 'index'])->name('post.index');
    Route::get('post/{post}', [\App\Http\Controllers\PostController::class, 'show'])->name('post.show');
    Route::get('post/user/{user:username}', [\App\Http\Controllers\PostController::class, 'indexForUser'])->name('post.user.index');
    Route::get('post/{post}/comment', [\App\Http\Controllers\PostController::class, 'indexComment'])->name('post.comment.index');
    Route::get('stats', [\App\Http\Controllers\PlayerController::class, 'index'])->name('player.index');
    Route::get('stats/player/{player}', [\App\Http\Controllers\PlayerController::class, 'show'])->name('player.show');
    Route::get('did-you-know', [\App\Http\Controllers\HomeController::class, 'didYouKnow'])->name('didyouknow.get');

    Route::get('server/{server}/ping', [\App\Http\Controllers\ServerController::class, 'pingServer'])->name('server.ping.get');
    Route::get('server/{server}/query', [\App\Http\Controllers\ServerController::class, 'queryServer'])->name('server.query.get');
    Route::get('server/{server}/webping', [\App\Http\Controllers\ServerController::class, 'pingServerWithWebQueryProtocol'])->name('server.webquery.ping');
    Route::get('server/{server}/webquery', [\App\Http\Controllers\ServerController::class, 'queryServerWithWebQueryProtocol'])->name('server.webquery.status');

    Route::get('@{user:username}', [\App\Http\Controllers\UserController::class, 'showProfile'])->name('user.public.get');
    Route::get('/staff-members', [\App\Http\Controllers\UserController::class, 'indexStaff'])->name('staff.index');

    Route::get('pages/{customPage:path}', [\App\Http\Controllers\CustomPageController::class, 'show'])->name('custom-page.show.long');
    Route::get('p/{customPage:path}', [\App\Http\Controllers\CustomPageController::class, 'show'])->name('custom-page.show');

    Route::get('search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');

    Route::get('auth/{provider}', [\App\Http\Controllers\SocialAuthController::class, 'redirect'])->name('social.login')->middleware('guest');
    Route::get('auth/{provider}/callback', [\App\Http\Controllers\SocialAuthController::class, 'handleCallback'])->name('social.login.callback')->middleware('guest');

    Route::get('/features', [\App\Http\Controllers\HomeController::class, 'features'])->name('features.list');
    Route::get('/version-check', [\App\Http\Controllers\HomeController::class, 'version'])->name('version.check');

    Route::get('player/avatar/{uuid}/{username?}/{textureid?}', [\App\Http\Controllers\PlayerController::class, 'getAvatarImage'])->name('player.avatar.get');
    Route::get('player/skin/{uuid}/{username?}/{textureid?}', [\App\Http\Controllers\PlayerController::class, 'getSkinImage'])->name('player.skin.get');
    Route::get('player/render/{uuid}/{username?}/{textureid?}', [\App\Http\Controllers\PlayerController::class, 'getRenderImage'])->name('player.render.get');

    Route::get('vote/{id}', [\App\Http\Controllers\HomeController::class, 'visitVotingSite'])->name('vote.visit');

    // Route::get('intel/player/{player:uuid}', [\App\Http\Controllers\PlayerIntelController::class, 'overview'])->name('player.intel.overview');
    Route::get('intel/player/{player:uuid}/sessions', [\App\Http\Controllers\PlayerIntelController::class, 'indexSession'])->name('player.intel.session.index');
    Route::get('intel/player/{player:uuid}/sessions/{session}', [\App\Http\Controllers\PlayerIntelController::class, 'showSession'])->name('player.intel.session.show');

    // Download file
    Route::get('download', [\App\Http\Controllers\DownloadController::class, 'index'])->name('download.index');
    Route::get('download/{download:slug}', [\App\Http\Controllers\DownloadController::class, 'show'])->name('download.show');
    Route::get('download/{download:slug}/download/{any?}', [\App\Http\Controllers\DownloadController::class, 'download'])->where('any', '.*')->name('download.download');

    // Custom Form
    Route::get('forms', [\App\Http\Controllers\CustomFormController::class, 'index'])->name('custom-form.index');
    Route::get('forms/{customForm:slug}', [\App\Http\Controllers\CustomFormController::class, 'show'])->name('custom-form.show');
    Route::post('forms/{customForm:slug}', [\App\Http\Controllers\CustomFormController::class, 'submit'])->name('custom-form.submit');

    // Recruitment (Public)
    Route::get('applications', [\App\Http\Controllers\RecruitmentController::class, 'index'])->name('recruitment.index');
    Route::get('applications/{recruitment:slug}', [\App\Http\Controllers\RecruitmentController::class, 'show'])->name('recruitment.show');

    // Locale
    Route::get('locale/list', [\App\Http\Controllers\LocaleController::class, 'getAvailableLocales'])->name('locale.list');
    Route::post('locale/set', [\App\Http\Controllers\LocaleController::class, 'setLocale'])->name('locale.set');

    // BanWarden
    Route::get('player/punishments', [\App\Http\Controllers\BanWardenController::class, 'index'])->name('player.punishment.index');
    Route::get('player/punishments/{playerPunishment:id}', [\App\Http\Controllers\BanWardenController::class, 'show'])->name('player.punishment.show');
    Route::get('player/punishments/{playerPunishment:id}/history', [\App\Http\Controllers\BanWardenController::class, 'indexLastPunishments'])->name('player.punishment.show.history');
    Route::get('player/punishments/{playerPunishment:id}/sessions', [\App\Http\Controllers\BanWardenController::class, 'indexLastSessions'])->name('player.punishment.show.session');
    Route::get('player/punishments/{playerPunishment:id}/alts', [\App\Http\Controllers\BanWardenController::class, 'indexAlts'])->name('player.punishment.show.alt');
    Route::get('player/punishments/{playerPunishment:id}/evidence/{evidence}', [\App\Http\Controllers\BanWardenController::class, 'showMediaEvidence'])->name('player.punishment.evidence.show');
});

/**
 * USER SECTION/LOGGED IN
 */
Route::middleware(['auth:sanctum', 'forbid-banned-user', 'redirect-uncompleted-user', 'verified-if-enabled'])->group(function () {
    // Shouts
    Route::get('shout', [\App\Http\Controllers\ShoutController::class, 'index'])->name('shout.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('shout', [\App\Http\Controllers\ShoutController::class, 'store'])->name('shout.store')->middleware('forbid-muted-user');
    Route::delete('shout/{shout}', [\App\Http\Controllers\ShoutController::class, 'destroy'])->name('shout.delete');

    // Posts
    Route::post('post', [\App\Http\Controllers\PostController::class, 'store'])->name('post.store')->middleware('forbid-muted-user');
    Route::delete('post/{post}', [\App\Http\Controllers\PostController::class, 'destroy'])->name('post.delete');
    // Post Comments
    Route::post('post/{post}/comment', [\App\Http\Controllers\PostController::class, 'postComment'])->name('post.comment.store')->middleware('forbid-muted-user');
    Route::delete('post/{post}/comment/{comment}', [\App\Http\Controllers\PostController::class, 'deleteComment'])->name('post.comment.delete');
    // Reactions
    Route::post('reaction/post/{post}/like', [\App\Http\Controllers\PostController::class, 'likePost'])->name('reaction.post.like');
    Route::post('reaction/post/{post}/unlike', [\App\Http\Controllers\PostController::class, 'unlikePost'])->name('reaction.post.unlike');

    // News Comments
    Route::post('news/{news}/comment', [\App\Http\Controllers\NewsController::class, 'postComment'])->name('news.comment.store')->middleware('forbid-muted-user');
    Route::delete('news/{news}/comment/{comment}', [\App\Http\Controllers\NewsController::class, 'deleteComment'])->name('news.comment.delete');

    // Polls
    Route::get('poll', [\App\Http\Controllers\PollController::class, 'index'])->name('poll.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('poll/{poll}/option/{option}/vote', [\App\Http\Controllers\PollController::class, 'vote'])->name('poll.vote');

    // User
    Route::post('auth/user/post-registration-setup', [\App\Http\Controllers\UserProfileController::class, 'postRegistrationSetup'])->name('auth.post-reg-setup')->withoutMiddleware(['redirect-uncompleted-user', 'verified-if-enabled']);
    Route::delete('auth/user/remove-cover', [\App\Http\Controllers\UserProfileController::class, 'deleteCoverImage'])->name('current-user-cover.destroy');
    Route::put('auth/user/notification-preferences', [\App\Http\Controllers\UserProfileController::class, 'putUpdateNotificationPreference'])->name('auth.put-notification-preferences')->withoutMiddleware('verified-if-enabled');
    Route::get('auth/user/social-accounts', [\App\Http\Controllers\SocialAuthController::class, 'indexLinked'])->name('auth.social-account.index')->withoutMiddleware('verified-if-enabled');
    Route::delete('auth/user/social-accounts/{socialAccount}', [\App\Http\Controllers\SocialAuthController::class, 'unlinkAccount'])->name('auth.social-account.delete')->withoutMiddleware('verified-if-enabled');

    // Notifications
    Route::get('user/notification', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notification.index')->withoutMiddleware(['redirect-uncompleted-user', 'verified-if-enabled']);
    Route::post('user/notification/read', [\App\Http\Controllers\NotificationController::class, 'postMarkAsRead'])->name('notification.mark-as-read')->withoutMiddleware('verified-if-enabled');

    // Account Linker
    Route::delete('account-link/remove/{player:uuid}', [\App\Http\Controllers\AccountLinkController::class, 'unlink'])->name('account-link.delete');
    Route::get('user/linked-players', [\App\Http\Controllers\AccountLinkController::class, 'listMyPlayers'])->name('linked-player.list')->withoutMiddleware(['verified-if-enabled']);

    // Skin Changer
    Route::get('user/change-player-skin', [\App\Http\Controllers\PlayerSkinController::class, 'showChangeSkin'])->name('change-player-skin.show');
    Route::post('user/change-player-skin', [\App\Http\Controllers\PlayerSkinController::class, 'postChangeSkin'])->name('change-player-skin.update');

    // Player Password Reset
    Route::get('user/reset-player-password', [\App\Http\Controllers\PlayerPasswordResetController::class, 'show'])->name('reset-player-password.show');
    Route::post('user/reset-player-password', [\App\Http\Controllers\PlayerPasswordResetController::class, 'update'])->name('reset-player-password.update');

    // Server Chatlog
    Route::get('chatlog/{server}', [\App\Http\Controllers\ServerChatlogController::class, 'index'])->name('chatlog.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('chatlog/{server}', [\App\Http\Controllers\ServerChatlogController::class, 'sendToServer'])->name('chatlog.send')->middleware(['forbid-muted-user', 'throttle:chat']);

    // Recruitment (Authenticated)
    Route::post('applications/{recruitment:slug}', [\App\Http\Controllers\RecruitmentController::class, 'submit'])->name('recruitment.submit');
    Route::get('applications/submissions/my', [\App\Http\Controllers\RecruitmentSubmissionController::class, 'index'])->name('recruitment-submission.index');
    Route::get('applications/{recruitment:slug}/submissions/{submission}', [\App\Http\Controllers\RecruitmentSubmissionController::class, 'show'])->name('recruitment-submission.show');
    Route::post('applications/{recruitment:slug}/submissions/{submission}/withdraw', [\App\Http\Controllers\RecruitmentSubmissionController::class, 'withdraw'])->name('recruitment-submission.withdraw');
    Route::get('applications/{recruitment:slug}/submissions/{submission}/messages', [\App\Http\Controllers\RecruitmentSubmissionController::class, 'indexMessages'])->name('recruitment-submission.message.index');
    Route::post('applications/{recruitment:slug}/submissions/{submission}/messages', [\App\Http\Controllers\RecruitmentSubmissionController::class, 'postMessage'])->name('recruitment-submission.message.store')->middleware('throttle:chat');

    // BanWarden (Authenticated)
    Route::delete('player/punishments/{playerPunishment:id}', [\App\Http\Controllers\BanWardenController::class, 'pardon'])->name('player.punishment.pardon');
    Route::post('player/punishments/{playerPunishment:id}/evidence', [\App\Http\Controllers\BanWardenController::class, 'createEvidence'])->name('player.punishment.evidence.store');
    Route::delete('player/punishments/{playerPunishment:id}/evidence', [\App\Http\Controllers\BanWardenController::class, 'deleteEvidence'])->name('player.punishment.evidence.delete');
});

/**
 * ADMIN SECTION
 */
Route::middleware(['auth:sanctum', 'verified-if-enabled', 'forbid-banned-user', 'staff-member', 'redirect-uncompleted-user'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Graph controller
    Route::get('/graph/online-players', [\App\Http\Controllers\Admin\GraphController::class, 'getOnlinePlayersOverTime'])->name('graph.online-players');
    Route::get('/graph/players-per-server', [\App\Http\Controllers\Admin\GraphController::class, 'getPlayersPerServer'])->name('graph.players-per-server');
    Route::get('/graph/players-per-country', [\App\Http\Controllers\Admin\GraphController::class, 'getPlayerPerCountry'])->name('graph.players-per-country');
    Route::get('/graph/network-trends-vs-month', [\App\Http\Controllers\Admin\GraphController::class, 'getNetworkTrendsMonthVsMonth'])->name('graph.network-trends-vs-month');
    Route::get('/graph/server-performance', [\App\Http\Controllers\Admin\GraphController::class, 'getServerPerformanceOverTime'])->name('graph.server-performance');
    Route::get('/graph/server-online-activity', [\App\Http\Controllers\Admin\GraphController::class, 'getServerOnlineActivityOverTime'])->name('graph.server-online-activity');
    Route::get('/graph/player-minecraft-versions', [\App\Http\Controllers\Admin\GraphController::class, 'getPlayerMinecraftVersions'])->name('graph.player-minecraft-versions');
    Route::get('/graph/player-join-addresses', [\App\Http\Controllers\Admin\GraphController::class, 'getPlayerJoinAddresses'])->name('graph.player-join-addresses');
    Route::get('/graph/player-join-addresses-timeseries', [\App\Http\Controllers\Admin\GraphController::class, 'getPlayerJoinAddressesOverTime'])->name('graph.player-join-addresses.timeseries');
    Route::get('/graph/player-minecraft-versions-timeseries', [\App\Http\Controllers\Admin\GraphController::class, 'getPlayerMinecraftVersionsOverTime'])->name('graph.player-minecraft-versions.timeseries');

    Route::get('user', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('user.index');
    //  Route::get('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('user.show');
    Route::get('user/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('user.edit');
    Route::put('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('user.update');
    Route::post('user/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('user.ban');
    Route::post('user/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('user.unban');
    Route::post('user/{user}/mute', [\App\Http\Controllers\Admin\UserController::class, 'mute'])->name('user.mute');
    Route::post('user/{user}/unmute', [\App\Http\Controllers\Admin\UserController::class, 'unmute'])->name('user.unmute');
    Route::delete('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('user.delete');

    Route::get('impersonate/{user}/take', [\App\Http\Controllers\Admin\ImpersonateController::class, 'take'])->name('impersonate.take')->withoutMiddleware(['auth:sanctum']);
    Route::get('impersonate/leave', [\App\Http\Controllers\Admin\ImpersonateController::class, 'leave'])->name('impersonate.leave')->withoutMiddleware(['auth:sanctum', 'staff-member']);

    Route::get('server', [\App\Http\Controllers\Admin\ServerController::class, 'index'])->name('server.index');
    Route::get('server/create', [\App\Http\Controllers\Admin\ServerController::class, 'create'])->name('server.create');
    Route::get('server/create-bungee', [\App\Http\Controllers\Admin\ServerController::class, 'createBungee'])->name('server.create-bungee');
    Route::post('server/force-scan', [\App\Http\Controllers\Admin\ServerController::class, 'postForceSyncStats'])->name('server.force-scan');
    Route::post('server', [\App\Http\Controllers\Admin\ServerController::class, 'store'])->name('server.store');
    Route::post('server-bungee', [\App\Http\Controllers\Admin\ServerController::class, 'storeBungee'])->name('server-bungee.store');
    Route::get('server/{server}', [\App\Http\Controllers\Admin\ServerController::class, 'show'])->name('server.show');
    Route::get('server/{server}/consolelogs', [\App\Http\Controllers\Admin\ServerController::class, 'getServerConsoleLogs'])->name('server.consolelogs.index');
    Route::get('server/{server}/edit', [\App\Http\Controllers\Admin\ServerController::class, 'edit'])->name('server.edit');
    Route::put('server/{server}', [\App\Http\Controllers\Admin\ServerController::class, 'update'])->name('server.update');
    Route::put('server/{server}/bungee', [\App\Http\Controllers\Admin\ServerController::class, 'updateBungee'])->name('server.update.bungee');
    Route::delete('server/{server}', [\App\Http\Controllers\Admin\ServerController::class, 'destroy'])->name('server.delete')->middleware('password.confirm');
    Route::post('server/{server}/send-command', [\App\Http\Controllers\Admin\ServerController::class, 'postSendCommandToServer'])->name('server.command');
    Route::get('server/{server}/stats', [\App\Http\Controllers\Admin\ServerController::class, 'showStatistics'])->name('server.show.stats');

    Route::get('intel/server/overview', [\App\Http\Controllers\Admin\ServerIntelController::class, 'overview'])->name('intel.server.index');
    Route::get('intel/server/overview/numbers', [\App\Http\Controllers\Admin\ServerIntelController::class, 'overviewNumbers'])->name('intel.server.index.numbers');
    Route::get('intel/server/performance', [\App\Http\Controllers\Admin\ServerIntelController::class, 'performance'])->name('intel.server.performance');
    Route::get('intel/server/performance/numbers', [\App\Http\Controllers\Admin\ServerIntelController::class, 'performanceNumbers'])->name('intel.server.performance.numbers');
    Route::get('intel/server/chatlog', [\App\Http\Controllers\Admin\ServerIntelController::class, 'chatlog'])->name('intel.server.chatlog');
    Route::get('intel/server/consolelog', [\App\Http\Controllers\Admin\ServerIntelController::class, 'consolelog'])->name('intel.server.consolelog');
    Route::get('intel/server/playerbase', [\App\Http\Controllers\Admin\ServerIntelController::class, 'playerbase'])->name('intel.server.playerbase');
    Route::get('intel/server/playerbase/countries', [\App\Http\Controllers\Admin\ServerIntelController::class, 'getPlayerPerCountry'])->name('intel.server.playerbase.countries');
    Route::get('intel/player/list', [\App\Http\Controllers\Admin\PlayerIntelController::class, 'playersList'])->name('intel.player.list');

    Route::delete('intel/player/{player:uuid}/delete', [\App\Http\Controllers\Admin\PlayerController::class, 'destroy'])->name('intel.player.delete');
    Route::delete('player/{player:uuid}/unlink', [\App\Http\Controllers\Admin\PlayerController::class, 'unlink'])->name('player.unlink');

    Route::get('rank', [\App\Http\Controllers\Admin\RankController::class, 'index'])->name('rank.index');
    Route::get('rank/create', [\App\Http\Controllers\Admin\RankController::class, 'create'])->name('rank.create');
    Route::post('rank', [\App\Http\Controllers\Admin\RankController::class, 'store'])->name('rank.store');
    Route::post('rank/reset', [\App\Http\Controllers\Admin\RankController::class, 'resetRanks'])->name('rank.reset');
    Route::get('rank/{rank}', [\App\Http\Controllers\Admin\RankController::class, 'show'])->name('rank.show');
    Route::get('rank/{rank}/edit', [\App\Http\Controllers\Admin\RankController::class, 'edit'])->name('rank.edit');
    Route::put('rank/{rank}', [\App\Http\Controllers\Admin\RankController::class, 'update'])->name('rank.update');
    Route::delete('rank/{rank}', [\App\Http\Controllers\Admin\RankController::class, 'destroy'])->name('rank.delete');

    Route::get('news', [\App\Http\Controllers\Admin\NewsController::class, 'index'])->name('news.index');
    Route::get('news/create', [\App\Http\Controllers\Admin\NewsController::class, 'create'])->name('news.create');
    Route::post('news', [\App\Http\Controllers\Admin\NewsController::class, 'store'])->name('news.store');
    Route::get('news/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'show'])->name('news.show');
    Route::get('news/{news}/edit', [\App\Http\Controllers\Admin\NewsController::class, 'edit'])->name('news.edit');
    Route::put('news/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'update'])->name('news.update');
    Route::delete('news/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'destroy'])->name('news.delete');

    Route::get('role', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('role.index');
    Route::get('role/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('role.create');
    Route::post('role', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('role.store');
    Route::get('role/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('role.edit');
    Route::put('role/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('role.update');
    Route::delete('role/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('role.delete');

    Route::get('setting/general', [\App\Http\Controllers\Admin\Settings\GeneralSettingController::class, 'show'])->name('setting.general.show');
    Route::post('setting/general', [\App\Http\Controllers\Admin\Settings\GeneralSettingController::class, 'update'])->name('setting.general.update');
    Route::get('setting/plugin', [\App\Http\Controllers\Admin\Settings\PluginSettingController::class, 'show'])->name('setting.plugin.show');
    Route::post('setting/plugin', [\App\Http\Controllers\Admin\Settings\PluginSettingController::class, 'update'])->name('setting.plugin.update');
    Route::post('setting/plugin/keygen', [\App\Http\Controllers\Admin\Settings\PluginSettingController::class, 'regeneratePluginApiKeys'])->name('setting.plugin.keygen');
    Route::get('setting/theme', [\App\Http\Controllers\Admin\Settings\ThemeSettingController::class, 'show'])->name('setting.theme.show');
    Route::post('setting/theme', [\App\Http\Controllers\Admin\Settings\ThemeSettingController::class, 'update'])->name('setting.theme.update');
    Route::get('setting/player', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'show'])->name('setting.player.show');
    Route::post('setting/player', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'update'])->name('setting.player.update');
    Route::post('setting/player/validate-rating-expression', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'validateRatingExpression'])->name('setting.player.validate-rating-expression');
    Route::post('setting/player/validate-score-expression', [\App\Http\Controllers\Admin\Settings\PlayerSettingController::class, 'validateScoreExpression'])->name('setting.player.validate-score-expression');
    Route::get('setting/navigation', [\App\Http\Controllers\Admin\Settings\NavigationSettingController::class, 'show'])->name('setting.navigation.show');
    Route::post('setting/navigation', [\App\Http\Controllers\Admin\Settings\NavigationSettingController::class, 'update'])->name('setting.navigation.update');
    Route::get('setting/seo', [\App\Http\Controllers\Admin\Settings\SeoSettingController::class, 'show'])->name('setting.seo.show');
    Route::post('setting/seo', [\App\Http\Controllers\Admin\Settings\SeoSettingController::class, 'update'])->name('setting.seo.update');
    Route::get('setting/danger', [\App\Http\Controllers\Admin\Settings\DangerSettingController::class, 'show'])->name('setting.danger.show');
    Route::delete('setting/danger/truncate-shouts', [\App\Http\Controllers\Admin\Settings\DangerSettingController::class, 'truncateShouts'])->name('setting.danger.truncate.shouts');
    Route::delete('setting/danger/truncate-consolelogs', [\App\Http\Controllers\Admin\Settings\DangerSettingController::class, 'truncateConsolelogs'])->name('setting.danger.truncate.consolelogs');
    Route::delete('setting/danger/truncate-chatlogs', [\App\Http\Controllers\Admin\Settings\DangerSettingController::class, 'truncateChatlogs'])->name('setting.danger.truncate.chatlogs');
    Route::delete('setting/danger/truncate-serverintel', [\App\Http\Controllers\Admin\Settings\DangerSettingController::class, 'truncatePlayerIntelData'])->name('setting.danger.truncate.playerintel');
    Route::delete('setting/danger/truncate-playerintel', [\App\Http\Controllers\Admin\Settings\DangerSettingController::class, 'truncateServerIntelData'])->name('setting.danger.truncate.serverintel');
    Route::delete('setting/danger/truncate-playerpunishments', [\App\Http\Controllers\Admin\Settings\DangerSettingController::class, 'truncatePlayerPunishments'])->name('setting.danger.truncate.playerpunishments');

    Route::get('poll', [\App\Http\Controllers\Admin\PollController::class, 'index'])->name('poll.index');
    Route::get('poll/create', [\App\Http\Controllers\Admin\PollController::class, 'create'])->name('poll.create');
    Route::post('poll', [\App\Http\Controllers\Admin\PollController::class, 'store'])->name('poll.store');
    Route::delete('poll/{poll}', [\App\Http\Controllers\Admin\PollController::class, 'destroy'])->name('poll.delete');
    Route::put('poll/{poll}/lock', [\App\Http\Controllers\Admin\PollController::class, 'lock'])->name('poll.lock');
    Route::put('poll/{poll}/unlock', [\App\Http\Controllers\Admin\PollController::class, 'unlock'])->name('poll.unlock');

    Route::get('custom-page', [\App\Http\Controllers\Admin\CustomPageController::class, 'index'])->name('custom-page.index');
    Route::get('custom-page/create', [\App\Http\Controllers\Admin\CustomPageController::class, 'create'])->name('custom-page.create');
    Route::post('custom-page', [\App\Http\Controllers\Admin\CustomPageController::class, 'store'])->name('custom-page.store');
    Route::get('custom-page/{customPage}/edit', [\App\Http\Controllers\Admin\CustomPageController::class, 'edit'])->name('custom-page.edit');
    Route::put('custom-page/{customPage}', [\App\Http\Controllers\Admin\CustomPageController::class, 'update'])->name('custom-page.update');
    Route::delete('custom-page/{customPage}', [\App\Http\Controllers\Admin\CustomPageController::class, 'destroy'])->name('custom-page.delete');

    Route::get('session', [\App\Http\Controllers\Admin\SessionController::class, 'index'])->name('session.index');

    Route::get('badge', [\App\Http\Controllers\Admin\BadgeController::class, 'index'])->name('badge.index');
    Route::get('badge/create', [\App\Http\Controllers\Admin\BadgeController::class, 'create'])->name('badge.create');
    Route::post('badge', [\App\Http\Controllers\Admin\BadgeController::class, 'store'])->name('badge.store');
    Route::get('badge/{badge}/edit', [\App\Http\Controllers\Admin\BadgeController::class, 'edit'])->name('badge.edit');
    Route::put('badge/{badge}', [\App\Http\Controllers\Admin\BadgeController::class, 'update'])->name('badge.update');
    Route::delete('badge/{badge}', [\App\Http\Controllers\Admin\BadgeController::class, 'destroy'])->name('badge.delete');

    Route::get('ask-db', [\App\Http\Controllers\Admin\AskDbController::class, 'index'])->name('ask-db.index');
    Route::post('ask-db', [\App\Http\Controllers\Admin\AskDbController::class, 'query'])->name('ask-db.query');
    Route::delete('ask-db', [\App\Http\Controllers\Admin\AskDbController::class, 'reset'])->name('ask-db.reset');

    Route::get('download', [\App\Http\Controllers\Admin\DownloadController::class, 'index'])->name('download.index');
    Route::get('download/create', [\App\Http\Controllers\Admin\DownloadController::class, 'create'])->name('download.create');
    Route::post('download', [\App\Http\Controllers\Admin\DownloadController::class, 'store'])->name('download.store');
    Route::get('download/{download}/edit', [\App\Http\Controllers\Admin\DownloadController::class, 'edit'])->name('download.edit');
    Route::put('download/{download}', [\App\Http\Controllers\Admin\DownloadController::class, 'update'])->name('download.update');
    Route::delete('download/{download}', [\App\Http\Controllers\Admin\DownloadController::class, 'destroy'])->name('download.delete');

    Route::get('custom-form', [\App\Http\Controllers\Admin\CustomFormController::class, 'index'])->name('custom-form.index');
    Route::get('custom-form/create', [\App\Http\Controllers\Admin\CustomFormController::class, 'create'])->name('custom-form.create');
    Route::post('custom-form', [\App\Http\Controllers\Admin\CustomFormController::class, 'store'])->name('custom-form.store');
    Route::get('custom-form/{customForm}', [\App\Http\Controllers\Admin\CustomFormController::class, 'show'])->name('custom-form.show');
    Route::get('custom-form/{customForm}/edit', [\App\Http\Controllers\Admin\CustomFormController::class, 'edit'])->name('custom-form.edit');
    Route::put('custom-form/{customForm}', [\App\Http\Controllers\Admin\CustomFormController::class, 'update'])->name('custom-form.update');
    Route::delete('custom-form/{customForm}', [\App\Http\Controllers\Admin\CustomFormController::class, 'destroy'])->name('custom-form.delete');

    Route::get('custom-form-submission', [\App\Http\Controllers\Admin\CustomFormSubmissionController::class, 'index'])->name('custom-form-submission.index');
    Route::get('custom-form-submission/archived', [\App\Http\Controllers\Admin\CustomFormSubmissionController::class, 'indexArchived'])->name('custom-form-submission.index-archived');
    Route::get('custom-form-submission/{submission}', [\App\Http\Controllers\Admin\CustomFormSubmissionController::class, 'show'])->name('custom-form-submission.show')->withTrashed();
    Route::delete('custom-form-submission/{submission}', [\App\Http\Controllers\Admin\CustomFormSubmissionController::class, 'destroy'])->name('custom-form-submission.delete')->withTrashed();
    Route::post('custom-form-submission/{submission}/archive', [\App\Http\Controllers\Admin\CustomFormSubmissionController::class, 'archive'])->name('custom-form-submission.archive');
    Route::post('custom-form-submission/{submission}/restore', [\App\Http\Controllers\Admin\CustomFormSubmissionController::class, 'restore'])->name('custom-form-submission.restore')->withTrashed();

    Route::get('application', [\App\Http\Controllers\Admin\RecruitmentController::class, 'index'])->name('recruitment.index');
    Route::get('application/create', [\App\Http\Controllers\Admin\RecruitmentController::class, 'create'])->name('recruitment.create');
    Route::post('application', [\App\Http\Controllers\Admin\RecruitmentController::class, 'store'])->name('recruitment.store');
    Route::get('application/{recruitment}', [\App\Http\Controllers\Admin\RecruitmentController::class, 'show'])->name('recruitment.show');
    Route::get('application/{recruitment}/edit', [\App\Http\Controllers\Admin\RecruitmentController::class, 'edit'])->name('recruitment.edit');
    Route::put('application/{recruitment}', [\App\Http\Controllers\Admin\RecruitmentController::class, 'update'])->name('recruitment.update');
    Route::delete('application/{recruitment}', [\App\Http\Controllers\Admin\RecruitmentController::class, 'destroy'])->name('recruitment.delete');

    Route::get('application-submission/open', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'indexOpen'])->name('recruitment-submission.index-open');
    Route::get('application-submission/closed', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'indexClosed'])->name('recruitment-submission.index-closed');
    Route::get('application-submission/{submission}', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'show'])->name('recruitment-submission.show');
    Route::delete('application-submission/{submission}', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'destroy'])->name('recruitment-submission.delete');
    Route::post('application-submission/{submission}/act', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'act'])->name('recruitment-submission.act');

    Route::get('application-submission/{submission}/message', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'indexMessages'])->name('recruitment-submission.message.index');
    Route::post('application-submission/{submission}/message', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'postMessage'])->name('recruitment-submission.message.store')->middleware('throttle:chat');
    Route::delete('application-submission/{submission}/message/{message}', [\App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'deleteMessage'])->name('recruitment-submission.message.delete');

    Route::get('failed-job', [\App\Http\Controllers\Admin\FailedJobController::class, 'index'])->name('failed-job.index');
    Route::post('failed-job/retry', [\App\Http\Controllers\Admin\FailedJobController::class, 'retry'])->name('failed-job.retry');
    Route::delete('failed-job/clear', [\App\Http\Controllers\Admin\FailedJobController::class, 'destroy'])->name('failed-job.clear');

    Route::get('command-queue', [\App\Http\Controllers\Admin\CommandQueueController::class, 'index'])->name('command-queue.index');
    Route::get('command-queue/create', [\App\Http\Controllers\Admin\CommandQueueController::class, 'create'])->name('command-queue.create');
    Route::post('command-queue', [\App\Http\Controllers\Admin\CommandQueueController::class, 'store'])->name('command-queue.store');
    Route::delete('command-queue', [\App\Http\Controllers\Admin\CommandQueueController::class, 'destroy'])->name('command-queue.delete');
    Route::post('command-queue/retry', [\App\Http\Controllers\Admin\CommandQueueController::class, 'retry'])->name('command-queue.retry');
});
