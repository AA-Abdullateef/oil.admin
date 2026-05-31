<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReferenceService
{
    private const PREFIX = [
        'deposit' => 'DEP',
        'withdrawal' => 'WDR',
        'buy' => 'BUY',
        'sell' => 'SEL',
        'default' => 'TXN',
    ];

    public function forTransaction(string $type): string
    {
        $prefix = self::PREFIX[$type] ?? self::PREFIX['default'];
        $date = now()->format('Ymd');
        $sequence = $this->nextDailySequence($prefix, $date);
        $numeric = $date . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
        $check = $this->luhnCheckDigit($numeric);

        return "{$prefix}-{$date}-" . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT) . "-{$check}";
    }

    public function forDeposit(): string
    {
        return $this->forTransaction(Transaction::TYPE_DEPOSIT);
    }

    public function forWithdrawal(): string
    {
        return $this->forTransaction(Transaction::TYPE_WITHDRAWAL);
    }

    public function verify(string $reference): bool
    {
        $parts = explode('-', $reference);

        if (count($parts) !== 4) {
            return false;
        }

        return $this->luhnCheckDigit($parts[1] . $parts[2]) === (int) $parts[3];
    }

    private function nextDailySequence(string $prefix, string $date): int
    {
        return DB::transaction(fn () => DB::table('transactions')
            ->where('reference', 'like', "{$prefix}-{$date}-%")
            ->lockForUpdate()
            ->count() + 1);
    }

    private function luhnCheckDigit(string $digits): int
    {
        $sum = 0;
        $double = false;

        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $digit = (int) $digits[$i];

            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = ! $double;
        }

        return (10 - ($sum % 10)) % 10;
    }
}
