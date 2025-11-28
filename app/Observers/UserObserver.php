<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->ensureSingleRole($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->ensureSingleRole($user);
    }

    private function ensureSingleRole(User $user): void
    {
        $roles = $user->roles;
        
        if ($roles->count() > 1) {
            $firstRole = $roles->first();
            $user->syncRoles([$firstRole->name]);
        }
    }
}
