<?php

namespace App\Events;

use App\Models\Deposit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Deposit $deposit) {}
}
