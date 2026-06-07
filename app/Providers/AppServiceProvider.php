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
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->ensureWritableRuntimeDirectories();

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

    private function ensureWritableRuntimeDirectories(): void
    {
        $directories = [
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/testing'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($directories as $directory) {
            File::ensureDirectoryExists($directory, 0775, true);

            $probe = @tempnam($directory, 'probe');

            if ($probe === false || realpath(dirname($probe)) !== realpath($directory)) {
                throw new RuntimeException("Laravel runtime directory is not writable: {$directory}");
            }

            File::delete($probe);
        }
    }
}
