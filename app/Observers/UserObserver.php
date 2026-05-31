<?php

namespace App\Observers;

use App\Events\AccountSuspended;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function created(User $user): void
    {
        if (! $user->profile()->exists()) {
            $user->profile()->create();
        }
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('status') && in_array($user->status, ['suspended', 'banned'], true)) {
            event(new AccountSuspended($user));
            $user->tokens()->delete();

            Log::info("User {$user->id} status changed to {$user->status}; tokens revoked.");
        }
    }

    public function deleted(User $user): void
    {
        $user->tokens()->delete();
    }
}
