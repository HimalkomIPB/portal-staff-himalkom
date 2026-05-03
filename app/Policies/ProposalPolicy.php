<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;


class ProposalPolicy
{
    public function viewAny(User $user): bool  {
        return true;
    }

    public function view(User $user, Proposal $proposal): bool {
        return $user->id === $proposal->uploader_id || $user->hasRole('bph') || $user->hasRole('managing director');
    }

    public function create(User $user): bool {
        return $user->hasRole('managing director');
    }

    public function update(User $user, Proposal $proposal): bool {
        return $user->id === $proposal->uploader_id || $user->hasRole('bph') ;
    }

    public function delete(User $user, Proposal $proposal): bool {
        return $user->hasRole('bph') ;
    }

    public function review(User $user, Proposal $proposal): bool {
        return $user->hasRole('bph') && $proposal->status === 'pending';
    }
}
