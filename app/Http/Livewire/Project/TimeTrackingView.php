<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Ticket;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TimeTrackingView extends Component
{
    public $projectId;
    public $timeLogs = [];
    public $showForm = false;
    public $editingLogId = null;

    // Form fields
    public $ticketId = null;
    public $hours = '';
    public $description = '';
    public $isBillable = true;
    public $loggedDate = '';
    public $category = 'development';

    // Filters
    public $filterUser = 'all';
    public $filterCategory = 'all';
    public $filterBillable = 'all';
    public $startDate = '';
    public $endDate = '';

    protected $rules = [
        'ticketId' => 'nullable|exists:tickets,id',
        'hours' => 'required|numeric|min:0.25|max:24',
        'description' => 'nullable|string|max:1000',
        'isBillable' => 'boolean',
        'loggedDate' => 'required|date',
        'category' => 'required|in:development,testing,documentation,meeting,other',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->loggedDate = now()->toDateString();
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->loadTimeLogs();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function loadTimeLogs()
    {
        $query = TimeLog::forProject($this->projectId)
            ->with(['user', 'ticket'])
            ->orderBy('logged_date', 'desc');

        if ($this->filterUser !== 'all') {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->filterCategory !== 'all') {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterBillable !== 'all') {
            $query->where('is_billable', $this->filterBillable === 'billable');
        }

        if ($this->startDate && $this->endDate) {
            $query->dateRange($this->startDate, $this->endDate);
        }

        $this->timeLogs = $query->get();
    }

    public function logTime()
    {
        $this->validate();

        TimeLog::create([
            'project_id' => $this->projectId,
            'ticket_id' => $this->ticketId,
            'user_id' => Auth::id(),
            'hours' => $this->hours,
            'description' => $this->description,
            'is_billable' => $this->isBillable,
            'logged_date' => $this->loggedDate,
            'category' => $this->category,
        ]);

        $this->resetForm();
        $this->loadTimeLogs();
        session()->flash('success', 'Time logged successfully!');
    }

    public function editLog($logId)
    {
        $log = TimeLog::find($logId);
        if ($log->user_id !== Auth::id() && !auth()->user()->can('manage_time_logs')) {
            session()->flash('error', 'You can only edit your own time logs');
            return;
        }

        $this->editingLogId = $logId;
        $this->ticketId = $log->ticket_id;
        $this->hours = $log->hours;
        $this->description = $log->description;
        $this->isBillable = $log->is_billable;
        $this->loggedDate = $log->logged_date->toDateString();
        $this->category = $log->category;
        $this->showForm = true;
    }

    public function updateLog()
    {
        $this->validate();

        $log = TimeLog::find($this->editingLogId);
        $log->update([
            'ticket_id' => $this->ticketId,
            'hours' => $this->hours,
            'description' => $this->description,
            'is_billable' => $this->isBillable,
            'logged_date' => $this->loggedDate,
            'category' => $this->category,
        ]);

        $this->resetForm();
        $this->loadTimeLogs();
        session()->flash('success', 'Time log updated successfully!');
    }

    public function deleteLog($logId)
    {
        $log = TimeLog::find($logId);
        if ($log->user_id !== Auth::id() && !auth()->user()->can('manage_time_logs')) {
            session()->flash('error', 'You can only delete your own time logs');
            return;
        }

        $log->delete();
        $this->loadTimeLogs();
        session()->flash('success', 'Time log deleted successfully!');
    }

    public function resetForm()
    {
        $this->ticketId = null;
        $this->hours = '';
        $this->description = '';
        $this->isBillable = true;
        $this->loggedDate = now()->toDateString();
        $this->category = 'development';
        $this->showForm = false;
        $this->editingLogId = null;
    }

    public function getTotalHoursProperty()
    {
        return $this->timeLogs->sum('hours');
    }

    public function getBillableHoursProperty()
    {
        return $this->timeLogs->where('is_billable', true)->sum('hours');
    }

    public function getNonBillableHoursProperty()
    {
        return $this->timeLogs->where('is_billable', false)->sum('hours');
    }

    public function render()
    {
        return view('livewire.project.time-tracking-view', [
            'tickets' => $this->project->tickets,
            'teamMembers' => $this->project->users,
        ]);
    }
}
