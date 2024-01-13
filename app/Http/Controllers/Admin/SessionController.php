<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\GeolocationService;
use Carbon\Carbon;
use Inertia\Inertia;
use Laravel\Jetstream\Agent;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SessionController extends Controller
{
    public function index(GeolocationService $geolocationService)
    {
        $this->authorize('viewAny', Session::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $timestamp5MinutesBefore = now()->subtract(5, 'minute')->timestamp;

        $sessions = QueryBuilder::for(Session::class)
            ->select(['id', 'user_id', 'ip_address', 'user_agent', 'last_activity'])
            ->where('last_activity', '>=', $timestamp5MinutesBefore)
            ->with('user:id,name,username,profile_photo_path,verified_at,settings')
            ->allowedFilters([
                'id',
                'user_id',
                'ip_address',
                'user_agent',
                'last_activity',
                AllowedFilter::custom('q', new FilterMultipleFields(['user_id', 'ip_address', 'user_agent', 'last_activity'])),
            ])
            ->allowedSorts(['id', 'user_id', 'ip_address', 'user_agent', 'last_activity'])
            ->defaultSort('-last_activity')
            ->paginate($perPage)
            ->withQueryString();

        $sessions->getCollection()->transform(function ($session) use ($geolocationService) {
            $agent = $this->createAgent($session);
            $country = $geolocationService->getCountryFromIP($session->ip_address);

            return (object) [
                'id' => $session->id,
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'platform_version' => $agent->platform() ? $agent->version($agent->platform()) : null,
                    'browser' => $agent->browser(),
                    'browser_version' => $agent->browser() ? $agent->version($agent->browser()) : null,
                ],
                'ip_address' => $session->ip_address,
                'country' => $country,
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'user' => $session->user,
            ];
        });

        return Inertia::render('Admin/Session/IndexSession', [
            'sessions' => $sessions,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    /**
     * Create a new agent instance from the given session.
     *
     * @param  mixed  $session
     * @return \Laravel\Jetstream\Agent
     */
    protected function createAgent($session)
    {
        return tap(new Agent(), fn ($agent) => $agent->setUserAgent($session->user_agent));
    }
}
