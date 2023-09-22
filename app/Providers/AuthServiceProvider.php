<?php

namespace App\Providers;

use App\Models\Role;
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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Post::class => \App\Policies\PostPolicy::class,
        \App\Models\Shout::class => \App\Policies\ShoutPolicy::class,
        \App\Models\Comment::class => \App\Policies\CommentPolicy::class,
        \App\Models\Server::class => \App\Policies\ServerPolicy::class,
        Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Rank::class => \App\Policies\RankPolicy::class,
        \App\Models\News::class => \App\Policies\NewsPolicy::class,
        \App\Models\Poll::class => \App\Policies\PollPolicy::class,
        \App\Models\CustomPage::class => \App\Policies\CustomPagePolicy::class,
        \App\Models\Session::class => \App\Policies\SessionPolicy::class,
        \App\Models\Badge::class => \App\Policies\BadgePolicy::class,
        \App\Models\Download::class => \App\Policies\DownloadPolicy::class,
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
    }
}
