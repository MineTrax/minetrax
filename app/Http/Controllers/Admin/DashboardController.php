<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Player;
use App\Models\Post;
use App\Models\User;
use App\Utils\Helpers\Helper;
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
            $kpiTotalUsers = Helper::fastCount('users');
            $kpiUserCreatedForInterval = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $totalUsersMinusInterval = $kpiTotalUsers - $kpiUserCreatedForInterval ?: 1;
            $kpiTotalUserPercent = $kpiTotalUsers !== 0 ? ($kpiUserCreatedForInterval / $totalUsersMinusInterval) * 100 : 0;
            $kpiUserLastSeenForInterval = User::where('last_login_at', '>=', Carbon::now()->subDays(7))->count();

            // KPI for total players
            $kpiTotalPlayers = Player::fastCount();
            $kpiPlayerCreatedForInterval = Player::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $totalPlayersMinusInterval = $kpiTotalPlayers - $kpiPlayerCreatedForInterval ?: 1;
            $kpiTotalPlayersPercent = $kpiTotalPlayers !== 0 ? ($kpiPlayerCreatedForInterval / $totalPlayersMinusInterval) * 100 : 0;
            $kpiPlayerLastSeenForInterval = Player::where('last_seen_at', '>=', Carbon::now()->subDays(7))->count();

            // KPI for total posts
            $kpiTotalPosts = Post::fastCount();
            $kpiPostCreatedForInterval = Post::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $totalPostCreatedMinusInterval = $kpiTotalPosts - $kpiPostCreatedForInterval ?: 1;
            $kpiTotalPostsPercent = $kpiTotalPosts !== 0 ? ($kpiPostCreatedForInterval / $totalPostCreatedMinusInterval) * 100 : 0;
            $kpiTotalComments = Comment::fastCount();

            // KPI for total failed jobs
            $kpiTotalFailedJobs = Helper::fastCount('failed_jobs');
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
