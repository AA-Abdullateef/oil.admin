<?php

use App\Http\Controllers\API\V1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Public ───────────────────────────────────────────────────
    Route::post('/register',        [V1\AuthController::class, 'register']);
    Route::post('/verify-registration-otp', [V1\AuthController::class, 'verifyRegistrationOtp']);
    Route::post('/resend-registration-otp', [V1\AuthController::class, 'resendRegistrationOtp']);
    Route::post('/login',           [V1\AuthController::class, 'login']);
    Route::post('/forgot-password', [V1\AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-otp', [V1\AuthController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [V1\AuthController::class, 'resetPassword']);
    Route::post('/contact',         [V1\ContactController::class, 'store']);

    // Public settings: platform info, payment details, and limits
    Route::get('/settings', [V1\SettingController::class, 'public']);

    // ── Location data ───────────────────────────────────────────────
    Route::get('/countries',                         [V1\LocationController::class, 'countries']);
    Route::get('/countries/{countrySlug}/states',    [V1\LocationController::class, 'statesBySlug']);
    Route::get('/states',                            [V1\LocationController::class, 'states']);


    // ── Authenticated (no KYC required) ──────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [V1\AuthController::class, 'logout']);
        Route::get('/me',      [V1\AuthController::class, 'me']);
        Route::get('/dashboard', [V1\DashboardController::class, 'show']);

        // Profile — allowed before KYC so the user can fill it in
        Route::get('/profile', [V1\ProfileController::class, 'show']);
        Route::put('/profile', [V1\ProfileController::class, 'update']);

        // KYC submission — must be accessible before verification
        Route::get('/kyc/status', [V1\KycController::class, 'status']);
        Route::post('/kyc/submit', [V1\KycController::class, 'submit']);

        // Assets - read-only, no KYC required
        Route::get('/assets',          [V1\AssetController::class, 'index']);
        Route::get('/assets/{asset}',  [V1\AssetController::class, 'show']);
        Route::get('/methods', [V1\MethodController::class, 'index']);
        Route::get('/methods/{method}/sub-methods', [V1\MethodController::class, 'subMethods']);
        Route::get('/sub-methods/{subMethod}', [V1\MethodController::class, 'showSubMethod']);

        // Notifications - allowed before KYC so users can get notified about verification results
        Route::get('/notifications',              [V1\NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read',   [V1\NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all',    [V1\NotificationController::class, 'markAllRead']);

        // Transaction proof files - owner can view existing files even if KYC status later changes.
        Route::get('/deposits/{deposit}/proof', [V1\ProofController::class, 'deposit'])
            ->name('api.v1.deposits.proof');
        Route::get('/withdrawals/{withdrawal}/proof', [V1\ProofController::class, 'withdrawal'])
            ->name('api.v1.withdrawals.proof');

        // ── KYC-gated: all financial operations ──────────────────
        Route::middleware('kyc')->group(function () {

            // Balances
            Route::get('/balances',              [V1\BalanceController::class, 'show']);
            Route::get('/balances/transactions', [V1\BalanceController::class, 'transactions']);
            Route::get('/balances/earnings', [V1\BalanceController::class, 'earnings']);

            // Deposits
            Route::get('/deposits',           [V1\DepositController::class, 'index']);
            Route::post('/deposits',          [V1\DepositController::class, 'store']);
            Route::get('/deposits/{deposit}', [V1\DepositController::class, 'show']);

            // Withdrawals
            Route::get('/withdrawals',                [V1\WithdrawalController::class, 'index']);
            Route::post('/withdrawals',               [V1\WithdrawalController::class, 'store']);
            Route::get('/withdrawals/{withdrawal}',   [V1\WithdrawalController::class, 'show']);

            // Trades
            Route::post('/trades/buy',  [V1\TradeController::class, 'buy']);
            Route::post('/trades/sell', [V1\TradeController::class, 'sell']);

            // Holdings
            Route::middleware('has_permission:view_holdings')->group(function () {
                Route::get('/holdings',         [V1\HoldingController::class, 'index']);
                Route::get('/holdings/trades',  [V1\HoldingController::class, 'trades']);
                Route::get('/holdings/{asset}', [V1\HoldingController::class, 'show']);
            });

            // Transactions
            Route::middleware('has_permission:view_transactions')->group(function () {
                Route::get('/transactions',               [V1\TransactionController::class, 'index']);
                Route::get('/transactions/{transaction}', [V1\TransactionController::class, 'show']);
            });
        });
    });
});
