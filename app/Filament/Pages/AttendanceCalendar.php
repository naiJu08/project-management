<?php

namespace App\Filament\Pages;

use App\Models\AttendanceRecord;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class AttendanceCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.attendance-calendar';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Attendance Calendar';

    protected static ?string $title = 'Attendance Calendar';

    public $selectedMonth;
    public $selectedYear;
    public $selectedUserId;
    public $calendarData = [];
    public $users = [];

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->users = User::orderBy('name')->get();
        $this->loadCalendarData();
    }

    public function loadCalendarData()
    {
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = AttendanceRecord::whereBetween('date', [$startDate, $endDate])
            ->with('user');

        if ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        }

        $records = $query->get()->groupBy(function($record) {
            return $record->date->format('Y-m-d');
        });

        $this->calendarData = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $this->calendarData[$dateKey] = [
                'date' => $currentDate->copy(),
                'records' => $records->get($dateKey, collect()),
            ];
            $currentDate->addDay();
        }
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->loadCalendarData();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->loadCalendarData();
    }

    public function updatedSelectedUserId()
    {
        $this->loadCalendarData();
    }

    public function getMonthName()
    {
        return Carbon::create($this->selectedYear, $this->selectedMonth, 1)->format('F Y');
    }

    public function getStats()
    {
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = AttendanceRecord::whereBetween('date', [$startDate, $endDate]);

        if ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        }

        $records = $query->get();

        return [
            'total_days' => $records->count(),
            'present' => $records->whereIn('status', ['present', 'late'])->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'on_leave' => $records->where('status', 'on-leave')->count(),
            'total_hours' => round($records->sum('work_hours'), 1),
            'avg_hours' => $records->whereIn('status', ['present', 'late'])->count() > 0 
                ? round($records->sum('work_hours') / $records->whereIn('status', ['present', 'late'])->count(), 1) 
                : 0,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('Manage attendance') || 
               auth()->user()->can('View all employees');
    }
}
