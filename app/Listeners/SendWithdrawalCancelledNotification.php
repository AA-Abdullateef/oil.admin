<?php

namespace App\Listeners;

use App\Events\WithdrawalCancelled;
use App\Notifications\WithdrawalCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWithdrawalCancelledNotification implements ShouldQueue
{
    public function handle(WithdrawalCancelled $event): void
    {
        $event->withdrawal->user->notify(
            new WithdrawalCancelledNotification($event->withdrawal)
        );
    }
}
