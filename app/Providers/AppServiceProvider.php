<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\DepositProof;
use App\Models\Earning;
use App\Models\Method;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalProof;
use App\Observers\AuditObserver;
use App\Observers\AssetObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

            return $frontendUrl.'/reset-password?'.http_build_query([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });

        Asset::observe([AuditObserver::class, AssetObserver::class]);
        DepositProof::observe(AuditObserver::class);
        Earning::observe(AuditObserver::class);
        Method::observe(AuditObserver::class);
        Permission::observe(AuditObserver::class);
        Role::observe(AuditObserver::class);
        Transaction::observe(AuditObserver::class);
        User::observe(UserObserver::class);
        User::observe(AuditObserver::class);
        WithdrawalProof::observe(AuditObserver::class);

        Paginator::defaultView('admin.partials.pagination');
    }
}
