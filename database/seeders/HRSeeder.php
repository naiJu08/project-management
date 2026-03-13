<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HRSeeder extends Seeder
{
    private array $hrModules = [
        'department', 'position', 'employee profile'
    ];

    private array $pluralActions = ['List'];
    private array $singularActions = ['View', 'Create', 'Update', 'Delete'];

    private array $extraHRPermissions = [
        'View all employees',
        'Manage payroll',
        'Approve leave requests',
        'Manage attendance',
        'Conduct performance reviews',
        'Export HR reports',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create HR Permissions
        $this->createHRPermissions();

        // 2. Create HR Roles
        $this->createHRRoles();

        // 3. Seed Sample Departments
        $this->seedDepartments();

        // 4. Seed Sample Positions
        $this->seedPositions();

        // 5. Create sample employee profile for first user
        $this->createSampleEmployeeProfile();
    }

    private function createHRPermissions()
    {
        $this->command->info('Creating HR permissions...');

        foreach ($this->hrModules as $module) {
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

        foreach ($this->extraHRPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission
            ]);
        }

        $this->command->info('HR permissions created successfully!');
    }

    private function createHRRoles()
    {
        $this->command->info('Creating HR roles...');

        // HR Manager Role - Full HR access
        $hrManager = Role::firstOrCreate(['name' => 'HR Manager']);
        $hrManagerPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', '%department%')
                  ->orWhere('name', 'like', '%position%')
                  ->orWhere('name', 'like', '%employee%')
                  ->orWhere('name', 'like', '%payroll%')
                  ->orWhere('name', 'like', '%leave%')
                  ->orWhere('name', 'like', '%attendance%')
                  ->orWhere('name', 'like', '%performance%')
                  ->orWhere('name', 'like', '%HR%');
        })->pluck('name')->toArray();
        $hrManager->syncPermissions($hrManagerPermissions);

        // HR Staff Role - Limited HR access
        $hrStaff = Role::firstOrCreate(['name' => 'HR Staff']);
        $hrStaffPermissions = [
            'List departments', 'View department',
            'List positions', 'View position',
            'List employee profiles', 'View employee profile', 'Create employee profile', 'Update employee profile',
            'View all employees',
            'Manage attendance',
        ];
        $hrStaff->syncPermissions($hrStaffPermissions);

        // Department Manager Role
        $deptManager = Role::firstOrCreate(['name' => 'Department Manager']);
        $deptManagerPermissions = [
            'List departments', 'View department', 'Update department',
            'List positions', 'View position',
            'List employee profiles', 'View employee profile',
            'Approve leave requests',
            'Conduct performance reviews',
        ];
        $deptManager->syncPermissions($deptManagerPermissions);

        $this->command->info('HR roles created successfully!');
    }

    private function seedDepartments()
    {
        $this->command->info('Seeding sample departments...');

        $departments = [
            ['name' => 'Engineering', 'description' => 'Software development and technical operations'],
            ['name' => 'Human Resources', 'description' => 'Employee management and recruitment'],
            ['name' => 'Sales', 'description' => 'Sales and business development'],
            ['name' => 'Marketing', 'description' => 'Marketing and brand management'],
            ['name' => 'Finance', 'description' => 'Financial planning and accounting'],
            ['name' => 'Operations', 'description' => 'Business operations and logistics'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }

        // Create sub-departments for Engineering
        $engineering = Department::where('name', 'Engineering')->first();
        if ($engineering) {
            Department::firstOrCreate(
                ['name' => 'Backend Development'],
                [
                    'description' => 'Server-side development',
                    'parent_id' => $engineering->id,
                ]
            );

            Department::firstOrCreate(
                ['name' => 'Frontend Development'],
                [
                    'description' => 'Client-side development',
                    'parent_id' => $engineering->id,
                ]
            );

            Department::firstOrCreate(
                ['name' => 'DevOps'],
                [
                    'description' => 'Infrastructure and deployment',
                    'parent_id' => $engineering->id,
                ]
            );
        }

        $this->command->info('Sample departments seeded successfully!');
    }

    private function seedPositions()
    {
        $this->command->info('Seeding sample positions...');

        $engineering = Department::where('name', 'Engineering')->first();
        $hr = Department::where('name', 'Human Resources')->first();
        $sales = Department::where('name', 'Sales')->first();

        $positions = [
            // Engineering positions
            ['title' => 'Software Engineer', 'level' => 'Junior', 'department_id' => $engineering?->id],
            ['title' => 'Software Engineer', 'level' => 'Mid', 'department_id' => $engineering?->id],
            ['title' => 'Software Engineer', 'level' => 'Senior', 'department_id' => $engineering?->id],
            ['title' => 'Tech Lead', 'level' => 'Lead', 'department_id' => $engineering?->id],
            ['title' => 'Engineering Manager', 'level' => 'Manager', 'department_id' => $engineering?->id],

            // HR positions
            ['title' => 'HR Specialist', 'level' => 'Mid', 'department_id' => $hr?->id],
            ['title' => 'HR Manager', 'level' => 'Manager', 'department_id' => $hr?->id],
            ['title' => 'Recruiter', 'level' => 'Mid', 'department_id' => $hr?->id],

            // Sales positions
            ['title' => 'Sales Representative', 'level' => 'Junior', 'department_id' => $sales?->id],
            ['title' => 'Account Executive', 'level' => 'Mid', 'department_id' => $sales?->id],
            ['title' => 'Sales Manager', 'level' => 'Manager', 'department_id' => $sales?->id],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(
                [
                    'title' => $position['title'],
                    'level' => $position['level'],
                    'department_id' => $position['department_id']
                ],
                $position
            );
        }

        $this->command->info('Sample positions seeded successfully!');
    }

    private function createSampleEmployeeProfile()
    {
        $this->command->info('Creating sample employee profile...');

        $firstUser = User::first();
        if ($firstUser && !$firstUser->employeeProfile) {
            $engineering = Department::where('name', 'Engineering')->first();
            $position = Position::where('title', 'Software Engineer')
                                ->where('level', 'Senior')
                                ->first();

            EmployeeProfile::create([
                'user_id' => $firstUser->id,
                'employee_code' => 'EMP-001',
                'department_id' => $engineering?->id,
                'position_id' => $position?->id,
                'hire_date' => now()->subYears(2),
                'employment_type' => 'full-time',
                'status' => 'active',
            ]);

            $this->command->info('Sample employee profile created for first user!');
        }
    }
}
