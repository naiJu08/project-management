<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('List leave requests') || 
               $user->can('Approve leave requests');
    }

    public function view(User $user, LeaveRequest $leaveRequest)
    {
        // Can view own requests, or has permission, or is the manager
        return $user->id === $leaveRequest->user_id ||
               $user->can('View leave request') ||
               $user->can('Approve leave requests') ||
               $this->isManager($user, $leaveRequest->user);
    }

    public function create(User $user)
    {
        // Anyone can create their own leave request
        return true;
    }

    public function update(User $user, LeaveRequest $leaveRequest)
    {
        // Can update own pending requests, or has permission
        if ($user->id === $leaveRequest->user_id && $leaveRequest->status === 'pending') {
            return true;
        }

        return $user->can('Update leave request');
    }

    public function delete(User $user, LeaveRequest $leaveRequest)
    {
        // Can delete own pending requests, or has permission
        if ($user->id === $leaveRequest->user_id && $leaveRequest->status === 'pending') {
            return true;
        }

        return $user->can('Delete leave request');
    }

    public function approve(User $user, LeaveRequest $leaveRequest)
    {
        // Can approve if has permission or is the manager
        return $user->can('Approve leave requests') || 
               $this->isManager($user, $leaveRequest->user);
    }

    public function reject(User $user, LeaveRequest $leaveRequest)
    {
        // Same as approve
        return $this->approve($user, $leaveRequest);
    }

    /**
     * Check if user is the manager of the leave requester.
     */
    protected function isManager(User $user, User $employee): bool
    {
        $employeeProfile = $employee->employeeProfile;
        return $employeeProfile && $employeeProfile->manager_id === $user->id;
    }
}
