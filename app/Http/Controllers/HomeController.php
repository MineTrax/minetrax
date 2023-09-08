<?php

namespace App\Http\Controllers;

use App\Enums\ServerType;
use App\Models\News;
use App\Models\Player;
use App\Models\Poll;
use App\Models\Server;
use App\Models\Session;
use App\Models\User;
use App\Settings\GeneralSettings;
use App\Settings\ThemeSettings;
use Cache;
use Http;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Storage;

class HomeController extends Controller
{
    public function home(Request $request, GeneralSettings $generalSettings, ThemeSettings $themeSettings)
    {
        // Latest news-list
        $newslist = News::orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->whereNotNull('published_at')
            ->limit(5)
            ->select(['id', 'title', 'published_at', 'type', 'slug', 'body'])
            ->get()
            ->append('time_to_read')
            ->makeHidden('body');

        // Latest pinned news.
        $latestPinnedNews = News::with('creator:id,name,username,profile_photo_path,verified_at,settings')
            ->orderByDesc('published_at')
            ->whereNotNull('published_at')
            ->where('is_pinned', true)
            ->limit(10)
            ->get()?->append('time_to_read');

        // Make stripped body_html of the pinned news
        foreach ($latestPinnedNews as $news) {
            $converter = new GithubFlavoredMarkdownConverter();
            $strippedBody = \Str::words($news->body, 25);
            $news->body_md = $converter->convertToHtml($strippedBody)->getContent();
        }

        // Newest User
        $newestUser = User::latest()->select(['id', 'name', 'username', 'created_at', 'profile_photo_path', 'settings'])->first();

        // Latest Poll
        $latestPoll = Poll::with('options')->latest()->orderByDesc('id')->first()?->getParsedForVueComponent($request->user());

        // Online users. get from Session.
        $timestamp5MinutesBefore = now()->subtract(5, 'minute')->timestamp;
        $onlineUsers = collect();
        Session::with('user:id,name,username,profile_photo_path,verified_at,settings')
            ->where('last_activity', '>=', $timestamp5MinutesBefore)
            ->select(['id', 'user_id', 'last_activity'])
            ->orderByDesc('last_activity')
            ->limit(10)
            ->get()
            // Filter out duplicate user sessions but don't filter guest session with null user_id
            ->filter(function ($user) use ($onlineUsers) {
                if (! $user->user) {
                    $onlineUsers->push($user);
                } else {
                    if (! $onlineUsers->firstWhere('user_id', $user->user_id)) {
                        $onlineUsers->push($user);
                    }
                }
            });

        // Welcome box content is in markdown so need to converted before displaying.
        $welcomeBoxContentHtml = null;
        if ($generalSettings->enable_welcomebox && $generalSettings->welcomebox_content) {
            $converter = new GithubFlavoredMarkdownConverter();
            $welcomeBoxContentHtml = $converter->convertToHtml($generalSettings->welcomebox_content)->getContent();
        }

        // Chat Logs Section
        $chatServerList = Server::select(['id', 'name'])
            ->where('type', '!=', ServerType::Bungee)
            ->where('is_ingame_chat_enabled', true)
            ->get();
        $chatDefaultServerId = $chatServerList->first()?->id;

        // Top 10 Players
        $top10Players = Player::select(['id', 'username', 'uuid', 'position', 'rating', 'total_score', 'last_seen_at', 'country_id', 'rank_id'])
            ->with(['country:id,iso_code,flag,name', 'rank:id,shortname,name'])
            ->orderBy(\DB::raw('-`position`'), 'desc') // this sort with position but excludes the nulls
            ->orderByDesc('rating')
            ->orderByDesc('total_score')
            ->limit(10)->get();

        return Inertia::render('Dashboard', [
            'pinnedNewsList' => $latestPinnedNews,
            'newslist' => $newslist,
            'newestUser' => $newestUser,
            'latestPoll' => $latestPoll,
            'onlineUsers' => $onlineUsers,
            'welcomeBoxContentHtml' => $welcomeBoxContentHtml,
            'chatDefaultServerId' => $chatDefaultServerId,
            'chatServerList' => $chatServerList,
            'top10Players' => $top10Players,
            'themeSettings' => [
                'enable_home_hero_section' => $themeSettings->enable_home_hero_section,
                'home_hero_bg_image_path_dark' => $themeSettings->home_hero_bg_image_path_dark,
                'home_hero_bg_image_path_light' => $themeSettings->home_hero_bg_image_path_light,
                'home_hero_bg_size_css' => $themeSettings->home_hero_bg_size_css,
                'home_hero_bg_height_css' => $themeSettings->home_hero_bg_height_css,
                'home_hero_bg_position_css' => $themeSettings->home_hero_bg_position_css,
                'home_hero_bg_repeat_css' => $themeSettings->home_hero_bg_repeat_css,
                'home_hero_bg_attachment_css' => $themeSettings->home_hero_bg_attachment_css,
                'show_join_box_in_home_hero' => $themeSettings->show_join_box_in_home_hero,
                'home_hero_fg_image_path_dark' => $themeSettings->home_hero_fg_image_path_dark,
                'home_hero_fg_image_path_light' => $themeSettings->home_hero_fg_image_path_light,
                'show_fg_image_box_in_home_hero' => $themeSettings->show_fg_image_box_in_home_hero,
                'show_discord_box_in_home_hero' => $themeSettings->show_discord_box_in_home_hero,
                'home_hero_bg_particles' => $themeSettings->home_hero_bg_particles,
            ],
        ]);
    }

    public function didYouKnow(): \Illuminate\Http\JsonResponse
    {
        $didYouKnowArray = json_decode(Storage::disk('local')->get('misc/did-you-know.json'), true);

        return response()->json(\Arr::random($didYouKnowArray));
    }

    public function features()
    {
        // Forget Cache for Testing:
        // Cache::forget('feature:list');

        // Cache it for 1 hours
        $oneHour = 3600;
        $featureList = Cache::remember('feature:list', $oneHour, function () {
            $features = Http::withoutVerifying()->timeout(5)->get('https://q0rmzst113.execute-api.eu-central-1.amazonaws.com/v1/features')->json();
            if (! $features['body']) {
                throw new \Exception('Failed to get data');
            }

            return $features['body'];
        });

        return Inertia::render('Extra/FeatureList', [
            'features' => $featureList,
        ]);
    }

    public function version()
    {
        $myVersion = config('app.version');

        $latestVersion = Http::withoutVerifying()->timeout(5)->get(
            'https://e74gvrc5hpiyr7wojet23ursau0ehgxd.lambda-url.eu-central-1.on.aws',
            [
                'version' => $myVersion,
                'from' => 'web',
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ]
        )->json();
        $latestVersion = $latestVersion['web'];

        $response = [
            'my_version' => $myVersion,
            'latest_version' => $latestVersion,
            'is_uptodate' => $myVersion == $latestVersion,
        ];

        return response()->json($response);
    }

    public function visitVotingSite(Request $request, GeneralSettings $generalSettings)
    {
        $voteSitesArray = $generalSettings->voteforserverbox_content;
        if (! $voteSitesArray) {
            return abort(404);
        }

        $id = (int) $request->id;
        if (array_key_exists($id - 1, $voteSitesArray)) {
            $votingSiteData = $voteSitesArray[$id - 1];

            return redirect()->away($votingSiteData['url']);
        } else {
            return abort(404);
        }
    }
}
