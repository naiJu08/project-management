<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceRecordPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('List attendance records') || 
               $user->can('Manage attendance');
    }

    public function view(User $user, AttendanceRecord $attendanceRecord)
    {
        // Can view own records, or has permission
        return $user->id === $attendanceRecord->user_id ||
               $user->can('View attendance record') ||
               $user->can('Manage attendance');
    }

    public function create(User $user)
    {
        // Anyone can create their own attendance record
        return true;
    }

    public function update(User $user, AttendanceRecord $attendanceRecord)
    {
        // Can update own records for today, or has permission
        if ($user->id === $attendanceRecord->user_id && $attendanceRecord->date->isToday()) {
            return true;
        }

        return $user->can('Update attendance record') || 
               $user->can('Manage attendance');
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord)
    {
        return $user->can('Delete attendance record') || 
               $user->can('Manage attendance');
    }

    public function checkIn(User $user)
    {
        // Anyone can check in
        return true;
    }

    public function checkOut(User $user)
    {
        // Anyone can check out
        return true;
    }
}
