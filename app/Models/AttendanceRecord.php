<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'total_hours',
        'breaks',
        'break_duration',
        'work_hours',
        'status',
        'location',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'breaks' => 'array',
        'break_duration' => 'decimal:2',
        'work_hours' => 'decimal:2',
    ];

    protected $appends = [
        'is_late',
        'is_present',
        'formatted_hours',
    ];

    /**
     * Get the user for this attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the employee was late.
     */
    public function isLate(): Attribute
    {
        return new Attribute(
            get: fn () => $this->status === 'late'
        );
    }

    /**
     * Check if the employee was present.
     */
    public function isPresent(): Attribute
    {
        return new Attribute(
            get: fn () => in_array($this->status, ['present', 'late'])
        );
    }

    /**
     * Get formatted hours (e.g., "8.5 hrs").
     */
    public function formattedHours(): Attribute
    {
        return new Attribute(
            get: function () {
                if (!$this->total_hours) {
                    return '—';
                }
                return number_format($this->total_hours, 1) . ' hrs';
            }
        );
    }

    /**
     * Calculate total hours between check-in and check-out.
     */
    public function calculateTotalHours()
    {
        if ($this->check_in && $this->check_out) {
            $this->total_hours = $this->check_in->diffInHours($this->check_out, true);
            $this->calculateWorkHours();
            $this->save();
        }
    }

    /**
     * Calculate work hours (total hours - break duration).
     */
    public function calculateWorkHours()
    {
        $this->work_hours = $this->total_hours - $this->break_duration;
    }

    /**
     * Start a break.
     */
    public function startBreak()
    {
        $breaks = $this->breaks ?? [];
        $breaks[] = [
            'start' => now()->toDateTimeString(),
            'end' => null,
        ];
        $this->breaks = $breaks;
        $this->save();
    }

    /**
     * End the current break.
     */
    public function endBreak()
    {
        $breaks = $this->breaks ?? [];
        
        // Find the last break without an end time
        for ($i = count($breaks) - 1; $i >= 0; $i--) {
            if ($breaks[$i]['end'] === null) {
                $breaks[$i]['end'] = now()->toDateTimeString();
                break;
            }
        }
        
        $this->breaks = $breaks;
        $this->calculateBreakDuration();
        $this->calculateWorkHours();
        $this->save();
    }

    /**
     * Calculate total break duration.
     */
    public function calculateBreakDuration()
    {
        $totalMinutes = 0;
        
        foreach ($this->breaks ?? [] as $break) {
            if ($break['start'] && $break['end']) {
                $start = \Carbon\Carbon::parse($break['start']);
                $end = \Carbon\Carbon::parse($break['end']);
                $totalMinutes += $start->diffInMinutes($end);
            }
        }
        
        $this->break_duration = round($totalMinutes / 60, 2);
    }

    /**
     * Check if currently on break.
     */
    public function isOnBreak(): bool
    {
        if (!$this->breaks) {
            return false;
        }
        
        foreach ($this->breaks as $break) {
            if ($break['start'] && $break['end'] === null) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check in the employee.
     */
    public function checkIn($time = null, $location = null)
    {
        $this->check_in = $time ?? now();
        $this->location = $location;
        
        // Determine if late (after 9:30 AM)
        $standardTime = $this->date->copy()->setTime(9, 30);
        if ($this->check_in->gt($standardTime)) {
            $this->status = 'late';
        } else {
            $this->status = 'present';
        }
        
        $this->save();
    }

    /**
     * Check out the employee.
     */
    public function checkOut($time = null)
    {
        $this->check_out = $time ?? now();
        $this->calculateTotalHours();
        
        // Determine if half-day (less than 4 hours)
        if ($this->total_hours < 4) {
            $this->status = 'half-day';
        }
        
        $this->save();
    }

    /**
     * Scope to get records for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope to get records for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get records within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get present records.
     */
    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'late']);
    }

    /**
     * Scope to get absent records.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }
}
