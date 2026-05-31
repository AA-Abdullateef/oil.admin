<?php

namespace App\Policies;

use App\Models\SubMethod;
use App\Models\User;

class SubMethodPolicy
{
    public function delete(User $user, SubMethod $subMethod): bool
    {
        return $user->hasPermission('manage_settings')
            && ! $subMethod->transactions()->exists();
    }
}
