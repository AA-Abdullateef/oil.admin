<?php

namespace App\Listeners;

use App\Events\DepositCompleted;
use App\Notifications\DepositCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDepositCompletedNotification implements ShouldQueue
{
    public function handle(DepositCompleted $event): void
    {
        $event->deposit->user->notify(
            new DepositCompletedNotification($event->deposit)
        );
    }
}
