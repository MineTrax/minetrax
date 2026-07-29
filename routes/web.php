<?php

use App\Http\Controllers\AccountLinkController;
use App\Http\Controllers\Admin\AskDbController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\CommandQueueController;
use App\Http\Controllers\Admin\CustomFormSubmissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\GraphController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\RankController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServerIntelController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\Settings\DangerSettingController;
use App\Http\Controllers\Admin\Settings\GeneralSettingController;
use App\Http\Controllers\Admin\Settings\NavigationSettingController;
use App\Http\Controllers\Admin\Settings\PlayerSettingController;
use App\Http\Controllers\Admin\Settings\PluginSettingController;
use App\Http\Controllers\Admin\Settings\SeoSettingController;
use App\Http\Controllers\Admin\Settings\StoreSettingController;
use App\Http\Controllers\Admin\Settings\ThemeSettingController;
use App\Http\Controllers\Admin\Store\StoreCategoryController;
use App\Http\Controllers\Admin\Store\StoreCouponController;
use App\Http\Controllers\Admin\Store\StoreCurrencyController as AdminStoreCurrencyController;
use App\Http\Controllers\Admin\Store\StoreOrderController;
use App\Http\Controllers\Admin\Store\StorePackageController;
use App\Http\Controllers\Admin\Store\StorePaymentGatewayController;
use App\Http\Controllers\Admin\Store\StoreSaleController;
use App\Http\Controllers\Admin\Store\StoreVariableController;
use App\Http\Controllers\BanWardenController;
use App\Http\Controllers\CustomFormController;
use App\Http\Controllers\CustomPageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayerIntelController;
use App\Http\Controllers\PlayerPasswordResetController;
use App\Http\Controllers\PlayerSkinController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\RecruitmentSubmissionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServerChatlogController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ShoutController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Store\StoreCartController;
use App\Http\Controllers\Store\StoreCheckoutController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Store\StoreCurrencyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
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
    Route::get('/', [HomeController::class, 'home'])->name('home');
    // The community homepage keeps its own URL, so news, the shoutbox and the widgets stay
    // reachable when the store owns `/`.
    Route::get('dashboard', [HomeController::class, 'dashboard'])->name('home.dashboard');
    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::get('news/{news:slug}', [NewsController::class, 'show'])->name('news.show');
    Route::get('news/{news}/comment', [NewsController::class, 'indexComment'])->name('news.comment.index');
    Route::get('post', [PostController::class, 'index'])->name('post.index');
    Route::get('post/{post}', [PostController::class, 'show'])->name('post.show');
    Route::get('post/user/{user:username}', [PostController::class, 'indexForUser'])->name('post.user.index');
    Route::get('post/{post}/comment', [PostController::class, 'indexComment'])->name('post.comment.index');
    Route::get('stats', [PlayerController::class, 'index'])->name('player.index');
    Route::get('stats/player/{player}', [PlayerController::class, 'show'])->name('player.show');
    Route::get('did-you-know', [HomeController::class, 'didYouKnow'])->name('didyouknow.get');

    Route::get('server/{server}/ping', [ServerController::class, 'pingServer'])->name('server.ping.get');
    Route::get('server/{server}/query', [ServerController::class, 'queryServer'])->name('server.query.get');
    Route::get('server/{server}/webping', [ServerController::class, 'pingServerWithWebQueryProtocol'])->name('server.webquery.ping');
    Route::get('server/{server}/webquery', [ServerController::class, 'queryServerWithWebQueryProtocol'])->name('server.webquery.status');

    Route::get('@{user:username}', [UserController::class, 'showProfile'])->name('user.public.get');
    Route::get('/staff-members', [UserController::class, 'indexStaff'])->name('staff.index');

    Route::get('pages/{customPage:path}', [CustomPageController::class, 'show'])->name('custom-page.show.long');
    Route::get('p/{customPage:path}', [CustomPageController::class, 'show'])->name('custom-page.show');

    Route::get('search', [SearchController::class, 'search'])->name('search');

    Route::get('auth/{provider}', [SocialAuthController::class, 'redirect'])->name('social.login')->middleware('guest');
    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleCallback'])->name('social.login.callback')->middleware('guest');

    Route::get('/features', [HomeController::class, 'features'])->name('features.list');
    Route::get('/version-check', [HomeController::class, 'version'])->name('version.check');

    Route::get('player/avatar/{uuid}/{username?}/{textureid?}', [PlayerController::class, 'getAvatarImage'])->name('player.avatar.get');
    Route::get('player/skin/{uuid}/{username?}/{textureid?}', [PlayerController::class, 'getSkinImage'])->name('player.skin.get');
    Route::get('player/render/{uuid}/{username?}/{textureid?}', [PlayerController::class, 'getRenderImage'])->name('player.render.get');

    Route::get('vote/{id}', [HomeController::class, 'visitVotingSite'])->name('vote.visit');

    // Route::get('intel/player/{player:uuid}', [\App\Http\Controllers\PlayerIntelController::class, 'overview'])->name('player.intel.overview');
    Route::get('intel/player/{player:uuid}/sessions', [PlayerIntelController::class, 'indexSession'])->name('player.intel.session.index');
    Route::get('intel/player/{player:uuid}/sessions/{session}', [PlayerIntelController::class, 'showSession'])->name('player.intel.session.show');

    // Download file
    Route::get('download', [DownloadController::class, 'index'])->name('download.index');
    Route::get('download/{download:slug}', [DownloadController::class, 'show'])->name('download.show');
    Route::get('download/{download:slug}/download/{any?}', [DownloadController::class, 'download'])->where('any', '.*')->name('download.download');

    // Custom Form
    Route::get('forms', [CustomFormController::class, 'index'])->name('custom-form.index');
    Route::get('forms/{customForm:slug}', [CustomFormController::class, 'show'])->name('custom-form.show');
    Route::post('forms/{customForm:slug}', [CustomFormController::class, 'submit'])->name('custom-form.submit');

    // Recruitment (Public)
    Route::get('applications', [RecruitmentController::class, 'index'])->name('recruitment.index');
    Route::get('applications/{recruitment:slug}', [RecruitmentController::class, 'show'])->name('recruitment.show');

    // Locale
    Route::get('locale/list', [LocaleController::class, 'getAvailableLocales'])->name('locale.list');
    Route::post('locale/set', [LocaleController::class, 'setLocale'])->name('locale.set');

    // BanWarden
    Route::get('player/punishments', [BanWardenController::class, 'index'])->name('player.punishment.index');
    Route::get('player/punishments/{playerPunishment:id}', [BanWardenController::class, 'show'])->name('player.punishment.show');
    Route::get('player/punishments/{playerPunishment:id}/history', [BanWardenController::class, 'indexLastPunishments'])->name('player.punishment.show.history');
    Route::get('player/punishments/{playerPunishment:id}/sessions', [BanWardenController::class, 'indexLastSessions'])->name('player.punishment.show.session');
    Route::get('player/punishments/{playerPunishment:id}/evidence/{evidence}', [BanWardenController::class, 'showMediaEvidence'])->name('player.punishment.evidence.show');

    // Store (public)
    Route::get('store', [StoreController::class, 'index'])->name('store.index');
    Route::get('store/category/{storeCategory:slug}', [StoreController::class, 'showCategory'])->name('store.category');
    Route::get('store/package/{storePackage:slug}', [StoreController::class, 'showPackage'])->name('store.package');
    Route::post('store/currency', [StoreCurrencyController::class, 'switch'])->name('store.currency.switch');

    Route::get('store/cart', [StoreCartController::class, 'show'])->name('store.cart.show');
    Route::post('store/cart', [StoreCartController::class, 'store'])->name('store.cart.store');
    Route::patch('store/cart/{cartItem}', [StoreCartController::class, 'update'])->name('store.cart.update');
    Route::delete('store/cart/{cartItem}', [StoreCartController::class, 'destroy'])->name('store.cart.delete');
    Route::post('store/cart/code', [StoreCartController::class, 'applyCode'])->name('store.cart.code')->middleware('throttle:store-code');

    Route::get('store/checkout', [StoreCheckoutController::class, 'create'])->name('store.checkout.create');
    Route::post('store/checkout', [StoreCheckoutController::class, 'store'])->name('store.checkout.store')->middleware('throttle:store-checkout');
    Route::get('store/order/{order:uuid}', [StoreCheckoutController::class, 'result'])->name('store.order.result');
    Route::get('store/order/{order:uuid}/status', [StoreCheckoutController::class, 'status'])->name('store.order.status');
    Route::post('store/order/{order:uuid}/cancel', [StoreCheckoutController::class, 'cancel'])->name('store.order.cancel');
});

