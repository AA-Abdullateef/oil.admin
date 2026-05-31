<?php

namespace App\Listeners;

use App\Events\AccountSuspended;
use App\Notifications\AccountSuspendedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAccountSuspendedNotification implements ShouldQueue
{
    public function handle(AccountSuspended $event): void
    {
        $event->user->notify(new AccountSuspendedNotification());
    }
}