<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Ticket;
use Carbon\Carbon;
use Livewire\Component;

class GanttView extends Component
{
    public $projectId;
    public $startDate;
    public $endDate;
    public $zoomLevel = 'month'; // day, week, month
    public $showTickets = true;
    public $showSprints = true;
    public $selectedSprint = null;

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->initializeDates();
    }

    public function initializeDates()
    {
        $sprints = $this->project->sprints()->orderBy('starts_at')->get();
        
        if ($sprints->count() > 0) {
            $this->startDate = $sprints->first()->starts_at->copy()->subMonth();
            $this->endDate = $sprints->last()->ends_at->copy()->addMonth();
        } else {
            $this->startDate = now()->startOfMonth();
            $this->endDate = now()->addMonths(3)->endOfMonth();
        }
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function getSprintsProperty()
    {
        return $this->project->sprints()
            ->with(['backlogItems'])
            ->orderBy('starts_at')
            ->get();
    }

    public function getTicketsProperty()
    {
        return $this->project->tickets()
            ->with(['status', 'priority', 'responsible'])
            ->orderBy('created_at')
            ->get();
    }

    public function getGanttChartDataProperty()
    {
        $data = [];

        // Add sprints
        if ($this->showSprints) {
            foreach ($this->sprints as $sprint) {
                $data[] = [
                    'id' => 'sprint-' . $sprint->id,
                    'name' => $sprint->name,
                    'type' => 'sprint',
                    'startDate' => $sprint->starts_at,
                    'endDate' => $sprint->ends_at,
                    'progress' => $this->calculateSprintProgress($sprint),
                    'status' => $sprint->status,
                    'color' => 'purple',
                    'data' => $sprint,
                ];
            }
        }

        // Add tickets
        if ($this->showTickets) {
            foreach ($this->tickets as $ticket) {
                // Use created_at to updated_at as timeline
                $data[] = [
                    'id' => 'ticket-' . $ticket->id,
                    'name' => $ticket->title,
                    'type' => 'ticket',
                    'startDate' => $ticket->created_at,
                    'endDate' => $ticket->updated_at ?? $ticket->created_at,
                    'progress' => $this->getTicketProgress($ticket),
                    'status' => $ticket->status->name ?? 'Pending',
                    'color' => $this->getTicketColor($ticket),
                    'data' => $ticket,
                ];
            }
        }

        return collect($data)->sortBy('startDate')->values();
    }

    public function calculateSprintProgress($sprint)
    {
        $items = $sprint->backlogItems;
        if ($items->count() === 0) {
            return 0;
        }
        $completed = $items->where('status', 'Done')->count();
        return round(($completed / $items->count()) * 100);
    }

    public function getTicketProgress($ticket)
    {
        if ($ticket->status->name === 'Done' || $ticket->status->name === 'Archived') {
            return 100;
        } elseif ($ticket->status->name === 'In Progress') {
            return 50;
        }
        return 0;
    }

    public function getTicketColor($ticket)
    {
        return match($ticket->priority->name ?? 'Medium') {
            'Critical' => 'red',
            'High' => 'orange',
            'Medium' => 'yellow',
            'Low' => 'green',
            default => 'blue',
        };
    }

    public function setZoomLevel($level)
    {
        $this->zoomLevel = $level;
    }

    public function toggleTickets()
    {
        $this->showTickets = !$this->showTickets;
    }

    public function toggleSprints()
    {
        $this->showSprints = !$this->showSprints;
    }

    public function zoomIn()
    {
        if ($this->zoomLevel === 'month') {
            $this->zoomLevel = 'week';
        } elseif ($this->zoomLevel === 'week') {
            $this->zoomLevel = 'day';
        }
    }

    public function zoomOut()
    {
        if ($this->zoomLevel === 'day') {
            $this->zoomLevel = 'week';
        } elseif ($this->zoomLevel === 'week') {
            $this->zoomLevel = 'month';
        }
    }

    public function getDateRangeProperty()
    {
        $current = $this->startDate->copy();
        $dates = [];

        while ($current <= $this->endDate) {
            $dates[] = $current->copy();
            
            if ($this->zoomLevel === 'day') {
                $current->addDay();
            } elseif ($this->zoomLevel === 'week') {
                $current->addWeek();
            } else {
                $current->addMonth();
            }
        }

        return $dates;
    }

    public function getBarPositionProperty()
    {
        return function ($item) {
            $totalDays = $this->startDate->diffInDays($this->endDate);
            $startOffset = $this->startDate->diffInDays($item['startDate']);
            $duration = $item['startDate']->diffInDays($item['endDate']) + 1;

            $startPercent = ($startOffset / $totalDays) * 100;
            $widthPercent = ($duration / $totalDays) * 100;

            return [
                'left' => max(0, $startPercent),
                'width' => max(2, $widthPercent),
            ];
        };
    }

    public function render()
    {
        return view('livewire.project.gantt-view', [
            'ganttData' => $this->getGanttChartDataProperty(),
            'dateRange' => $this->getDateRangeProperty(),
            'getBarPosition' => $this->getBarPositionProperty(),
        ]);
    }
}
