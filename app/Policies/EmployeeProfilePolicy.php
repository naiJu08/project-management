<?php

namespace App\Policies;

use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('List employee profiles') || 
               $user->can('View all employees');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeProfile  $employeeProfile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, EmployeeProfile $employeeProfile)
    {
        // Can view own profile, or has permission, or is the manager
        return $user->id === $employeeProfile->user_id ||
               $user->can('View employee profile') ||
               $user->can('View all employees') ||
               $user->id === $employeeProfile->manager_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('Create employee profile');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeProfile  $employeeProfile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, EmployeeProfile $employeeProfile)
    {
        // Can update own basic info, or has permission
        if ($user->id === $employeeProfile->user_id) {
            // Employees can only update certain fields (phone, address, emergency contact)
            return true;
        }

        return $user->can('Update employee profile');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeProfile  $employeeProfile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, EmployeeProfile $employeeProfile)
    {
        return $user->can('Delete employee profile');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeProfile  $employeeProfile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, EmployeeProfile $employeeProfile)
    {
        return $user->can('Delete employee profile');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeProfile  $employeeProfile
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, EmployeeProfile $employeeProfile)
    {
        return $user->can('Delete employee profile');
    }

    /**
     * Determine whether the user can view salary information.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmployeeProfile  $employeeProfile
     * @return bool
     */
    public function viewSalary(User $user, EmployeeProfile $employeeProfile)
    {
        return $user->can('Manage payroll') || 
               $user->hasRole('HR Manager');
    }
}
