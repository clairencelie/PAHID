<?php

namespace App\Policies;

use App\Models\Prospect;
use App\Models\User;

class ProspectPolicy
{
    public function edit(User $user, Prospect $prospect): bool
    {
        if ($user->hasRole(['bc', 'supervisor', 'admin'])) {
            return true;
        }

        return $user->id === $prospect->marketing_user_id
            && in_array($prospect->status, ['DRAFT', 'NEED_CLARIFICATION']);
    }

    public function delete(User $user, Prospect $prospect): bool
    {
        return $user->hasRole(['admin', 'supervisor'])
            || ($user->id === $prospect->marketing_user_id && $prospect->status === 'DRAFT');
    }
}
