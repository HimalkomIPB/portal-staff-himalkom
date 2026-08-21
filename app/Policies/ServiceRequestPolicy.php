<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        // Pengaju (dan satu divisinya)
        if ($user->department_id === $serviceRequest->department_id) {
            return true;
        }

        // Pengelola Kreatif
        if ($serviceRequest->isKreatifService() && $user->department?->slug === 'creative') {
            return true;
        }

        // Pengelola RnT
        if ($serviceRequest->isRnTService() && $user->department?->slug === 'research-and-technology') {
            return true;
        }
        
        // Superadmin/Supervisor handled by AuthServiceProvider or User model typically
        if ($user->hasRole('supervisor')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All members can create request
        return $user->department_id !== null;
    }

    /**
     * Determine whether the user can update the model (change status/assign).
     */
    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        // Only managers can update status or upload final result
        if ($serviceRequest->isKreatifService() && $user->department?->slug === 'creative') {
            return true;
        }

        if ($serviceRequest->isRnTService() && $user->department?->slug === 'research-and-technology') {
            return true;
        }

        return false;
    }
    
    /**
     * Determine whether the user can upload a temporary attachment/comment.
     */
    public function comment(User $user, ServiceRequest $serviceRequest): bool
    {
        // Requester side
        if ($user->department_id === $serviceRequest->department_id) {
            return true;
        }
        
        // Manager side
        return $this->update($user, $serviceRequest);
    }
    
    /**
     * Determine whether the user can approve the final result.
     */
    public function approve(User $user, ServiceRequest $serviceRequest): bool
    {
        // Only the requester (or someone from their department) can approve the final result
        return $user->department_id === $serviceRequest->department_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        // Only the requester can delete it, and only if it's still pending
        return $user->id === $serviceRequest->requester_id && $serviceRequest->status === 'pending';
    }
}
