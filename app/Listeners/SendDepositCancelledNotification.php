<?php

namespace App\Listeners;

use App\Events\DepositCancelled;
use App\Notifications\DepositCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDepositCancelledNotification implements ShouldQueue
{
    public function handle(DepositCancelled $event): void
    {
        $event->deposit->user->notify(
            new DepositCancelledNotification($event->deposit)
        );
    }
}
