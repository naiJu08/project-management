<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LeaveManagementSeeder extends Seeder
{
    private array $leaveModules = [
        'leave type', 'leave request', 'attendance record'
    ];

    private array $pluralActions = ['List'];
    private array $singularActions = ['View', 'Create', 'Update', 'Delete'];

    private array $extraPermissions = [
        'Approve leave requests',
        'Manage attendance',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create Leave & Attendance Permissions
        $this->createPermissions();

        // 2. Update HR Roles with new permissions
        $this->updateHRRoles();

        // 3. Seed Standard Leave Types
        $this->seedLeaveTypes();

        // 4. Grant permissions to first user
        $this->grantPermissionsToFirstUser();
    }

    private function createPermissions()
    {
        $this->command->info('Creating Leave & Attendance permissions...');

        foreach ($this->leaveModules as $module) {
            $plural = Str::plural($module);
            $singular = $module;

            foreach ($this->pluralActions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . ' ' . $plural
                ]);
            }

            foreach ($this->singularActions as $action) {
                Permission::firstOrCreate([
                    'name' => $action . ' ' . $singular
                ]);
            }
        }

        foreach ($this->extraPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission
            ]);
        }

        $this->command->info('Leave & Attendance permissions created successfully!');
    }

    private function updateHRRoles()
    {
        $this->command->info('Updating HR roles with new permissions...');

        // HR Manager - Full access
        $hrManager = Role::where('name', 'HR Manager')->first();
        if ($hrManager) {
            $permissions = Permission::where(function ($query) {
                $query->where('name', 'like', '%leave%')
                      ->orWhere('name', 'like', '%attendance%');
            })->pluck('name')->toArray();
            $hrManager->givePermissionTo($permissions);
        }

        // HR Staff - Limited access
        $hrStaff = Role::where('name', 'HR Staff')->first();
        if ($hrStaff) {
            $hrStaff->givePermissionTo([
                'List leave types', 'View leave type',
                'List leave requests', 'View leave request',
                'List attendance records', 'View attendance record',
                'Manage attendance',
            ]);
        }

        // Department Manager - Team approval
        $deptManager = Role::where('name', 'Department Manager')->first();
        if ($deptManager) {
            $deptManager->givePermissionTo([
                'List leave requests', 'View leave request',
                'Approve leave requests',
                'List attendance records', 'View attendance record',
            ]);
        }

        $this->command->info('HR roles updated successfully!');
    }

    private function seedLeaveTypes()
    {
        $this->command->info('Seeding standard leave types...');

        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'description' => 'Paid annual vacation leave',
                'days_per_year' => 20,
                'is_paid' => true,
                'requires_approval' => true,
                'color' => '#10b981',
                'icon' => 'heroicon-o-sun',
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'description' => 'Paid sick leave for medical reasons',
                'days_per_year' => 10,
                'is_paid' => true,
                'requires_approval' => false,
                'color' => '#ef4444',
                'icon' => 'heroicon-o-heart',
            ],
            [
                'name' => 'Personal Leave',
                'code' => 'PL',
                'description' => 'Personal time off',
                'days_per_year' => 5,
                'is_paid' => true,
                'requires_approval' => true,
                'color' => '#3b82f6',
                'icon' => 'heroicon-o-user',
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'description' => 'Unpaid time off',
                'days_per_year' => 0,
                'is_paid' => false,
                'requires_approval' => true,
                'color' => '#6b7280',
                'icon' => 'heroicon-o-ban',
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'description' => 'Maternity leave for new mothers',
                'days_per_year' => 90,
                'is_paid' => true,
                'requires_approval' => true,
                'color' => '#ec4899',
                'icon' => 'heroicon-o-heart',
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'PTL',
                'description' => 'Paternity leave for new fathers',
                'days_per_year' => 10,
                'is_paid' => true,
                'requires_approval' => true,
                'color' => '#8b5cf6',
                'icon' => 'heroicon-o-heart',
            ],
            [
                'name' => 'Bereavement Leave',
                'code' => 'BL',
                'description' => 'Leave for family bereavement',
                'days_per_year' => 5,
                'is_paid' => true,
                'requires_approval' => false,
                'color' => '#000000',
                'icon' => 'heroicon-o-heart',
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::firstOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }

        $this->command->info('Standard leave types seeded successfully!');
    }

    private function grantPermissionsToFirstUser()
    {
        $this->command->info('Granting permissions to first user...');

        $user = User::first();
        if ($user) {
            $permissions = [
                'List leave types', 'View leave type', 'Create leave type', 'Update leave type', 'Delete leave type',
                'List leave requests', 'View leave request', 'Create leave request', 'Update leave request', 'Delete leave request',
                'List attendance records', 'View attendance record', 'Create attendance record', 'Update attendance record', 'Delete attendance record',
                'Approve leave requests',
                'Manage attendance',
            ];

            $user->givePermissionTo($permissions);
            $this->command->info('Permissions granted to ' . $user->name);
        }
    }
}
