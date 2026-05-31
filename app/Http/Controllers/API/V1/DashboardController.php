<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService,
        private readonly SettingService $settings,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile.country', 'profile.state');
        $balances = $this->balanceService->getAllBalances($user);

        $recentTransactions = $user->transactions()
            ->with(['asset', 'method'])
            ->latest()
            ->limit(8)
            ->get();

        $monthlyCredits = $this->monthlyTransactionTotal($user->id, Transaction::DIRECTION_CREDIT);
        $monthlyDebits = $this->monthlyTransactionTotal($user->id, Transaction::DIRECTION_DEBIT);

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                ],
                'account' => [
                    'kyc_status' => $user->profile?->kyc_status ?? 'pending',
                    'profile_complete' => $this->profileIsComplete($user->profile),
                    'can_transact' => $user->status === 'active' && $user->profile?->kyc_status === 'verified',
                    'unread_notifications' => $user->unreadNotifications()->count(),
                ],
                'portfolio' => [
                    'balances_count' => $balances->count(),
                    'balances' => $balances->map(fn (array $row) => [
                        'asset' => [
                            'id' => $row['asset']->id,
                            'symbol' => $row['asset']->symbol,
                            'name' => $row['asset']->name,
                            'type' => $row['asset']->type,
                        ],
                        'quantity' => $this->decimal($row['quantity']),
                        'value' => $this->decimal($row['value']),
                    ])->values(),
                ],
                'activity' => [
                    'total_transactions' => $user->transactions()->count(),
                    'monthly_credits' => $this->decimal($monthlyCredits),
                    'monthly_debits' => $this->decimal($monthlyDebits),
                    'pending_deposits' => $this->pendingSummary(Deposit::query(), $user->id),
                    'pending_withdrawals' => $this->pendingSummary(Withdrawal::query(), $user->id),
                    'recent_transactions' => TransactionResource::collection($recentTransactions),
                ],
                'limits' => [
                    'min_deposit_amount' => $this->settings->get('min_deposit_amount', 10),
                    'max_deposit_amount' => $this->settings->get('max_deposit_amount', 50000),
                    'min_withdrawal_amount' => $this->settings->get('min_withdrawal_amount', 20),
                    'max_withdrawal_amount' => $this->settings->get('max_withdrawal_amount', 20000),
                ],
                'next_actions' => $this->nextActions($user),
            ],
        ]);
    }

    private function monthlyTransactionTotal(string $userId, string $direction): string
    {
        return (string) Transaction::query()
            ->where('user_id', $userId)
            ->where('direction', $direction)
            ->whereIn('status', Transaction::BALANCE_STATUSES)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    }

    private function pendingSummary($query, string $userId): array
    {
        $query
            ->where('user_id', $userId)
            ->where('status', Transaction::STATUS_PENDING);

        return [
            'count' => (clone $query)->count(),
            'amount' => $this->decimal((string) (clone $query)->sum('amount')),
        ];
    }

    private function profileIsComplete(?object $profile): bool
    {
        return $profile
            && $profile->country_id
            && $profile->state_id
            && $profile->address
            && $profile->gender
            && $profile->date_of_birth;
    }

    private function nextActions(object $user): array
    {
        if ($user->status !== 'active') {
            return [[
                'type' => 'contact_support',
                'label' => 'Contact support',
                'href' => '/support',
                'priority' => 'high',
            ]];
        }

        if (! $this->profileIsComplete($user->profile)) {
            return [[
                'type' => 'complete_profile',
                'label' => 'Complete profile',
                'href' => '/profile',
                'priority' => 'high',
            ]];
        }

        return match ($user->profile?->kyc_status ?? 'pending') {
            'verified' => [[
                'type' => 'deposit',
                'label' => 'Fund account',
                'href' => '/deposits/create',
                'priority' => 'normal',
            ]],
            'submitted', 'under_review' => [[
                'type' => 'kyc_review',
                'label' => 'KYC under review',
                'href' => '/kyc',
                'priority' => 'normal',
            ]],
            default => [[
                'type' => 'submit_kyc',
                'label' => 'Submit KYC',
                'href' => '/kyc',
                'priority' => 'high',
            ]],
        };
    }

    private function decimal(string $value): string
    {
        return number_format((float) $value, 8, '.', '');
    }
}
