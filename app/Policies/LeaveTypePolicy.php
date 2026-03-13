<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('List leave types');
    }

    public function view(User $user, LeaveType $leaveType)
    {
        return $user->can('View leave type');
    }

    public function create(User $user)
    {
        return $user->can('Create leave type');
    }

    public function update(User $user, LeaveType $leaveType)
    {
        return $user->can('Update leave type');
    }

    public function delete(User $user, LeaveType $leaveType)
    {
        return $user->can('Delete leave type');
    }

    public function restore(User $user, LeaveType $leaveType)
    {
        return $user->can('Delete leave type');
    }

    public function forceDelete(User $user, LeaveType $leaveType)
    {
        return $user->can('Delete leave type');
    }
}
