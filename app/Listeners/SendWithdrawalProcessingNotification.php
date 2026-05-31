<?php

namespace App\Listeners;

use App\Events\WithdrawalProcessing;
use App\Notifications\WithdrawalProcessingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWithdrawalProcessingNotification implements ShouldQueue
{
    public function handle(WithdrawalProcessing $event): void
    {
        $event->withdrawal->user->notify(
            new WithdrawalProcessingNotification($event->withdrawal)
        );
    }
}
