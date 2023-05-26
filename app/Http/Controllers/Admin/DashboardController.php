<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MinecraftPlayerSession;
use App\Models\Player;
use App\Models\Post;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DashboardController extends Controller
{
    public function index()
    {
        // KPI for total users
        $kpiTotalUsers = User::count();
        $kpiUserCreatedForInterval = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::callback('for_days', function ($query, $value) {
                    $query->where('created_at', '>=', Carbon::now()->subDays($value));
                })->default(7),
            ])
            ->count();
        $kpiTotalUserPercent = $kpiTotalUsers !== 0 ? ($kpiUserCreatedForInterval / ($kpiTotalUsers - $kpiUserCreatedForInterval)) * 100 : 0;
        $kpiUserLastSeenForInterval = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::callback('for_days', function ($query, $value) {
                    $query->where('last_login_at', '>=', Carbon::now()->subDays($value));
                })->default(7),
            ])
            ->count();

        // KPI for total players
        $kpiTotalPlayers = Player::count();
        $kpiPlayerCreatedForInterval = QueryBuilder::for(Player::class)
            ->allowedFilters([
                AllowedFilter::callback('for_days', function ($query, $value) {
                    $query->where('created_at', '>=', Carbon::now()->subDays($value));
                })->default(7),
            ])
            ->count();
        $kpiTotalPlayersPercent = $kpiTotalPlayers !== 0 ? ($kpiPlayerCreatedForInterval / ($kpiTotalPlayers - $kpiPlayerCreatedForInterval)) * 100 : 0;
        $kpiPlayerLastSeenForInterval = QueryBuilder::for(Player::class)
            ->allowedFilters([
                AllowedFilter::callback('for_days', function ($query, $value) {
                    $query->where('last_seen_at', '>=', Carbon::now()->subDays($value));
                })->default(7),
            ])
            ->count();

        // KPI for total posts
        $kpiTotalPosts = Post::count();
        $kpiPostCreatedForInterval = QueryBuilder::for(Post::class)
            ->allowedFilters([
                AllowedFilter::callback('for_days', function ($query, $value) {
                    $query->where('created_at', '>=', Carbon::now()->subDays($value));
                })->default(7),
            ])
            ->count();
        $kpiTotalPostsPercent = $kpiTotalPosts !== 0 ? ($kpiPostCreatedForInterval / ($kpiTotalPosts - $kpiPostCreatedForInterval)) * 100 : 0;
        $kpiTotalComments = Comment::count();

        // KPI for total failed jobs
        $kpiTotalFailedJobs = DB::table('failed_jobs')->count();
        $kpiFailedJobsForInterval = DB::table('failed_jobs')->where('failed_at', '>=', Carbon::now()->subDays(7))->count();
        $kpiTotalFailedJobPercent = $kpiTotalFailedJobs !== 0 ? ($kpiFailedJobsForInterval / ($kpiTotalFailedJobs - $kpiFailedJobsForInterval)) * 100 : 0;

        //        $this->authorize('view');
        return Inertia::render('Admin/Dashboard', [
            'kpiTotalUsers' => $kpiTotalUsers,
            'kpiUserCreatedForInterval' => $kpiUserCreatedForInterval,
            'kpiUserLastSeenForInterval' => $kpiUserLastSeenForInterval,
            'kpiTotalUserPercent' => $kpiTotalUserPercent,

            'kpiTotalPlayers' => $kpiTotalPlayers,
            'kpiPlayerCreatedForInterval' => $kpiPlayerCreatedForInterval,
            'kpiPlayerLastSeenForInterval' => $kpiPlayerLastSeenForInterval,
            'kpiTotalPlayersPercent' => $kpiTotalPlayersPercent,

            'kpiTotalFailedJobs' => $kpiTotalFailedJobs,
            'kpiFailedJobsForInterval' => $kpiFailedJobsForInterval,
            'kpiTotalFailedJobPercent' => $kpiTotalFailedJobPercent,

            'kpiTotalPosts' => $kpiTotalPosts,
            'kpiPostCreatedForInterval' => $kpiPostCreatedForInterval,
            'kpiTotalPostsPercent' => $kpiTotalPostsPercent,
            'kpiTotalComments' => $kpiTotalComments,
        ]);
    }
}
