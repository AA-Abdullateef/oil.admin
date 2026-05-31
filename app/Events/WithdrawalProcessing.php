<?php

namespace App\Events;

use App\Models\Withdrawal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalProcessing
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Withdrawal $withdrawal) {}
}
