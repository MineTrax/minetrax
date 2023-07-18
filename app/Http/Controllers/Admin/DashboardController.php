<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Player;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $responseData = [];
        if ($request->user()->can('view admin_dashboard')) {
            // KPI for total users
            $kpiTotalUsers = User::count();
            $kpiUserCreatedForInterval = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $totalUsersMinusInterval = $kpiTotalUsers - $kpiUserCreatedForInterval ?: 1;
            $kpiTotalUserPercent = $kpiTotalUsers !== 0 ? ($kpiUserCreatedForInterval / $totalUsersMinusInterval) * 100 : 0;
            $kpiUserLastSeenForInterval = User::where('last_login_at', '>=', Carbon::now()->subDays(7))->count();

            // KPI for total players
            $kpiTotalPlayers = Player::count();
            $kpiPlayerCreatedForInterval = Player::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $totalPlayersMinusInterval = $kpiTotalPlayers - $kpiPlayerCreatedForInterval ?: 1;
            $kpiTotalPlayersPercent = $kpiTotalPlayers !== 0 ? ($kpiPlayerCreatedForInterval / $totalPlayersMinusInterval) * 100 : 0;
            $kpiPlayerLastSeenForInterval = Player::where('last_seen_at', '>=', Carbon::now()->subDays(7))->count();

            // KPI for total posts
            $kpiTotalPosts = Post::count();
            $kpiPostCreatedForInterval = Post::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $totalPostCreatedMinusInterval = $kpiTotalPosts - $kpiPostCreatedForInterval ?: 1;
            $kpiTotalPostsPercent = $kpiTotalPosts !== 0 ? ($kpiPostCreatedForInterval / $totalPostCreatedMinusInterval) * 100 : 0;
            $kpiTotalComments = Comment::count();

            // KPI for total failed jobs
            $kpiTotalFailedJobs = DB::table('failed_jobs')->count();
            $kpiFailedJobsForInterval = DB::table('failed_jobs')->where('failed_at', '>=', Carbon::now()->subDays(7))->count();
            $totalFailedJobsMinusInterval = $kpiTotalFailedJobs - $kpiFailedJobsForInterval ?: 1;
            $kpiTotalFailedJobPercent = $kpiTotalFailedJobs !== 0 ? ($kpiFailedJobsForInterval / $totalFailedJobsMinusInterval) * 100 : 0;

            $responseData = [
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
            ];
        }

        return Inertia::render('Admin/Dashboard', $responseData);
    }
}
