<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\Notifications\DispatchOperationalNotifications;
use App\Listeners\RecordUserLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            RecordUserLogin::class,
        ],
    ];

    /**
     * @var array<int, class-string>
     */
    protected $subscribe = [
        DispatchOperationalNotifications::class,
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
