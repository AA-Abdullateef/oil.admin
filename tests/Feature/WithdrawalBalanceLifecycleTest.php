<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Deposit;
use App\Models\Method;
use App\Models\SubMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BalanceService;
use App\Services\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WithdrawalBalanceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_withdrawal_immediately_reduces_available_balance_and_processing_keeps_it_reduced(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create();
        $asset = $this->createAsset();
        $method = $this->createMethod();
        $subMethod = $this->createSubMethod($method);
        $balanceService = app(BalanceService::class);

        $this->createCompletedDeposit($user, $asset, $method, $subMethod, '100.00000000');

        $withdrawal = app(WithdrawalService::class)->request($user, [
            'asset_id' => $asset->id,
            'sub_method_id' => $subMethod->id,
            'amount' => '30',
            'destination_type' => 'bank',
            'account_name' => 'Ada Okafor',
            'account_number' => '0123456789',
            'bank_name' => 'Test Bank',
        ]);

        $this->assertSame('70.00000000', $balanceService->for($user, $asset));

        app(WithdrawalService::class)->process($withdrawal, $admin);

        $this->assertSame('70.00000000', $balanceService->for($user, $asset));
    }

    public function test_cancelled_pending_withdrawal_restores_available_balance(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create();
        $asset = $this->createAsset();
        $method = $this->createMethod();
        $subMethod = $this->createSubMethod($method);
        $balanceService = app(BalanceService::class);

        $this->createCompletedDeposit($user, $asset, $method, $subMethod, '100.00000000');

        $withdrawal = app(WithdrawalService::class)->request($user, [
            'asset_id' => $asset->id,
            'sub_method_id' => $subMethod->id,
            'amount' => '30',
            'destination_type' => 'crypto',
            'wallet_address' => 'TTestWalletAddress123',
            'network' => 'TRC20',
        ]);

        $this->assertSame('70.00000000', $balanceService->for($user, $asset));

        app(WithdrawalService::class)->cancel($withdrawal, $admin, 'Customer requested cancellation.');

        $this->assertSame('100.00000000', $balanceService->for($user, $asset));
    }

    private function createAsset(): Asset
    {
        return Asset::create([
            'symbol' => 'USD',
            'name' => 'US Dollar',
            'type' => Asset::TYPE_CURRENCY,
            'current_price' => '1.00000000',
            'status' => Asset::STATUS_ACTIVE,
        ]);
    }

    private function createMethod(): Method
    {
        return Method::create([
            'name' => 'Bank Transfer',
        ]);
    }

    private function createSubMethod(Method $method): SubMethod
    {
        return SubMethod::create([
            'method_id' => $method->id,
            'name' => 'Test Bank',
            'account_name' => 'Oil Admin',
            'account_number' => '0123456789',
            'bank_name' => 'Test Bank',
            'is_active' => true,
        ]);
    }

    private function createCompletedDeposit(User $user, Asset $asset, Method $method, SubMethod $subMethod, string $amount): Deposit
    {
        return Deposit::create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'method_id' => $method->id,
            'sub_method_id' => $subMethod->id,
            'type' => Transaction::TYPE_DEPOSIT,
            'direction' => Transaction::DIRECTION_CREDIT,
            'amount' => $amount,
            'reference' => 'Initial balance',
            'status' => Transaction::STATUS_COMPLETED,
        ]);
    }
}
