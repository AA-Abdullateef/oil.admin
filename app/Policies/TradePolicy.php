<?php

namespace App\Policies;

use App\Models\User;

class TradePolicy
{
    public function buy(User $user): bool
    {
        return $user->hasPermission('buy_assets') && $user->status === 'active';
    }

    public function sell(User $user): bool
    {
        return $user->hasPermission('sell_assets') && $user->status === 'active';
    }
}
