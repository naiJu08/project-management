<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Filament\Widgets\Project\ProjectStatsWidget;
use App\Filament\Widgets\Project\ProjectTicketsByStatusWidget;
use App\Filament\Widgets\Project\ProjectTicketsByPriorityWidget;
use App\Filament\Widgets\Project\ProjectLatestTicketsWidget;
use App\Filament\Widgets\Project\ProjectTeamWidget;
use App\Filament\Widgets\Project\ProjectTicketsTypeChartWidget;
use App\Filament\Widgets\Project\ProjectPriorityDistributionWidget;
use App\Filament\Widgets\Project\ProjectHealthScoreWidget;
use App\Filament\Widgets\Project\ProjectAIInsightsWidget;
use Livewire\Component;

class DashboardView extends Component
{
    public $projectId;

    public function mount($projectId)
    {
        $this->projectId = $projectId;
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function getWidgets(): array
    {
        return [
            ProjectStatsWidget::class,
            ProjectHealthScoreWidget::class,
            ProjectTicketsByStatusWidget::class,
            ProjectTicketsByPriorityWidget::class,
            ProjectTicketsTypeChartWidget::class,
            ProjectPriorityDistributionWidget::class,
            ProjectAIInsightsWidget::class,
            ProjectLatestTicketsWidget::class,
            ProjectTeamWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 6;
    }

    public function render()
    {
        return view('livewire.project.dashboard-view');
    }
}
