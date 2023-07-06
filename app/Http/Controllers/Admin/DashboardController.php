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
            $kpiTotalUserPercent = $kpiTotalUsers !== 0 ? ($kpiUserCreatedForInterval / ($kpiTotalUsers - $kpiUserCreatedForInterval)) * 100 : 0;
            $kpiUserLastSeenForInterval = User::where('last_login_at', '>=', Carbon::now()->subDays(7))->count();

            // KPI for total players
            $kpiTotalPlayers = Player::count();
            $kpiPlayerCreatedForInterval = Player::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $kpiTotalPlayersPercent = $kpiTotalPlayers !== 0 ? ($kpiPlayerCreatedForInterval / ($kpiTotalPlayers - $kpiPlayerCreatedForInterval)) * 100 : 0;
            $kpiPlayerLastSeenForInterval = Player::where('last_seen_at', '>=', Carbon::now()->subDays(7))->count();

            // KPI for total posts
            $kpiTotalPosts = Post::count();
            $kpiPostCreatedForInterval = Post::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            $kpiTotalPostsPercent = $kpiTotalPosts !== 0 ? ($kpiPostCreatedForInterval / ($kpiTotalPosts - $kpiPostCreatedForInterval)) * 100 : 0;
            $kpiTotalComments = Comment::count();

            // KPI for total failed jobs
            $kpiTotalFailedJobs = DB::table('failed_jobs')->count();
            $kpiFailedJobsForInterval = DB::table('failed_jobs')->where('failed_at', '>=', Carbon::now()->subDays(7))->count();
            $kpiTotalFailedJobPercent = $kpiTotalFailedJobs !== 0 ? ($kpiFailedJobsForInterval / ($kpiTotalFailedJobs - $kpiFailedJobsForInterval)) * 100 : 0;

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

        //        $this->authorize('view');
        return Inertia::render('Admin/Dashboard', $responseData);
    }
}