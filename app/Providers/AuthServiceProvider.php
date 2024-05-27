<?php

namespace App\Providers;

use App\Models\DispListEntry;
use App\Models\File;
use App\Models\Message;
use App\Models\User;
use App\Policies\DispListEntriesPolicy;
use App\Policies\FilePolicy;
use App\Policies\MessagePolicy;
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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        File::class => FilePolicy::class,
        Message::class => MessagePolicy::class,
        User::class => UserPolicy::class,
        DispListEntry::class => DispListEntriesPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
