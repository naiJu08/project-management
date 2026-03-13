<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_code',
        'department_id',
        'position_id',
        'manager_id',
        'hire_date',
        'employment_type',
        'status',
        'salary',
        'date_of_birth',
        'phone',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'date_of_birth' => 'date',
        'salary' => 'encrypted',
    ];

    protected $appends = [
        'tenure_years',
        'age',
    ];

    /**
     * Get the user associated with this employee profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department this employee belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position of this employee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the manager of this employee.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get employees who report to this employee.
     */
    public function directReports()
    {
        return $this->hasMany(EmployeeProfile::class, 'manager_id', 'user_id');
    }

    /**
     * Scope to get only active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get employees by department.
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to get employees by employment type.
     */
    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    /**
     * Get the employee's tenure in years.
     */
    public function tenureYears(): Attribute
    {
        return new Attribute(
            get: function () {
                if (!$this->hire_date) {
                    return null;
                }
                return $this->hire_date->diffInYears(now());
            }
        );
    }

    /**
     * Get the employee's age.
     */
    public function age(): Attribute
    {
        return new Attribute(
            get: function () {
                if (!$this->date_of_birth) {
                    return null;
                }
                return $this->date_of_birth->diffInYears(now());
            }
        );
    }

    /**
     * Get the full employee name with code.
     */
    public function getFullIdentifierAttribute(): string
    {
        return $this->employee_code . ' - ' . $this->user->name;
    }

    /**
     * Check if employee is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if employee can approve leave requests.
     */
    public function canApproveLeaves(): bool
    {
        return $this->directReports()->exists() || 
               $this->user->hasRole(['HR Manager', 'Department Manager']);
    }
}