/**
 * USER SECTION/LOGGED IN
 */
Route::middleware(['auth:sanctum', 'forbid-banned-user', 'redirect-uncompleted-user', 'verified-if-enabled'])->group(function () {
    // A buyer's own purchase history. Guest orders are reached through the result page instead,
    // where the order uuid itself is the credential.
    Route::get('store/my-orders', [App\Http\Controllers\Store\StoreOrderController::class, 'index'])->name('store.my-order.index');
    Route::get('store/my-orders/{order:uuid}', [App\Http\Controllers\Store\StoreOrderController::class, 'show'])->name('store.my-order.show');

    // Shouts
    Route::get('shout', [ShoutController::class, 'index'])->name('shout.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('shout', [ShoutController::class, 'store'])->name('shout.store')->middleware('forbid-muted-user');
    Route::delete('shout/{shout}', [ShoutController::class, 'destroy'])->name('shout.delete');

    // Posts
    Route::post('post', [PostController::class, 'store'])->name('post.store')->middleware('forbid-muted-user');
    Route::delete('post/{post}', [PostController::class, 'destroy'])->name('post.delete');
    // Post Comments
    Route::post('post/{post}/comment', [PostController::class, 'postComment'])->name('post.comment.store')->middleware('forbid-muted-user');
    Route::delete('post/{post}/comment/{comment}', [PostController::class, 'deleteComment'])->name('post.comment.delete');
    // Reactions
    Route::post('reaction/post/{post}/like', [PostController::class, 'likePost'])->name('reaction.post.like');
    Route::post('reaction/post/{post}/unlike', [PostController::class, 'unlikePost'])->name('reaction.post.unlike');

    // News Comments
    Route::post('news/{news}/comment', [NewsController::class, 'postComment'])->name('news.comment.store')->middleware('forbid-muted-user');
    Route::delete('news/{news}/comment/{comment}', [NewsController::class, 'deleteComment'])->name('news.comment.delete');

    // Polls
    Route::get('poll', [PollController::class, 'index'])->name('poll.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('poll/{poll}/option/{option}/vote', [PollController::class, 'vote'])->name('poll.vote');

    // User
    Route::post('auth/user/post-registration-setup', [UserProfileController::class, 'postRegistrationSetup'])->name('auth.post-reg-setup')->withoutMiddleware(['redirect-uncompleted-user', 'verified-if-enabled']);
    Route::delete('auth/user/remove-cover', [UserProfileController::class, 'deleteCoverImage'])->name('current-user-cover.destroy');
    Route::put('auth/user/notification-preferences', [UserProfileController::class, 'putUpdateNotificationPreference'])->name('auth.put-notification-preferences')->withoutMiddleware('verified-if-enabled');
    Route::get('auth/user/social-accounts', [SocialAuthController::class, 'indexLinked'])->name('auth.social-account.index')->withoutMiddleware('verified-if-enabled');
    Route::delete('auth/user/social-accounts/{socialAccount}', [SocialAuthController::class, 'unlinkAccount'])->name('auth.social-account.delete')->withoutMiddleware('verified-if-enabled');

    // Notifications
    Route::get('user/notification', [NotificationController::class, 'index'])->name('notification.index')->withoutMiddleware(['redirect-uncompleted-user', 'verified-if-enabled']);
    Route::post('user/notification/read', [NotificationController::class, 'postMarkAsRead'])->name('notification.mark-as-read')->withoutMiddleware('verified-if-enabled');

    // Account Linker
    Route::delete('account-link/remove/{player:uuid}', [AccountLinkController::class, 'unlink'])->name('account-link.delete');
    Route::get('user/linked-players', [AccountLinkController::class, 'listMyPlayers'])->name('linked-player.list')->withoutMiddleware(['verified-if-enabled']);

    // Skin Changer
    Route::get('user/change-player-skin', [PlayerSkinController::class, 'showChangeSkin'])->name('change-player-skin.show');
    Route::post('user/change-player-skin', [PlayerSkinController::class, 'postChangeSkin'])->name('change-player-skin.update');

    // Player Password Reset
    Route::get('user/reset-player-password', [PlayerPasswordResetController::class, 'show'])->name('reset-player-password.show');
    Route::post('user/reset-player-password', [PlayerPasswordResetController::class, 'update'])->name('reset-player-password.update');

    // Server Chatlog
    Route::get('chatlog/{server}', [ServerChatlogController::class, 'index'])->name('chatlog.index')->withoutMiddleware(['auth:sanctum', 'verified-if-enabled']);
    Route::post('chatlog/{server}', [ServerChatlogController::class, 'sendToServer'])->name('chatlog.send')->middleware(['forbid-muted-user', 'throttle:chat']);

    // Recruitment (Authenticated)
    Route::post('applications/{recruitment:slug}', [RecruitmentController::class, 'submit'])->name('recruitment.submit');
    Route::get('applications/submissions/my', [RecruitmentSubmissionController::class, 'index'])->name('recruitment-submission.index');
    Route::get('applications/{recruitment:slug}/submissions/{submission}', [RecruitmentSubmissionController::class, 'show'])->name('recruitment-submission.show');
    Route::post('applications/{recruitment:slug}/submissions/{submission}/withdraw', [RecruitmentSubmissionController::class, 'withdraw'])->name('recruitment-submission.withdraw');
    Route::get('applications/{recruitment:slug}/submissions/{submission}/messages', [RecruitmentSubmissionController::class, 'indexMessages'])->name('recruitment-submission.message.index');
    Route::post('applications/{recruitment:slug}/submissions/{submission}/messages', [RecruitmentSubmissionController::class, 'postMessage'])->name('recruitment-submission.message.store')->middleware('throttle:chat');

    // BanWarden (Authenticated)
    Route::delete('player/punishments/{playerPunishment:id}', [BanWardenController::class, 'pardon'])->name('player.punishment.pardon');
    Route::post('player/punishments/{playerPunishment:id}/evidence', [BanWardenController::class, 'createEvidence'])->name('player.punishment.evidence.store');
    Route::delete('player/punishments/{playerPunishment:id}/evidence', [BanWardenController::class, 'deleteEvidence'])->name('player.punishment.evidence.delete');
});

/**
 * ADMIN SECTION
 */
Route::middleware(['auth:sanctum', 'verified-if-enabled', 'forbid-banned-user', 'staff-member', 'redirect-uncompleted-user'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Graph controller
    Route::get('/graph/online-players', [GraphController::class, 'getOnlinePlayersOverTime'])->name('graph.online-players');
    Route::get('/graph/players-per-server', [GraphController::class, 'getPlayersPerServer'])->name('graph.players-per-server');
    Route::get('/graph/players-per-country', [GraphController::class, 'getPlayerPerCountry'])->name('graph.players-per-country');
    Route::get('/graph/network-trends-vs-month', [GraphController::class, 'getNetworkTrendsMonthVsMonth'])->name('graph.network-trends-vs-month');
    Route::get('/graph/server-performance', [GraphController::class, 'getServerPerformanceOverTime'])->name('graph.server-performance');
    Route::get('/graph/server-online-activity', [GraphController::class, 'getServerOnlineActivityOverTime'])->name('graph.server-online-activity');
    Route::get('/graph/player-minecraft-versions', [GraphController::class, 'getPlayerMinecraftVersions'])->name('graph.player-minecraft-versions');
    Route::get('/graph/player-join-addresses', [GraphController::class, 'getPlayerJoinAddresses'])->name('graph.player-join-addresses');
    Route::get('/graph/player-join-addresses-timeseries', [GraphController::class, 'getPlayerJoinAddressesOverTime'])->name('graph.player-join-addresses.timeseries');
    Route::get('/graph/player-minecraft-versions-timeseries', [GraphController::class, 'getPlayerMinecraftVersionsOverTime'])->name('graph.player-minecraft-versions.timeseries');

    Route::get('user', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('user.index');
    //  Route::get('user/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('user.show');
    Route::get('user/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('user.edit');
    Route::put('user/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('user.update');
    Route::post('user/{user}/ban', [App\Http\Controllers\Admin\UserController::class, 'ban'])->name('user.ban');
    Route::post('user/{user}/unban', [App\Http\Controllers\Admin\UserController::class, 'unban'])->name('user.unban');
    Route::post('user/{user}/mute', [App\Http\Controllers\Admin\UserController::class, 'mute'])->name('user.mute');
    Route::post('user/{user}/unmute', [App\Http\Controllers\Admin\UserController::class, 'unmute'])->name('user.unmute');
    Route::delete('user/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('user.delete');

    Route::get('impersonate/{user}/take', [ImpersonateController::class, 'take'])->name('impersonate.take')->withoutMiddleware(['auth:sanctum']);
    Route::get('impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave')->withoutMiddleware(['auth:sanctum', 'staff-member']);

    Route::get('server', [App\Http\Controllers\Admin\ServerController::class, 'index'])->name('server.index');
    Route::get('server/create', [App\Http\Controllers\Admin\ServerController::class, 'create'])->name('server.create');
    Route::get('server/create-bungee', [App\Http\Controllers\Admin\ServerController::class, 'createBungee'])->name('server.create-bungee');
    Route::post('server/force-scan', [App\Http\Controllers\Admin\ServerController::class, 'postForceSyncStats'])->name('server.force-scan');
    Route::post('server', [App\Http\Controllers\Admin\ServerController::class, 'store'])->name('server.store');
    Route::post('server-bungee', [App\Http\Controllers\Admin\ServerController::class, 'storeBungee'])->name('server-bungee.store');
    Route::get('server/{server}', [App\Http\Controllers\Admin\ServerController::class, 'show'])->name('server.show');
    Route::get('server/{server}/consolelogs', [App\Http\Controllers\Admin\ServerController::class, 'getServerConsoleLogs'])->name('server.consolelogs.index');
    Route::get('server/{server}/edit', [App\Http\Controllers\Admin\ServerController::class, 'edit'])->name('server.edit');
    Route::put('server/{server}', [App\Http\Controllers\Admin\ServerController::class, 'update'])->name('server.update');
    Route::put('server/{server}/bungee', [App\Http\Controllers\Admin\ServerController::class, 'updateBungee'])->name('server.update.bungee');
    Route::delete('server/{server}', [App\Http\Controllers\Admin\ServerController::class, 'destroy'])->name('server.delete')->middleware('password.confirm');
    Route::post('server/{server}/send-command', [App\Http\Controllers\Admin\ServerController::class, 'postSendCommandToServer'])->name('server.command');
    Route::get('server/{server}/stats', [App\Http\Controllers\Admin\ServerController::class, 'showStatistics'])->name('server.show.stats');

    Route::get('intel/server/overview', [ServerIntelController::class, 'overview'])->name('intel.server.index');
    Route::get('intel/server/overview/numbers', [ServerIntelController::class, 'overviewNumbers'])->name('intel.server.index.numbers');
    Route::get('intel/server/performance', [ServerIntelController::class, 'performance'])->name('intel.server.performance');
    Route::get('intel/server/performance/numbers', [ServerIntelController::class, 'performanceNumbers'])->name('intel.server.performance.numbers');
    Route::get('intel/server/chatlog', [ServerIntelController::class, 'chatlog'])->name('intel.server.chatlog');
    Route::get('intel/server/consolelog', [ServerIntelController::class, 'consolelog'])->name('intel.server.consolelog');
    Route::get('intel/server/playerbase', [ServerIntelController::class, 'playerbase'])->name('intel.server.playerbase');
    Route::get('intel/server/playerbase/countries', [ServerIntelController::class, 'getPlayerPerCountry'])->name('intel.server.playerbase.countries');
    Route::get('intel/player/list', [App\Http\Controllers\Admin\PlayerIntelController::class, 'playersList'])->name('intel.player.list');

    Route::delete('intel/player/{player:uuid}/delete', [App\Http\Controllers\Admin\PlayerController::class, 'destroy'])->name('intel.player.delete');
    Route::delete('player/{player:uuid}/unlink', [App\Http\Controllers\Admin\PlayerController::class, 'unlink'])->name('player.unlink');

    Route::get('rank', [RankController::class, 'index'])->name('rank.index');
    Route::get('rank/create', [RankController::class, 'create'])->name('rank.create');
    Route::post('rank', [RankController::class, 'store'])->name('rank.store');
    Route::post('rank/reset', [RankController::class, 'resetRanks'])->name('rank.reset');
    Route::get('rank/{rank}', [RankController::class, 'show'])->name('rank.show');
    Route::get('rank/{rank}/edit', [RankController::class, 'edit'])->name('rank.edit');
    Route::put('rank/{rank}', [RankController::class, 'update'])->name('rank.update');
    Route::delete('rank/{rank}', [RankController::class, 'destroy'])->name('rank.delete');

    Route::get('news', [App\Http\Controllers\Admin\NewsController::class, 'index'])->name('news.index');
    Route::get('news/create', [App\Http\Controllers\Admin\NewsController::class, 'create'])->name('news.create');
    Route::post('news', [App\Http\Controllers\Admin\NewsController::class, 'store'])->name('news.store');
    Route::get('news/{news}/edit', [App\Http\Controllers\Admin\NewsController::class, 'edit'])->name('news.edit');
    Route::put('news/{news}', [App\Http\Controllers\Admin\NewsController::class, 'update'])->name('news.update');
    Route::delete('news/{news}', [App\Http\Controllers\Admin\NewsController::class, 'destroy'])->name('news.delete');

    Route::get('role', [RoleController::class, 'index'])->name('role.index');
    Route::get('role/create', [RoleController::class, 'create'])->name('role.create');
    Route::post('role', [RoleController::class, 'store'])->name('role.store');
    Route::get('role/{role}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::put('role/{role}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('role/{role}', [RoleController::class, 'destroy'])->name('role.delete');

    Route::get('setting/general', [GeneralSettingController::class, 'show'])->name('setting.general.show');
    Route::post('setting/general', [GeneralSettingController::class, 'update'])->name('setting.general.update');
    Route::get('setting/plugin', [PluginSettingController::class, 'show'])->name('setting.plugin.show');
    Route::post('setting/plugin', [PluginSettingController::class, 'update'])->name('setting.plugin.update');
    Route::post('setting/plugin/keygen', [PluginSettingController::class, 'regeneratePluginApiKeys'])->name('setting.plugin.keygen');
    Route::get('setting/theme', [ThemeSettingController::class, 'show'])->name('setting.theme.show');
    Route::post('setting/theme', [ThemeSettingController::class, 'update'])->name('setting.theme.update');
    Route::get('setting/player', [PlayerSettingController::class, 'show'])->name('setting.player.show');
    Route::post('setting/player', [PlayerSettingController::class, 'update'])->name('setting.player.update');
    Route::post('setting/player/validate-rating-expression', [PlayerSettingController::class, 'validateRatingExpression'])->name('setting.player.validate-rating-expression');
    Route::post('setting/player/validate-score-expression', [PlayerSettingController::class, 'validateScoreExpression'])->name('setting.player.validate-score-expression');
    Route::get('setting/navigation', [NavigationSettingController::class, 'show'])->name('setting.navigation.show');
    Route::post('setting/navigation', [NavigationSettingController::class, 'update'])->name('setting.navigation.update');
    Route::get('setting/seo', [SeoSettingController::class, 'show'])->name('setting.seo.show');
    Route::post('setting/seo', [SeoSettingController::class, 'update'])->name('setting.seo.update');
    Route::get('setting/store', [StoreSettingController::class, 'show'])->name('setting.store.show');
    Route::post('setting/store', [StoreSettingController::class, 'update'])->name('setting.store.update');
    Route::get('setting/danger', [DangerSettingController::class, 'show'])->name('setting.danger.show');
    Route::delete('setting/danger/truncate-shouts', [DangerSettingController::class, 'truncateShouts'])->name('setting.danger.truncate.shouts');
    Route::delete('setting/danger/truncate-consolelogs', [DangerSettingController::class, 'truncateConsolelogs'])->name('setting.danger.truncate.consolelogs');
    Route::delete('setting/danger/truncate-chatlogs', [DangerSettingController::class, 'truncateChatlogs'])->name('setting.danger.truncate.chatlogs');
    Route::delete('setting/danger/truncate-serverintel', [DangerSettingController::class, 'truncatePlayerIntelData'])->name('setting.danger.truncate.playerintel');
    Route::delete('setting/danger/truncate-playerintel', [DangerSettingController::class, 'truncateServerIntelData'])->name('setting.danger.truncate.serverintel');
    Route::delete('setting/danger/truncate-playerpunishments', [DangerSettingController::class, 'truncatePlayerPunishments'])->name('setting.danger.truncate.playerpunishments');
    Route::delete('setting/danger/reset-playerintelstats', [DangerSettingController::class, 'resetPlayerIntelStats'])->name('setting.danger.reset.playerintelstats');

    Route::get('poll', [App\Http\Controllers\Admin\PollController::class, 'index'])->name('poll.index');
    Route::get('poll/create', [App\Http\Controllers\Admin\PollController::class, 'create'])->name('poll.create');
    Route::post('poll', [App\Http\Controllers\Admin\PollController::class, 'store'])->name('poll.store');
    Route::delete('poll/{poll}', [App\Http\Controllers\Admin\PollController::class, 'destroy'])->name('poll.delete');
    Route::put('poll/{poll}/lock', [App\Http\Controllers\Admin\PollController::class, 'lock'])->name('poll.lock');
    Route::put('poll/{poll}/unlock', [App\Http\Controllers\Admin\PollController::class, 'unlock'])->name('poll.unlock');

    Route::get('custom-page', [App\Http\Controllers\Admin\CustomPageController::class, 'index'])->name('custom-page.index');
    Route::get('custom-page/create', [App\Http\Controllers\Admin\CustomPageController::class, 'create'])->name('custom-page.create');
    Route::post('custom-page', [App\Http\Controllers\Admin\CustomPageController::class, 'store'])->name('custom-page.store');
    Route::get('custom-page/{customPage}/edit', [App\Http\Controllers\Admin\CustomPageController::class, 'edit'])->name('custom-page.edit');
    Route::put('custom-page/{customPage}', [App\Http\Controllers\Admin\CustomPageController::class, 'update'])->name('custom-page.update');
    Route::delete('custom-page/{customPage}', [App\Http\Controllers\Admin\CustomPageController::class, 'destroy'])->name('custom-page.delete');

    Route::get('session', [SessionController::class, 'index'])->name('session.index');

    Route::get('badge', [BadgeController::class, 'index'])->name('badge.index');
    Route::get('badge/create', [BadgeController::class, 'create'])->name('badge.create');
    Route::post('badge', [BadgeController::class, 'store'])->name('badge.store');
    Route::get('badge/{badge}/edit', [BadgeController::class, 'edit'])->name('badge.edit');
    Route::put('badge/{badge}', [BadgeController::class, 'update'])->name('badge.update');
    Route::delete('badge/{badge}', [BadgeController::class, 'destroy'])->name('badge.delete');

    Route::get('ask-db', [AskDbController::class, 'index'])->name('ask-db.index');
    Route::post('ask-db', [AskDbController::class, 'query'])->name('ask-db.query');
    Route::delete('ask-db', [AskDbController::class, 'reset'])->name('ask-db.reset');

    Route::get('download', [App\Http\Controllers\Admin\DownloadController::class, 'index'])->name('download.index');
    Route::get('download/create', [App\Http\Controllers\Admin\DownloadController::class, 'create'])->name('download.create');
    Route::post('download', [App\Http\Controllers\Admin\DownloadController::class, 'store'])->name('download.store');
    Route::get('download/{download}/edit', [App\Http\Controllers\Admin\DownloadController::class, 'edit'])->name('download.edit');
    Route::put('download/{download}', [App\Http\Controllers\Admin\DownloadController::class, 'update'])->name('download.update');
    Route::delete('download/{download}', [App\Http\Controllers\Admin\DownloadController::class, 'destroy'])->name('download.delete');

    // Store: catalog
    // Store admin lives under a single /admin/store prefix rather than a flat store-* namespace,
    // so the module reads as one section in URLs the way it does in the sidebar.
    Route::prefix('store')->name('store.')->group(function () {
        Route::get('category', [StoreCategoryController::class, 'index'])->name('category.index');
        Route::get('category/create', [StoreCategoryController::class, 'create'])->name('category.create');
        Route::post('category', [StoreCategoryController::class, 'store'])->name('category.store');
        Route::get('category/{storeCategory}/edit', [StoreCategoryController::class, 'edit'])->name('category.edit');
        Route::put('category/{storeCategory}', [StoreCategoryController::class, 'update'])->name('category.update');
        Route::delete('category/{storeCategory}', [StoreCategoryController::class, 'destroy'])->name('category.delete');

        Route::get('package', [StorePackageController::class, 'index'])->name('package.index');
        Route::get('package/create', [StorePackageController::class, 'create'])->name('package.create');
        Route::post('package', [StorePackageController::class, 'store'])->name('package.store');
        Route::get('package/{storePackage}/edit', [StorePackageController::class, 'edit'])->name('package.edit');
        Route::put('package/{storePackage}', [StorePackageController::class, 'update'])->name('package.update');
        Route::delete('package/{storePackage}', [StorePackageController::class, 'destroy'])->name('package.delete');

        Route::get('variable', [StoreVariableController::class, 'index'])->name('variable.index');
        Route::get('variable/create', [StoreVariableController::class, 'create'])->name('variable.create');
        Route::post('variable', [StoreVariableController::class, 'store'])->name('variable.store');
        Route::get('variable/{storeVariable}/edit', [StoreVariableController::class, 'edit'])->name('variable.edit');
        Route::put('variable/{storeVariable}', [StoreVariableController::class, 'update'])->name('variable.update');
        Route::delete('variable/{storeVariable}', [StoreVariableController::class, 'destroy'])->name('variable.delete');

        Route::get('coupon', [StoreCouponController::class, 'index'])->name('coupon.index');
        Route::get('coupon/create', [StoreCouponController::class, 'create'])->name('coupon.create');
        Route::post('coupon', [StoreCouponController::class, 'store'])->name('coupon.store');
        Route::get('coupon/{storeCoupon}/edit', [StoreCouponController::class, 'edit'])->name('coupon.edit');
        Route::put('coupon/{storeCoupon}', [StoreCouponController::class, 'update'])->name('coupon.update');
        Route::delete('coupon/{storeCoupon}', [StoreCouponController::class, 'destroy'])->name('coupon.delete');

        Route::get('sale', [StoreSaleController::class, 'index'])->name('sale.index');
        Route::get('sale/create', [StoreSaleController::class, 'create'])->name('sale.create');
        Route::post('sale', [StoreSaleController::class, 'store'])->name('sale.store');
        Route::get('sale/{storeSale}/edit', [StoreSaleController::class, 'edit'])->name('sale.edit');
        Route::put('sale/{storeSale}', [StoreSaleController::class, 'update'])->name('sale.update');
        Route::delete('sale/{storeSale}', [StoreSaleController::class, 'destroy'])->name('sale.delete');

        Route::get('order', [StoreOrderController::class, 'index'])->name('order.index');
        Route::get('order/{order:uuid}', [StoreOrderController::class, 'show'])->name('order.show');
        Route::post('order/{order:uuid}/mark-paid', [StoreOrderController::class, 'markPaid'])->name('order.mark-paid');
        Route::post('order/{order:uuid}/cancel', [StoreOrderController::class, 'cancel'])->name('order.cancel');
        Route::post('order/{order:uuid}/refund', [StoreOrderController::class, 'refund'])->name('order.refund');
        Route::post('order/{order:uuid}/resend', [StoreOrderController::class, 'resend'])->name('order.resend');

        Route::get('currency', [AdminStoreCurrencyController::class, 'index'])->name('currency.index');
        Route::get('currency/create', [AdminStoreCurrencyController::class, 'create'])->name('currency.create');
        Route::post('currency', [AdminStoreCurrencyController::class, 'store'])->name('currency.store');
        Route::get('currency/{storeCurrency}/edit', [AdminStoreCurrencyController::class, 'edit'])->name('currency.edit');
        Route::put('currency/{storeCurrency}', [AdminStoreCurrencyController::class, 'update'])->name('currency.update');
        Route::delete('currency/{storeCurrency}', [AdminStoreCurrencyController::class, 'destroy'])->name('currency.delete');
        Route::post('currency/{storeCurrency}/make-base', [AdminStoreCurrencyController::class, 'makeBase'])->name('currency.make-base');

        Route::get('payment-gateway', [StorePaymentGatewayController::class, 'index'])->name('payment-gateway.index');
        Route::post('payment-gateway', [StorePaymentGatewayController::class, 'update'])->name('payment-gateway.update');
    });

    Route::get('custom-form', [App\Http\Controllers\Admin\CustomFormController::class, 'index'])->name('custom-form.index');
    Route::get('custom-form/create', [App\Http\Controllers\Admin\CustomFormController::class, 'create'])->name('custom-form.create');
    Route::post('custom-form', [App\Http\Controllers\Admin\CustomFormController::class, 'store'])->name('custom-form.store');
    Route::get('custom-form/{customForm}', [App\Http\Controllers\Admin\CustomFormController::class, 'show'])->name('custom-form.show');
    Route::get('custom-form/{customForm}/edit', [App\Http\Controllers\Admin\CustomFormController::class, 'edit'])->name('custom-form.edit');
    Route::put('custom-form/{customForm}', [App\Http\Controllers\Admin\CustomFormController::class, 'update'])->name('custom-form.update');
    Route::delete('custom-form/{customForm}', [App\Http\Controllers\Admin\CustomFormController::class, 'destroy'])->name('custom-form.delete');

    Route::get('custom-form-submission', [CustomFormSubmissionController::class, 'index'])->name('custom-form-submission.index');
    Route::get('custom-form-submission/archived', [CustomFormSubmissionController::class, 'indexArchived'])->name('custom-form-submission.index-archived');
    Route::get('custom-form-submission/{submission}', [CustomFormSubmissionController::class, 'show'])->name('custom-form-submission.show')->withTrashed();
    Route::delete('custom-form-submission/{submission}', [CustomFormSubmissionController::class, 'destroy'])->name('custom-form-submission.delete')->withTrashed();
    Route::post('custom-form-submission/{submission}/archive', [CustomFormSubmissionController::class, 'archive'])->name('custom-form-submission.archive');
    Route::post('custom-form-submission/{submission}/restore', [CustomFormSubmissionController::class, 'restore'])->name('custom-form-submission.restore')->withTrashed();

    Route::get('application', [App\Http\Controllers\Admin\RecruitmentController::class, 'index'])->name('recruitment.index');
    Route::get('application/create', [App\Http\Controllers\Admin\RecruitmentController::class, 'create'])->name('recruitment.create');
    Route::post('application', [App\Http\Controllers\Admin\RecruitmentController::class, 'store'])->name('recruitment.store');
    Route::get('application/{recruitment}', [App\Http\Controllers\Admin\RecruitmentController::class, 'show'])->name('recruitment.show');
    Route::get('application/{recruitment}/edit', [App\Http\Controllers\Admin\RecruitmentController::class, 'edit'])->name('recruitment.edit');
    Route::put('application/{recruitment}', [App\Http\Controllers\Admin\RecruitmentController::class, 'update'])->name('recruitment.update');
    Route::delete('application/{recruitment}', [App\Http\Controllers\Admin\RecruitmentController::class, 'destroy'])->name('recruitment.delete');

    Route::get('application-submission/open', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'indexOpen'])->name('recruitment-submission.index-open');
    Route::get('application-submission/closed', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'indexClosed'])->name('recruitment-submission.index-closed');
    Route::get('application-submission/{submission}', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'show'])->name('recruitment-submission.show');
    Route::delete('application-submission/{submission}', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'destroy'])->name('recruitment-submission.delete');
    Route::post('application-submission/{submission}/act', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'act'])->name('recruitment-submission.act');

    Route::get('application-submission/{submission}/message', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'indexMessages'])->name('recruitment-submission.message.index');
    Route::post('application-submission/{submission}/message', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'postMessage'])->name('recruitment-submission.message.store')->middleware('throttle:chat');
    Route::delete('application-submission/{submission}/message/{message}', [App\Http\Controllers\Admin\RecruitmentSubmissionController::class, 'deleteMessage'])->name('recruitment-submission.message.delete');

    Route::get('failed-job', [FailedJobController::class, 'index'])->name('failed-job.index');
    Route::post('failed-job/retry', [FailedJobController::class, 'retry'])->name('failed-job.retry');
    Route::delete('failed-job/clear', [FailedJobController::class, 'destroy'])->name('failed-job.clear');

    Route::get('command-queue', [CommandQueueController::class, 'index'])->name('command-queue.index');
    Route::get('command-queue/create', [CommandQueueController::class, 'create'])->name('command-queue.create');
    Route::post('command-queue', [CommandQueueController::class, 'store'])->name('command-queue.store');
    Route::delete('command-queue', [CommandQueueController::class, 'destroy'])->name('command-queue.delete');
    Route::post('command-queue/retry', [CommandQueueController::class, 'retry'])->name('command-queue.retry');
});
