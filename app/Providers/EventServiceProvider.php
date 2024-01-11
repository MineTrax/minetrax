<?php

namespace App\Providers;

use App\Events\CustomFormSubmissionCreated;
use App\Events\MinecraftPlayerEventCreated;
use App\Events\MinecraftPlayerSessionCreated;
use App\Listeners\NotifyStaffOnCustomFormSubmission;
use App\Listeners\UpdateStatsOnMinecraftPlayerEvent;
use App\Listeners\UpsertPlayerOnSessionStart;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SocialiteWasCalled::class => [
            DiscordExtendSocialite::class
        ],
        MinecraftPlayerSessionCreated::class => [
            UpsertPlayerOnSessionStart::class,
        ],
        MinecraftPlayerEventCreated::class => [
            UpdateStatsOnMinecraftPlayerEvent::class,
        ],
        CustomFormSubmissionCreated::class => [
            NotifyStaffOnCustomFormSubmission::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
