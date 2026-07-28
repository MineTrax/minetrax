<?php

namespace App\Providers;

use App\Models\Badge;
use App\Models\CommandQueue;
use App\Models\Comment;
use App\Models\CustomForm;
use App\Models\CustomFormSubmission;
use App\Models\CustomPage;
use App\Models\Download;
use App\Models\FailedJob;
use App\Models\News;
use App\Models\PlayerPunishment;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Rank;
use App\Models\Recruitment;
use App\Models\RecruitmentSubmission;
use App\Models\Role;
use App\Models\Server;
use App\Models\Session;
use App\Models\Shout;
use App\Models\StoreBan;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\StoreVariable;
use App\Models\User;
use App\Policies\BadgePolicy;
use App\Policies\CommandQueuePolicy;
use App\Policies\CommentPolicy;
use App\Policies\CustomFormPolicy;
use App\Policies\CustomFormSubmissionPolicy;
use App\Policies\CustomPagePolicy;
use App\Policies\DownloadPolicy;
use App\Policies\FailedJobPolicy;
use App\Policies\NewsPolicy;
use App\Policies\PlayerPunishmentPolicy;
use App\Policies\PollPolicy;
use App\Policies\PostPolicy;
use App\Policies\RankPolicy;
use App\Policies\RecruitmentPolicy;
use App\Policies\RecruitmentSubmissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\ServerPolicy;
use App\Policies\SessionPolicy;
use App\Policies\ShoutPolicy;
use App\Policies\StoreBanPolicy;
use App\Policies\StoreCategoryPolicy;
use App\Policies\StoreCouponPolicy;
use App\Policies\StoreCurrencyPolicy;
use App\Policies\StoreOrderPolicy;
use App\Policies\StorePackagePolicy;
use App\Policies\StoreSalePolicy;
use App\Policies\StoreVariablePolicy;
use App\Policies\UserPolicy;
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
        User::class => UserPolicy::class,
        Post::class => PostPolicy::class,
        Shout::class => ShoutPolicy::class,
        Comment::class => CommentPolicy::class,
        Server::class => ServerPolicy::class,
        Role::class => RolePolicy::class,
        Rank::class => RankPolicy::class,
        News::class => NewsPolicy::class,
        Poll::class => PollPolicy::class,
        CustomPage::class => CustomPagePolicy::class,
        Session::class => SessionPolicy::class,
        Badge::class => BadgePolicy::class,
        Download::class => DownloadPolicy::class,
        CustomForm::class => CustomFormPolicy::class,
        CustomFormSubmission::class => CustomFormSubmissionPolicy::class,
        Recruitment::class => RecruitmentPolicy::class,
        RecruitmentSubmission::class => RecruitmentSubmissionPolicy::class,
        FailedJob::class => FailedJobPolicy::class,
        CommandQueue::class => CommandQueuePolicy::class,
        PlayerPunishment::class => PlayerPunishmentPolicy::class,
        StoreCategory::class => StoreCategoryPolicy::class,
        StorePackage::class => StorePackagePolicy::class,
        StoreVariable::class => StoreVariablePolicy::class,
        StoreCurrency::class => StoreCurrencyPolicy::class,
        StoreOrder::class => StoreOrderPolicy::class,
        StoreCoupon::class => StoreCouponPolicy::class,
        StoreSale::class => StoreSalePolicy::class,
        StoreBan::class => StoreBanPolicy::class,
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
