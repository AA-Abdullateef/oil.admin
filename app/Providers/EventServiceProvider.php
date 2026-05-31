<?php

namespace App\Providers;

use App\Events\AccountSuspended;
use App\Events\DepositCompleted;
use App\Events\DepositCancelled;
use App\Events\WithdrawalProcessing;
use App\Events\WithdrawalCancelled;
use App\Listeners\SendAccountSuspendedNotification;
use App\Listeners\SendDepositCompletedNotification;
use App\Listeners\SendDepositCancelledNotification;
use App\Listeners\SendWithdrawalProcessingNotification;
use App\Listeners\SendWithdrawalCancelledNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DepositCompleted::class => [
            SendDepositCompletedNotification::class,
        ],
        DepositCancelled::class => [
            SendDepositCancelledNotification::class,
        ],
        WithdrawalProcessing::class => [
            SendWithdrawalProcessingNotification::class,
        ],
        WithdrawalCancelled::class => [
            SendWithdrawalCancelledNotification::class,
        ],
        AccountSuspended::class => [
            SendAccountSuspendedNotification::class,
        ],
    ];
}
