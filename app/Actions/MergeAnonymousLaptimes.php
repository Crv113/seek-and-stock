<?php

namespace App\Actions;

use App\Models\AnonymousUser;
use App\Models\User;

class MergeAnonymousLaptimes
{
    public function handle(User $user): bool
    {
        $updated = AnonymousUser::where('guid', $user->guid)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        return $updated > 0;
    }
}
