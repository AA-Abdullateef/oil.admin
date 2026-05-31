<?php

namespace App\Providers;

use App\Models\SubMethod;
use App\Policies\SubMethodPolicy;
use App\Policies\TradePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SubMethod::class => SubMethodPolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('buy-assets', [TradePolicy::class, 'buy']);
        Gate::define('sell-assets', [TradePolicy::class, 'sell']);
    }
}
