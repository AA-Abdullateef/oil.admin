<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'pending_deposits' => Deposit::where('status', Transaction::STATUS_PENDING)->count(),
            'pending_withdrawals' => Withdrawal::where('status', Transaction::STATUS_PENDING)->count(),
            'total_wallet_balance' => Transaction::where('direction', Transaction::DIRECTION_CREDIT)
                ->whereIn('status', Transaction::BALANCE_STATUSES)
                ->sum('amount') - Transaction::where('direction', Transaction::DIRECTION_DEBIT)
                ->whereIn('status', Transaction::BALANCE_STATUSES)
                ->sum('amount'),
            'total_assets' => Asset::where('status', Asset::STATUS_ACTIVE)->count(),
            'total_transactions' => Transaction::count(),
            'monthly_credits' => Transaction::where('direction', Transaction::DIRECTION_CREDIT)
                ->whereIn('status', Transaction::BALANCE_STATUSES)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'monthly_debits' => Transaction::where('direction', Transaction::DIRECTION_DEBIT)
                ->whereIn('status', Transaction::BALANCE_STATUSES)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        $recentDeposits = Deposit::with(['user', 'asset'])->latest()->limit(5)->get();
        $recentWithdrawals = Withdrawal::with(['user', 'asset'])->latest()->limit(5)->get();
        $recentUsers = User::latest()->limit(5)->get();
        $recentAuditLogs = AuditLog::with('user')->latest()->limit(8)->get();

        $needsAttention = [
            ['label' => 'Pending deposits', 'count' => $stats['pending_deposits'], 'href' => route('admin.deposits.index', ['status' => 'pending']), 'tone' => 'amber'],
            ['label' => 'Pending withdrawals', 'count' => $stats['pending_withdrawals'], 'href' => route('admin.withdrawals.index', ['status' => 'pending']), 'tone' => 'red'],
            ['label' => 'Cancelled transactions', 'count' => Transaction::where('status', Transaction::STATUS_CANCELLED)->count(), 'href' => route('admin.transactions.index', ['status' => 'cancelled']), 'tone' => 'red'],
            ['label' => 'Suspended users', 'count' => User::whereIn('status', ['suspended', 'banned'])->count(), 'href' => route('admin.users.index'), 'tone' => 'blue'],
        ];

        $chartAssets = Asset::where('status', Asset::STATUS_ACTIVE)
            ->whereIn('type', [Asset::TYPE_SHARE, Asset::TYPE_COMMODITY])
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get()
            ->map(function (Asset $asset) {
                $history = $asset->dynamicPriceHistory();

                $first = (float) $history->first()->price;
                $last = (float) $history->last()->price;

                return [
                    'name' => $asset->name,
                    'symbol' => $asset->symbol,
                    'currency' => config('app.currency', '$'),
                    'current_price' => $asset->current_price,
                    'change' => $first > 0 ? (($last - $first) / $first) * 100 : 0,
                    'points' => $this->sparklinePoints($history->pluck('price')->map(fn ($price) => (float) $price)->all()),
                ];
            });

        return view('admin.dashboard', compact(
            'stats',
            'recentDeposits',
            'recentWithdrawals',
            'recentUsers',
            'recentAuditLogs',
            'needsAttention',
            'chartAssets'
        ));
    }

    private function sparklinePoints(array $values): string
    {
        if (count($values) === 1) {
            $values[] = $values[0];
        }

        $min = min($values);
        $max = max($values);
        $range = $max - $min;
        $lastIndex = max(count($values) - 1, 1);

        return collect($values)
            ->map(function (float $value, int $index) use ($min, $range, $lastIndex) {
                $x = ($index / $lastIndex) * 100;
                $y = $range > 0 ? 38 - ((($value - $min) / $range) * 32) : 20;

                return round($x, 2) . ',' . round($y, 2);
            })
            ->implode(' ');
    }
}
