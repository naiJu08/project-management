<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        \App\Models\Department::class => \App\Policies\DepartmentPolicy::class,
        \App\Models\Position::class => \App\Policies\PositionPolicy::class,
        \App\Models\EmployeeProfile::class => \App\Policies\EmployeeProfilePolicy::class,
        \App\Models\LeaveType::class => \App\Policies\LeaveTypePolicy::class,
        \App\Models\LeaveRequest::class => \App\Policies\LeaveRequestPolicy::class,
        \App\Models\AttendanceRecord::class => \App\Policies\AttendanceRecordPolicy::class,
        \App\Models\WikiPage::class => \App\Policies\WikiPagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
