<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\Sprint;
use Carbon\Carbon;
use Livewire\Component;

class CalendarView extends Component
{
    public $projectId;
    public $currentMonth;
    public $currentYear;
    public $selectedDate = null;
    public $viewMode = 'month'; // month, week, day
    public $showEventForm = false;
    public $eventTitle = '';
    public $eventDate = '';
    public $eventDescription = '';
    public $eventType = 'ticket'; // ticket, sprint, milestone

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function previousPeriod()
    {
        if ($this->viewMode === 'month') {
            $this->previousMonth();
        } elseif ($this->viewMode === 'week') {
            $this->previousWeek();
        } elseif ($this->viewMode === 'day') {
            $this->previousDay();
        }
    }

    public function nextPeriod()
    {
        if ($this->viewMode === 'month') {
            $this->nextMonth();
        } elseif ($this->viewMode === 'week') {
            $this->nextWeek();
        } elseif ($this->viewMode === 'day') {
            $this->nextDay();
        }
    }

    public function previousMonth()
    {
        if ($this->currentMonth === 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }

    public function nextMonth()
    {
        if ($this->currentMonth === 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }

    public function previousWeek()
    {
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate);
            $date->subWeek();
            $this->selectedDate = $date->toDateString();
        }
    }

    public function nextWeek()
    {
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate);
            $date->addWeek();
            $this->selectedDate = $date->toDateString();
        }
    }

    public function previousDay()
    {
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate);
            $date->subDay();
            $this->selectedDate = $date->toDateString();
        }
    }

    public function nextDay()
    {
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate);
            $date->addDay();
            $this->selectedDate = $date->toDateString();
        }
    }

    public function goToToday()
    {
        if ($this->viewMode === 'month') {
            $this->currentMonth = now()->month;
            $this->currentYear = now()->year;
        } else {
            $this->selectedDate = now()->toDateString();
        }
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        // Auto-select today's date when switching to week or day view
        if (($mode === 'week' || $mode === 'day') && !$this->selectedDate) {
            $this->selectedDate = now()->toDateString();
        }
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        // Automatically switch to day view when a date is selected
        $this->viewMode = 'day';
    }

    public function getCalendarDaysProperty()
    {
        $firstDay = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startDate = $firstDay->copy()->startOfWeek();
        $endDate = $lastDay->copy()->endOfWeek();

        $days = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $days[] = $current->copy();
            $current->addDay();
        }

        return $days;
    }

    public function getEventsForDateProperty()
    {
        return function ($date) {
            $dateStr = $date->toDateString();
            $events = [];

            // Get tickets that are relevant to this specific date
            $tickets = Ticket::where('project_id', $this->projectId)
                ->where(function ($query) use ($dateStr) {
                    // Show tickets with start date on this day
                    $query->whereDate('start_date', $dateStr)
                          // Or tickets with due date on this day
                          ->orWhereDate('due_date', $dateStr)
                          // Or tickets created on this day (as fallback)
                          ->orWhereDate('created_at', $dateStr);
                })
                ->get();
            
            // Only add events for tickets found for this specific date
            foreach ($tickets as $ticket) {
                $events[] = [
                    'id' => 'ticket-' . $ticket->id,
                    'title' => $ticket->name ?? 'Untitled Ticket',
                    'type' => 'ticket',
                    'color' => 'blue',
                    'data' => $ticket
                ];
            }

            
            // Get sprints
            $sprints = Sprint::where('project_id', $this->projectId)
                ->where(function ($query) use ($dateStr) {
                    $query->whereDate('starts_at', '<=', $dateStr)
                        ->whereDate('ends_at', '>=', $dateStr);
                })
                ->get();

            foreach ($sprints as $sprint) {
                $events[] = [
                    'id' => 'sprint-' . $sprint->id,
                    'title' => $sprint->name,
                    'type' => 'sprint',
                    'color' => 'purple',
                    'data' => $sprint
                ];
            }

            return $events;
        };
    }

    public function getWeekDaysProperty()
    {
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate);
            $startOfWeek = $date->copy()->startOfWeek();
            $days = [];

            for ($i = 0; $i < 7; $i++) {
                $days[] = $startOfWeek->copy()->addDays($i);
            }

            return $days;
        }

        return [];
    }

    public function render()
    {
        return view('livewire.project.calendar-view', [
            'calendarDays' => $this->getCalendarDaysProperty(),
            'getEventsForDate' => $this->getEventsForDateProperty(),
            'weekDays' => $this->getWeekDaysProperty(),
        ]);
    }
}
