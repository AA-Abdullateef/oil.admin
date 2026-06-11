<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

// ── Admin auth (guest only) ──────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('guest')->group(function () {
    Route::get('/login',  [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->name('login.submit');
});

// ── Admin logout (authenticated) ────────────────────────────────
Route::post('/admin/logout', [Admin\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

// ── Admin panel ─────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::middleware('has_permission:manage_users')->group(function () {
            Route::resource('users', Admin\UserController::class)->except(['create', 'store', 'edit']);
            Route::post('users/{user}/verify-email', [Admin\UserController::class, 'verifyEmail'])->name('users.verify-email');
            Route::post('users/{user}/assign-role', [Admin\UserController::class, 'assignRole'])->name('users.assign-role');
            Route::post('users/{user}/remove-role', [Admin\UserController::class, 'removeRole'])->name('users.remove-role');
        });

        // Deposits
        Route::middleware('has_permission:manage_transactions')->group(function () {
            Route::get('deposits',           [Admin\DepositController::class, 'index'])->name('deposits.index');
            Route::get('deposits/{deposit}', [Admin\DepositController::class, 'show'])->name('deposits.show');
            Route::get('deposits/{deposit}/proof', [Admin\ProofController::class, 'deposit'])->name('deposits.proof');
            Route::post('deposits/{deposit}/cancel', [Admin\DepositController::class, 'cancel'])->name('deposits.cancel');
        });
        Route::post('deposits/{deposit}/complete', [Admin\DepositController::class, 'complete'])
            ->middleware('has_permission:complete_deposits')
            ->name('deposits.complete');

        // Withdrawals
        Route::middleware('has_permission:manage_transactions')->group(function () {
            Route::get('withdrawals',              [Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
            Route::get('withdrawals/{withdrawal}', [Admin\WithdrawalController::class, 'show'])->name('withdrawals.show');
            Route::get('withdrawals/{withdrawal}/proof', [Admin\ProofController::class, 'withdrawal'])->name('withdrawals.proof');
            Route::post('withdrawals/{withdrawal}/cancel', [Admin\WithdrawalController::class, 'cancel'])->name('withdrawals.cancel');
        });
        Route::post('withdrawals/{withdrawal}/process', [Admin\WithdrawalController::class, 'process'])
            ->middleware('has_permission:process_withdrawals')
            ->name('withdrawals.process');

        // Assets
        Route::post('assets/sync-prices', [Admin\AssetController::class, 'syncPrices'])
            ->middleware('has_permission:manage_assets')
            ->name('assets.sync-prices');
        Route::resource('assets', Admin\AssetController::class)
            ->except(['show'])
            ->middleware('has_permission:manage_assets');

        // Roles & Permissions
        Route::resource('roles', Admin\RoleController::class)
            ->except(['show'])
            ->middleware('has_permission:manage_roles');
        Route::resource('permissions', Admin\PermissionController::class)
            ->except(['show'])
            ->middleware('has_permission:manage_permissions');

        // KYC — critical for compliance, must be easily accessible
        Route::middleware('has_permission:manage_kyc')->group(function () {
            Route::get('kyc',                          [Admin\KycController::class, 'index'])->name('kyc.index');
            Route::get('kyc/{profile}',                [Admin\KycController::class, 'show'])->name('kyc.show');
            Route::post('kyc/{profile}/approve',       [Admin\KycController::class, 'approve'])->name('kyc.approve');
            Route::post('kyc/{profile}/reject',        [Admin\KycController::class, 'reject'])->name('kyc.reject');
            Route::post('kyc/{profile}/request-info',  [Admin\KycController::class, 'requestInfo'])->name('kyc.request-info');
            Route::post('kyc/{profile}/under-review',  [Admin\KycController::class, 'markUnderReview'])->name('kyc.under-review');
        });

        // Balances
        Route::middleware('has_permission:manage_balances')->group(function () {
            Route::get('balances',        [Admin\BalanceController::class, 'index'])->name('balances.index');
            Route::get('balances/{user}', [Admin\BalanceController::class, 'show'])->name('balances.show');
        });

        // Holdings, earning schedules & transactions
        Route::get('holdings', [Admin\HoldingController::class, 'index'])
            ->middleware('has_permission:view_holdings')
            ->name('holdings.index');
        Route::middleware('has_permission:manage_transactions')->group(function () {
            Route::post('earning-schedules/{earningSchedule}/pause', [Admin\EarningScheduleController::class, 'pause'])->name('earning-schedules.pause');
            Route::post('earning-schedules/{earningSchedule}/resume', [Admin\EarningScheduleController::class, 'resume'])->name('earning-schedules.resume');
            Route::resource('earning-schedules', Admin\EarningScheduleController::class)->except(['show']);
        });
        Route::middleware('has_permission:view_transaction_logs')->group(function () {
            Route::get('transactions',               [Admin\TransactionController::class, 'index'])->name('transactions.index');
            Route::get('transactions/{transaction}', [Admin\TransactionController::class, 'show'])->name('transactions.show');
            Route::get('audit-logs',                 [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        });

        Route::get('contact-messages', [Admin\ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [Admin\ContactMessageController::class, 'show'])->name('contact-messages.show');

        Route::middleware('has_permission:manage_settings')->group(function () {
            Route::resource('sub-methods', Admin\SubMethodController::class)->except(['show']);
            Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        });
    });


Route::get('/mail-test', function () {
    Mail::raw('Mail is working.', function ($message) {
        $message->to('adeyemoabdullateef94@gmail.com')
            ->subject('Mail Test');
    });

    return 'Mail sent';
});