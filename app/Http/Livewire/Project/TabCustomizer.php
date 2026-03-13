<?php

namespace App\Http\Livewire\Project;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TabCustomizer extends Component
{
    public $projectId;
    public $showCustomizer = false;
    public $enabledTabs = [];
    public $allTabs = [];
    public $tabOrder = [];

    protected $availableTabs = [
        'board' => ['name' => 'Board', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
        'overview' => ['name' => 'Overview', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'list' => ['name' => 'List', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
        'backlog' => ['name' => 'Backlog', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
        'sprint' => ['name' => 'Sprints', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        'dashboard' => ['name' => 'Dashboard', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        'calendar' => ['name' => 'Calendar', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'wiki' => ['name' => 'Wiki', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        'gantt' => ['name' => 'Gantt', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        'chat' => ['name' => 'Chat', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        'time-tracking' => ['name' => 'Time Tracking', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'reports' => ['name' => 'Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        'milestones' => ['name' => 'Milestones', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'budget' => ['name' => 'Budget', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->allTabs = $this->availableTabs;
        $this->loadTabPreferences();
    }

    public function loadTabPreferences()
    {
        $userId = Auth::id();
        $cacheKey = "project_tabs_{$this->projectId}_user_{$userId}";
        
        $preferences = cache($cacheKey, [
            'enabled' => ['board', 'overview', 'list', 'backlog', 'sprint', 'dashboard', 'calendar', 'wiki', 'gantt', 'chat', 'time-tracking', 'reports', 'milestones', 'budget'],
            'order' => ['board', 'overview', 'list', 'backlog', 'sprint', 'dashboard', 'calendar', 'wiki', 'gantt', 'chat', 'time-tracking', 'reports', 'milestones', 'budget'],
        ]);

        $this->enabledTabs = $preferences['enabled'];
        $this->tabOrder = $preferences['order'];
    }

    public function toggleTab($tabKey)
    {
        if (in_array($tabKey, $this->enabledTabs)) {
            $this->enabledTabs = array_filter($this->enabledTabs, fn($tab) => $tab !== $tabKey);
        } else {
            $this->enabledTabs[] = $tabKey;
        }
    }

    public function moveTabUp($tabKey)
    {
        $index = array_search($tabKey, $this->tabOrder);
        if ($index > 0) {
            $temp = $this->tabOrder[$index];
            $this->tabOrder[$index] = $this->tabOrder[$index - 1];
            $this->tabOrder[$index - 1] = $temp;
        }
    }

    public function moveTabDown($tabKey)
    {
        $index = array_search($tabKey, $this->tabOrder);
        if ($index < count($this->tabOrder) - 1) {
            $temp = $this->tabOrder[$index];
            $this->tabOrder[$index] = $this->tabOrder[$index + 1];
            $this->tabOrder[$index + 1] = $temp;
        }
    }

    public function savePreferences()
    {
        $userId = Auth::id();
        $cacheKey = "project_tabs_{$this->projectId}_user_{$userId}";
        
        cache([
            $cacheKey => [
                'enabled' => $this->enabledTabs,
                'order' => $this->tabOrder,
            ]
        ], now()->addYears(1));

        $this->showCustomizer = false;
        session()->flash('success', 'Tab preferences saved successfully!');
        
        // Emit event to reload tab preferences in parent component
        $this->emit('tabPreferencesUpdated');
    }

    public function resetToDefaults()
    {
        $this->enabledTabs = array_keys($this->availableTabs);
        $this->tabOrder = array_keys($this->availableTabs);
        $this->savePreferences();
    }

    public function render()
    {
        return view('livewire.project.tab-customizer');
    }
}
