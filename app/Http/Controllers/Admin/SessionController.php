<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\User;
use App\Services\GeolocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Jenssegers\Agent\Agent;

class SessionController extends Controller
{
    public function index(GeolocationService $geolocationService)
    {
        $this->authorize('viewAny', Session::class);

        $timestamp5MinutesBefore = now()->subtract(5, 'minute')->timestamp;
        $sessions = Session::with('user:id,name,username,profile_photo_path,verified_at,settings')
            ->where('last_activity', '>=' , $timestamp5MinutesBefore)
            ->select(['id', 'user_id', 'ip_address', 'user_agent', 'last_activity'])
            ->orderByDesc('last_activity')
            ->paginate(10);

        $sessions->getCollection()->transform(function ($session) use ($geolocationService) {
            $agent = $this->createAgent($session);
            $country = $geolocationService->getCountryFromIP($session->ip_address);
            return (object) [
                'id' => $session->id,
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'device' => $agent->device(),
                    'platform' => $agent->platform(),
                    'platform_version' => $agent->version($agent->platform()),
                    'browser' => $agent->browser(),
                    'browser_version' => $agent->version($agent->browser())
                ],
                'ip_address' => $session->ip_address,
                'country' => $country,
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'user' => $session->user,
            ];
        });

        return Inertia::render('Admin/Session/IndexSession', [
            'sessions' => $sessions
        ]);
    }

    private function createAgent($session)
    {
        return tap(new Agent, function ($agent) use ($session) {
            $agent->setUserAgent($session->user_agent);
        });
    }
}
