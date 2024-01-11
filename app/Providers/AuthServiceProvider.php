<?php

namespace App\Providers;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\CustomForm;
use App\Models\CustomFormSubmission;
use App\Models\CustomPage;
use App\Models\Download;
use App\Models\News;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Rank;
use App\Models\Role;
use App\Models\Server;
use App\Models\Session;
use App\Models\Shout;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => \App\Policies\UserPolicy::class,
        Post::class => \App\Policies\PostPolicy::class,
        Shout::class => \App\Policies\ShoutPolicy::class,
        Comment::class => \App\Policies\CommentPolicy::class,
        Server::class => \App\Policies\ServerPolicy::class,
        Role::class => \App\Policies\RolePolicy::class,
        Rank::class => \App\Policies\RankPolicy::class,
        News::class => \App\Policies\NewsPolicy::class,
        Poll::class => \App\Policies\PollPolicy::class,
        CustomPage::class => \App\Policies\CustomPagePolicy::class,
        Session::class => \App\Policies\SessionPolicy::class,
        Badge::class => \App\Policies\BadgePolicy::class,
        Download::class => \App\Policies\DownloadPolicy::class,
        CustomForm::class => \App\Policies\CustomFormPolicy::class,
        CustomFormSubmission::class => \App\Policies\CustomFormSubmissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Super Admin can do anything even if that permission is missing for him.
        Gate::before(function ($user, $ability) {
            return $user->hasRole(Role::SUPER_ADMIN_ROLE_NAME) ? true : null;
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->can('view pulse_admin_dashboard');
        });
    }
}
