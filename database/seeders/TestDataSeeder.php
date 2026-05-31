<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\DepositProof;
use App\Models\EarningSchedule;
use App\Models\Method;
use App\Models\Role;
use App\Models\SubMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalProof;
use App\Support\Roles\RoleManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $depositProofPath = $this->copySeederFileToPublicProofs('inbankimgtemplate1.jpg');
        $withdrawalProofPath = $this->copySeederFileToPublicProofs('ppa_Letter.pdf');

        $userRole = Role::where('slug', RoleManager::USER)->first();

        $users = collect([
            ['name' => 'Ada Okafor', 'email' => 'ada@example.com'],
            ['name' => 'Tunde Balogun', 'email' => 'tunde@example.com'],
            ['name' => 'Maryam Bello', 'email' => 'maryam@example.com'],
        ])->map(function (array $data) use ($userRole) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            if ($userRole) {
                $user->roles()->syncWithoutDetaching($userRole);
            }

            $user->profile()->firstOrCreate([], ['kyc_status' => 'verified']);

            return $user;
        });

        $assets = collect([
            ['symbol' => 'USD', 'name' => 'US Dollar', 'type' => 'currency', 'current_price' => '1.00000000', 'price_source' => 'manual'],
            ['symbol' => 'USDT', 'name' => 'Tether USD', 'type' => 'crypto', 'current_price' => '1.00000000', 'price_source' => 'manual'],
            ['symbol' => 'SHELL', 'name' => 'Shell Internal Oil Asset', 'type' => 'share', 'current_price' => '68.25000000', 'price_source' => 'alphavantage'],
            ['symbol' => 'DANGOTE', 'name' => 'Dangote Internal Asset', 'type' => 'commodity', 'current_price' => '42.50000000', 'price_source' => 'manual'],
        ])->mapWithKeys(function (array $data) {
            $asset = Asset::updateOrCreate(['symbol' => $data['symbol']], $data + ['status' => 'active']);

            return [$asset->symbol => $asset];
        });

        $methods = collect(['Bank Transfer', 'Cryptocurrency'])
            ->mapWithKeys(fn (string $name) => [$name => Method::firstOrCreate(['name' => $name])]);

        $subMethods = collect([
            [
                'method' => 'Bank Transfer',
                'name' => 'Oil Admin Bank',
                'account_name' => 'Oil Admin',
                'account_number' => '0123456789',
                'bank_name' => 'Oil Admin Bank',
                'instructions' => 'Use your transaction reference as the transfer narration.',
            ],
            [
                'method' => 'Cryptocurrency',
                'name' => 'USDT (TRC20)',
                'wallet_address' => 'TSeedWalletAddress123',
                'network' => 'TRC20',
                'instructions' => 'Send only USDT on TRC20.',
            ],
            [
                'method' => 'Cryptocurrency',
                'name' => 'Bitcoin',
                'wallet_address' => 'bc1qseedwalletaddress',
                'network' => 'BTC',
                'instructions' => 'Send only BTC to this address.',
            ],
        ])->mapWithKeys(function (array $data) use ($methods) {
            $methodName = $data['method'];
            unset($data['method']);

            $subMethod = SubMethod::updateOrCreate(
                ['method_id' => $methods[$methodName]->id, 'name' => $data['name']],
                $data + ['method_id' => $methods[$methodName]->id, 'is_active' => true]
            );

            return [$data['name'] => $subMethod];
        });

        $ada = $users[0];
        $tunde = $users[1];

        $deposit = Transaction::updateOrCreate(
            ['reference' => 'Deposit via Bank Transfer', 'user_id' => $ada->id],
            [
                'asset_id' => $assets['USD']->id,
                'method_id' => $methods['Bank Transfer']->id,
                'sub_method_id' => $subMethods['Oil Admin Bank']->id,
                'type' => Transaction::TYPE_DEPOSIT,
                'direction' => Transaction::DIRECTION_CREDIT,
                'amount' => '12000.00000000',
                'status' => Transaction::STATUS_COMPLETED,
                'updated_by' => User::where('email', 'superadmin@gmail.com')->value('id'),
                'updated_at' => now()->subDays(3),
            ]
        );

        DepositProof::updateOrCreate(
            ['transaction_id' => $deposit->id],
            ['proof' => $depositProofPath]
        );

        Transaction::updateOrCreate(
            ['reference' => 'Buy SHELL from USD', 'user_id' => $ada->id, 'asset_id' => $assets['USD']->id, 'direction' => Transaction::DIRECTION_DEBIT],
            [
                'method_id' => null,
                'type' => Transaction::TYPE_BUY,
                'amount' => '6825.00000000',
                'status' => Transaction::STATUS_COMPLETED,
            ]
        );

        Transaction::updateOrCreate(
            ['reference' => 'Buy SHELL from USD', 'user_id' => $ada->id, 'asset_id' => $assets['SHELL']->id, 'direction' => Transaction::DIRECTION_CREDIT],
            [
                'method_id' => null,
                'type' => Transaction::TYPE_BUY,
                'amount' => '6825.00000000',
                'status' => Transaction::STATUS_COMPLETED,
            ]
        );

        // Deposit and buy transactions for Tunde
        $deposit2 = Transaction::updateOrCreate(
            ['reference' => 'Deposit via Bank Transfer', 'user_id' => $tunde->id],
            [
                'asset_id' => $assets['USD']->id,
                'method_id' => $methods['Bank Transfer']->id,
                'sub_method_id' => $subMethods['Oil Admin Bank']->id,
                'type' => Transaction::TYPE_DEPOSIT,
                'direction' => Transaction::DIRECTION_CREDIT,
                'amount' => '5000.00000000',
                'status' => Transaction::STATUS_COMPLETED,
                'updated_by' => User::where('email', 'superadmin@gmail.com')->value('id'),
                'updated_at' => now()->subDays(3),
            ]
        );

        DepositProof::updateOrCreate(['transaction_id' => $deposit2->id], ['proof' => null]);

        Transaction::updateOrCreate(
            ['reference' => 'Buy DANGOTE from USD', 'user_id' => $tunde->id, 'asset_id' => $assets['USD']->id, 'direction' => Transaction::DIRECTION_DEBIT],
            [
                'method_id' => null,
                'type' => Transaction::TYPE_BUY,
                'amount' => '4250.00000000',
                'status' => Transaction::STATUS_COMPLETED,
            ]
        );

        Transaction::updateOrCreate(
            ['reference' => 'Deposit via USDT TRC20', 'user_id' => $tunde->id],
            [
                'asset_id' => $assets['USDT']->id,
                'method_id' => $methods['Cryptocurrency']->id,
                'sub_method_id' => $subMethods['USDT (TRC20)']->id,
                'type' => Transaction::TYPE_DEPOSIT,
                'direction' => Transaction::DIRECTION_CREDIT,
                'amount' => '1000.00000000',
                'status' => Transaction::STATUS_COMPLETED,
                'updated_by' => User::where('email', 'superadmin@gmail.com')->value('id'),
                'updated_at' => now()->subDays(2),
            ]
        );

        $withdrawal = Transaction::updateOrCreate(
            ['reference' => 'Withdrawal to USDT TRC20', 'user_id' => $tunde->id],
            [
                'asset_id' => $assets['USDT']->id,
                'method_id' => $methods['Cryptocurrency']->id,
                'sub_method_id' => $subMethods['USDT (TRC20)']->id,
                'type' => Transaction::TYPE_WITHDRAWAL,
                'direction' => Transaction::DIRECTION_DEBIT,
                'amount' => '250.00000000',
                'status' => Transaction::STATUS_PENDING,
            ]
        );

        WithdrawalProof::updateOrCreate(
            ['transaction_id' => $withdrawal->id],
            [
                'destination_type' => 'crypto',
                'wallet_address' => 'TUserSeedWallet123',
                'network' => 'TRC20',
                'proof' => $withdrawalProofPath,
            ]
        );

        EarningSchedule::updateOrCreate(
            ['asset_id' => $assets['USD']->id, 'frequency' => EarningSchedule::FREQUENCY_DAILY],
            [
                'percentage' => '1.0000',
                'start_date' => now()->startOfDay()->addDay(),
                'next_run_at' => now()->startOfDay()->addDay(),
                'status' => EarningSchedule::STATUS_ACTIVE,
                'created_by' => User::where('email', 'superadmin@gmail.com')->value('id'),
            ]
        );

        AuditLog::updateOrCreate(
            ['event' => 'seed_data_refreshed', 'auditable_type' => null, 'auditable_id' => null],
            ['id' => (string) Str::uuid(), 'metadata' => ['source' => static::class], 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function copySeederFileToPublicProofs(string $filename): string
    {
        $source = database_path("seeders/files/{$filename}");
        $target = "proofs/testing/{$filename}";

        if (is_file($source) && ! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }
}
