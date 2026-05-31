<?php

namespace Database\Seeders;

use App\Models\Method;
use App\Models\Setting;
use App\Models\SubMethod;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'platform_name',
                'value' => config('app.name'),
                'type' => 'string',
                'group' => 'platform',
                'label' => 'Platform name',
                'description' => 'Displayed in emails and the UI.',
                'is_public' => true,
            ],
            [
                'key' => 'platform_tagline',
                'value' => 'Invest in the energy that powers the world.',
                'type' => 'string',
                'group' => 'platform',
                'label' => 'Platform tagline',
                'is_public' => true,
            ],
            [
                'key' => 'support_email',
                'value' => 'support@example.com',
                'type' => 'string',
                'group' => 'platform',
                'label' => 'Support email',
                'is_public' => true,
            ],
            [
                'key' => 'support_phone',
                'value' => null,
                'type' => 'string',
                'group' => 'platform',
                'label' => 'Support phone number',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'platform',
                'label' => 'Maintenance mode',
                'description' => 'When enabled, the API returns 503 for all user requests.',
                'is_public' => false,
            ],
            [
                'key' => 'min_deposit_amount',
                'value' => '10',
                'type' => 'integer',
                'group' => 'limits',
                'label' => 'Minimum deposit (USD)',
                'is_public' => true,
            ],
            [
                'key' => 'max_deposit_amount',
                'value' => '50000',
                'type' => 'integer',
                'group' => 'limits',
                'label' => 'Maximum deposit (USD)',
                'is_public' => true,
            ],
            [
                'key' => 'min_withdrawal_amount',
                'value' => '20',
                'type' => 'integer',
                'group' => 'limits',
                'label' => 'Minimum withdrawal (USD)',
                'is_public' => true,
            ],
            [
                'key' => 'max_withdrawal_amount',
                'value' => '20000',
                'type' => 'integer',
                'group' => 'limits',
                'label' => 'Maximum withdrawal (USD)',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->migratePaymentSettingsToSubMethods();

        Setting::where('key', 'withdrawal_fee_percent')->delete();
        Setting::where('group', 'payment')->delete();

        $this->command->info('Platform settings seeded.');
    }

    private function migratePaymentSettingsToSubMethods(): void
    {
        $payment = Setting::where('group', 'payment')
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
            ->toArray();

        if ($payment === []) {
            return;
        }

        $bankMethod = Method::where('name', 'Bank Transfer')->first();
        $cryptoMethod = Method::where('name', 'Cryptocurrency')->first();

        if ($bankMethod && array_filter([
            $payment['bank_name'] ?? null,
            $payment['bank_account_name'] ?? null,
            $payment['bank_account_number'] ?? null,
            $payment['bank_routing_number'] ?? null,
            $payment['bank_swift_code'] ?? null,
            $payment['bank_iban'] ?? null,
        ])) {
            SubMethod::updateOrCreate(
                ['method_id' => $bankMethod->id, 'name' => $payment['bank_name'] ?: 'Bank Transfer'],
                [
                    'account_name' => $payment['bank_account_name'] ?? null,
                    'account_number' => $payment['bank_account_number'] ?? null,
                    'bank_name' => $payment['bank_name'] ?? null,
                    'routing_number' => $payment['bank_routing_number'] ?? null,
                    'swift_code' => $payment['bank_swift_code'] ?? null,
                    'iban' => $payment['bank_iban'] ?? null,
                    'instructions' => 'Migrated from legacy payment settings.',
                    'is_active' => true,
                ]
            );
        }

        if (! $cryptoMethod) {
            return;
        }

        foreach ([
            'crypto_btc_address' => ['Bitcoin', 'BTC'],
            'crypto_usdt_trc20_address' => ['USDT (TRC20)', 'TRC20'],
            'crypto_usdt_erc20_address' => ['USDT (ERC20)', 'ERC20'],
            'crypto_eth_address' => ['Ethereum', 'ERC20'],
        ] as $key => [$name, $network]) {
            if (empty($payment[$key])) {
                continue;
            }

            SubMethod::updateOrCreate(
                ['method_id' => $cryptoMethod->id, 'name' => $name],
                [
                    'wallet_address' => $payment[$key],
                    'network' => $network,
                    'instructions' => 'Migrated from legacy payment settings.',
                    'is_active' => true,
                ]
            );
        }
    }
}
