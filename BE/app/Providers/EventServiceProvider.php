<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Map các event với listener tương ứng.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
          SendEmailVerificationNotification::class,
        ],
        \App\Events\MessageSent::class => [
            \App\Listeners\MessageSent::class,
        ],
    ];

    /**
     * Đăng ký bất kỳ sự kiện nào của ứng dụng.
     */
    public function boot(): void
    {
        //
    }
}
