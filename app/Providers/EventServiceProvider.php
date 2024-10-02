<?php

namespace App\Providers;

use App\Events\MessageStatusChecked;
use App\Events\UserChangedMessageStatus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Events\UserRoleAdd;
use App\Listeners\CreateDnContract;
use App\Listeners\ForwardMsg;
use App\Listeners\OnChangeMsgStatusByUser;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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
        UserRoleAdd::class => [
            ForwardMsg::class,
        ],
        UserChangedMessageStatus::class => [
            OnChangeMsgStatusByUser::class
        ],
        MessageStatusChecked::class => [
            CreateDnContract::class
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
}
