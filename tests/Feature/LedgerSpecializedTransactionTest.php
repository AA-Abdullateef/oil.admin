<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Deposit;
use App\Models\Method;
use App\Models\SubMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\DepositService;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerSpecializedTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_service_returns_deposit_model(): void
    {
        $user = User::factory()->create();
        $asset = $this->createAsset();
        $method = $this->createMethod();
        $subMethod = $this->createSubMethod($method);

        $deposit = app(DepositService::class)->initiate($user, [
            'asset_id' => $asset->id,
            'sub_method_id' => $subMethod->id,
            'amount' => '125.50',
        ]);

        $this->assertInstanceOf(Deposit::class, $deposit);
        $this->assertSame(Transaction::TYPE_DEPOSIT, $deposit->type);
        $this->assertNotNull($deposit->depositProof);
    }

    public function test_ledger_service_returns_withdrawal_model_for_withdrawal_records(): void
    {
        $user = User::factory()->create();
        $asset = $this->createAsset();
        $method = $this->createMethod();
        $subMethod = $this->createSubMethod($method);

        $withdrawal = app(LedgerService::class)->recordWithdrawal($user, $asset, $subMethod, '50');

        $this->assertInstanceOf(Withdrawal::class, $withdrawal);
        $this->assertSame(Transaction::TYPE_WITHDRAWAL, $withdrawal->type);
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
}
