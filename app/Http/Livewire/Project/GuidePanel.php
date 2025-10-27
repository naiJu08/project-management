<?php

namespace App\Http\Livewire\Project;

use Livewire\Component;

class GuidePanel extends Component
{
    public $activeTab = 'board';
    public $showGuide = false;

    protected $guides = [
        'board' => [
            'title' => 'Board Guide',
            'description' => 'Manage your project tasks using the Kanban board',
            'steps' => [
                [
                    'number' => 1,
                    'title' => 'View Tasks',
                    'description' => 'See all tasks organized by status columns (To Do, In Progress, Done)',
                ],
                [
                    'number' => 2,
                    'title' => 'Drag & Drop',
                    'description' => 'Click and drag tasks between columns to update their status',
                ],
                [
                    'number' => 3,
                    'title' => 'Create Task',
                    'description' => 'Click the "+" button in any column to create a new task',
                ],
                [
                    'number' => 4,
                    'title' => 'Edit Task',
                    'description' => 'Click on a task card to view details and make edits',
                ],
            ],
            'tips' => [
                'Use filters to focus on specific team members or priorities',
                'Drag multiple tasks at once by selecting them first',
                'Set due dates to keep track of deadlines',
            ],
        ],
        'overview' => [
            'title' => 'Overview Guide',
            'description' => 'Get a quick snapshot of your project status',
            'steps' => [
                [
                    'number' => 1,
                    'title' => 'Project Stats',
                    'description' => 'View key metrics like total tickets, completion rate, and team size',
                ],
                [
                    'number' => 2,
                    'title' => 'Active Sprint',
                    'description' => 'See the current sprint status and progress',
                ],
                [
                    'number' => 3,
                    'title' => 'Team Members',
                    'description' => 'View all team members assigned to the project',
                ],
                [
                    'number' => 4,
                    'title' => 'Recent Activity',
                    'description' => 'Check the latest updates and changes',
                ],
            ],
            'tips' => [
                'Click on any metric to drill down into details',
                'Use the refresh button to get the latest data',
                'Export reports for stakeholder updates',
            ],
        ],
        'list' => [
            'title' => 'List View Guide',
            'description' => 'View all tickets in a detailed table format',
            'steps' => [
                [
                    'number' => 1,
                    'title' => 'Search & Filter',
                    'description' => 'Use the search bar and filters to find specific tickets',
                ],
                [
                    'number' => 2,
                    'title' => 'Sort Columns',
                    'description' => 'Click column headers to sort by status, priority, or assignee',
                ],
                [
                    'number' => 3,
                    'title' => 'Bulk Actions',
                    'description' => 'Select multiple tickets to perform batch operations',
                ],
                [
                    'number' => 4,
                    'title' => 'Export Data',
                    'description' => 'Export the list as CSV or PDF for reporting',
                ],
            ],
            'tips' => [
                'Use advanced filters for complex searches',
                'Save custom views for frequently used filters',
                'Keyboard shortcuts available for power users',
            ],
        ],
        'time-tracking' => [
            'title' => 'Time Tracking Guide',
            'description' => 'Log and manage project hours',
            'steps' => [
                [
                    'number' => 1,
                    'title' => 'Log Time',
                    'description' => 'Click "Log Time" button and enter hours, category, and date',
                ],
                [
                    'number' => 2,
                    'title' => 'Set Billable Status',
                    'description' => 'Mark hours as billable or non-billable for invoicing',
                ],
                [
                    'number' => 3,
                    'title' => 'Filter & Search',
                    'description' => 'Use filters to view hours by team member, category, or date range',
                ],
                [
                    'number' => 4,
                    'title' => 'Edit & Delete',
                    'description' => 'Modify or remove time entries as needed',
                ],
            ],
            'tips' => [
                'Log time daily for accurate tracking',
                'Use categories to organize work types',
                'Review billable hours for invoicing accuracy',
                'Export time reports for payroll',
            ],
        ],
        'reports' => [
            'title' => 'Reports & Analytics Guide',
            'description' => 'Analyze project performance and metrics',
            'steps' => [
                [
                    'number' => 1,
                    'title' => 'Select Report Type',
                    'description' => 'Choose from Overview, Tickets, Hours, Team, or Sprint reports',
                ],
                [
                    'number' => 2,
                    'title' => 'Set Date Range',
                    'description' => 'Filter data by 7 days, 30 days, 90 days, or 1 year',
                ],
                [
                    'number' => 3,
                    'title' => 'View Metrics',
                    'description' => 'Analyze charts, graphs, and key performance indicators',
                ],
                [
                    'number' => 4,
                    'title' => 'Export Report',
                    'description' => 'Download reports as PDF or Excel for presentations',
                ],
            ],
            'tips' => [
                'Use reports for stakeholder updates',
                'Track trends over time to identify patterns',
                'Compare metrics across different periods',
                'Share reports with team members',
            ],
        ],
        'milestones' => [
            'title' => 'Milestones & Releases Guide',
            'description' => 'Plan and track project milestones',
            'steps' => [
                [
                    'number' => 1,
                    'title' => 'Create Milestone',
                    'description' => 'Click "New Milestone" and enter name, date, and version',
                ],
                [
                    'number' => 2,
                    'title' => 'Add Description',
                    'description' => 'Include milestone goals and release notes',
                ],
                [
                    'number' => 3,
                    'title' => 'Track Progress',
                    'description' => 'View completion percentage and associated tickets',
                ],
                [
                    'number' => 4,
                    'title' => 'Update Status',
                    'description' => 'Change status from Planned to In Progress to Completed',
                ],
            ],
            'tips' => [
                'Set realistic target dates',
                'Link tickets to milestones for tracking',
                'Use release notes for documentation',
                'Monitor overdue milestones',
            ],
        ],
        'budget' => [
            'title' => 'Budget & Cost Management Guide',
            'description' => 'Manage project budget and expenses',
            'steps' => [
                [
                    'number' => 1,
                    'title' => 'Set Budget',
                    'description' => 'Click "Set Budget" and enter total project budget',
                ],
                [
                    'number' => 2,
                    'title' => 'Add Expenses',
                    'description' => 'Click "Add Expense" and categorize spending',
                ],
                [
                    'number' => 3,
                    'title' => 'Approve Expenses',
                    'description' => 'Review and approve pending expenses',
                ],
                [
                    'number' => 4,
                    'title' => 'Monitor Spending',
                    'description' => 'Track budget utilization and remaining funds',
                ],
            ],
            'tips' => [
                'Review budget regularly to avoid overspending',
                'Categorize expenses for better tracking',
                'Set up approval workflows for expenses',
                'Generate budget reports for stakeholders',
            ],
        ],
    ];

    public function mount($activeTab = 'board')
    {
        $this->activeTab = $activeTab;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleGuide()
    {
        $this->showGuide = !$this->showGuide;
    }

    public function getGuideProperty()
    {
        return $this->guides[$this->activeTab] ?? null;
    }

    public function render()
    {
        return view('livewire.project.guide-panel');
    }
}
