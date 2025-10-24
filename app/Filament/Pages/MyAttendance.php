<?php

namespace App\Filament\Pages;

use App\Models\AttendanceRecord;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MyAttendance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static string $view = 'filament.pages.my-attendance';

    protected static ?string $navigationGroup = 'HR Management';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'My Attendance';

    protected static ?string $title = 'My Attendance';

    public $todayRecord;
    public $isCheckedIn = false;
    public $isOnBreak = false;
    public $checkInTime;
    public $currentTime;
    public $totalHours = 0;
    public $breakDuration = 0;
    public $workHours = 0;

    protected $listeners = ['refreshAttendance' => '$refresh'];

    public function mount()
    {
        $this->loadTodayRecord();
    }

    public function loadTodayRecord()
    {
        $this->todayRecord = AttendanceRecord::where('user_id', Auth::id())
            ->where('date', today())
            ->first();

        if ($this->todayRecord) {
            $this->isCheckedIn = (bool) $this->todayRecord->check_in;
            $this->isOnBreak = $this->todayRecord->isOnBreak();
            $this->checkInTime = $this->todayRecord->check_in?->format('H:i:s');
            $this->totalHours = $this->todayRecord->total_hours ?? 0;
            $this->breakDuration = $this->todayRecord->break_duration ?? 0;
            $this->workHours = $this->todayRecord->work_hours ?? 0;
        }
    }

    public function checkIn()
    {
        if (!$this->todayRecord) {
            $this->todayRecord = AttendanceRecord::create([
                'user_id' => Auth::id(),
                'date' => today(),
            ]);
        }

        $this->todayRecord->checkIn();
        $this->loadTodayRecord();

        $this->notify('success', 'Checked in successfully!');
    }

    public function checkOut()
    {
        if ($this->todayRecord) {
            $this->todayRecord->checkOut();
            $this->loadTodayRecord();

            $this->notify('success', 'Checked out successfully!');
        }
    }

    public function startBreak()
    {
        if ($this->todayRecord) {
            $this->todayRecord->startBreak();
            $this->loadTodayRecord();

            $this->notify('info', 'Break started');
        }
    }

    public function endBreak()
    {
        if ($this->todayRecord) {
            $this->todayRecord->endBreak();
            $this->loadTodayRecord();

            $this->notify('success', 'Break ended');
        }
    }

    protected function getViewData(): array
    {
        return [
            'recentRecords' => AttendanceRecord::where('user_id', Auth::id())
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get(),
        ];
    }
}
