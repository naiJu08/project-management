<?php

namespace App\Services\AI;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Models\BacklogItem;
use App\Models\Sprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnhancedManagementActionHandler
{
    /**
     * Create a new project
     */
    public function createProject(array $parameters): array
    {
        $required = ['name'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $project = Project::create([
            'name' => $parameters['name'],
            'description' => $parameters['description'] ?? null,
            'owner_id' => Auth::id(),
            'status' => $parameters['status'] ?? 'active',
            'start_date' => $parameters['start_date'] ?? now(),
            'end_date' => $parameters['end_date'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => "✅ Project '{$project->name}' has been created successfully!",
            'data' => $project,
            'redirect' => "/admin/projects/{$project->id}",
        ];
    }

    /**
     * Update project
     */
    public function updateProject(array $parameters): array
    {
        $required = ['project_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $project = Project::find($parameters['project_id']);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found.'];
        }

        $updateData = array_filter([
            'name' => $parameters['name'] ?? null,
            'description' => $parameters['description'] ?? null,
            'status' => $parameters['status'] ?? null,
            'end_date' => $parameters['end_date'] ?? null,
        ]);

        $project->update($updateData);

        return [
            'success' => true,
            'message' => "✅ Project '{$project->name}' updated successfully!",
            'data' => $project,
        ];
    }

    /**
     * Archive project
     */
    public function archiveProject(array $parameters): array
    {
        $required = ['project_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $project = Project::find($parameters['project_id']);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found.'];
        }

        $project->update(['status' => 'archived']);

        return [
            'success' => true,
            'message' => "✅ Project '{$project->name}' has been archived.",
            'data' => $project,
        ];
    }

    /**
     * Create a new ticket
     */
    public function createTicket(array $parameters): array
    {
        $required = ['title', 'project_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $ticket = Ticket::create([
            'title' => $parameters['title'],
            'description' => $parameters['description'] ?? null,
            'project_id' => $parameters['project_id'],
            'owner_id' => Auth::id(),
            'responsible_id' => $this->findUserId($parameters['assigned_to'] ?? null),
            'type_id' => $parameters['type_id'] ?? null,
            'priority_id' => $parameters['priority_id'] ?? null,
            'status_id' => $parameters['status_id'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => "✅ Ticket '{$ticket->title}' created successfully!",
            'data' => $ticket,
            'redirect' => "/admin/tickets/{$ticket->id}",
        ];
    }

    /**
     * Update ticket
     */
    public function updateTicket(array $parameters): array
    {
        $required = ['ticket_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $ticket = Ticket::find($parameters['ticket_id']);
        if (!$ticket) {
            return ['success' => false, 'message' => 'Ticket not found.'];
        }

        $updateData = array_filter([
            'title' => $parameters['title'] ?? null,
            'description' => $parameters['description'] ?? null,
            'status_id' => $parameters['status_id'] ?? null,
            'priority_id' => $parameters['priority_id'] ?? null,
            'responsible_id' => $this->findUserId($parameters['assigned_to'] ?? null),
        ]);

        $ticket->update($updateData);

        return [
            'success' => true,
            'message' => "✅ Ticket '{$ticket->title}' updated!",
            'data' => $ticket,
        ];
    }

    /**
     * Assign ticket to user
     */
    public function assignTicket(array $parameters): array
    {
        $required = ['ticket_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $ticket = Ticket::find($parameters['ticket_id']);
        if (!$ticket) {
            return ['success' => false, 'message' => 'Ticket not found.'];
        }

        $userId = $this->findUserId($parameters['user_name'] ?? $parameters['user_id'] ?? null);
        if (!$userId) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $ticket->update(['responsible_id' => $userId]);
        $user = User::find($userId);

        return [
            'success' => true,
            'message' => "✅ Ticket assigned to {$user->name}!",
            'data' => ['ticket' => $ticket, 'user' => $user],
        ];
    }

    /**
     * Close ticket
     */
    public function closeTicket(array $parameters): array
    {
        $required = ['ticket_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $ticket = Ticket::find($parameters['ticket_id']);
        if (!$ticket) {
            return ['success' => false, 'message' => 'Ticket not found.'];
        }

        // Find "closed" status
        $closedStatus = \App\Models\Status::where('name', 'Closed')->first();
        if ($closedStatus) {
            $ticket->update(['status_id' => $closedStatus->id]);
        }

        return [
            'success' => true,
            'message' => "✅ Ticket '{$ticket->title}' closed!",
            'data' => $ticket,
        ];
    }

    /**
     * List projects
     */
    public function listProjects(array $parameters): array
    {
        $query = Project::query();

        if (isset($parameters['status'])) {
            $query->where('status', $parameters['status']);
        }

        if (isset($parameters['owner'])) {
            $userId = $this->findUserId($parameters['owner']);
            if ($userId) {
                $query->where('owner_id', $userId);
            }
        }

        $projects = $query->limit($parameters['limit'] ?? 10)->get();

        $message = "Found {$projects->count()} project(s)";
        if (isset($parameters['status'])) {
            $message .= " with status '{$parameters['status']}'";
        }

        return [
            'success' => true,
            'message' => $message,
            'data' => $projects,
        ];
    }

    /**
     * List tickets
     */
    public function listTickets(array $parameters): array
    {
        $query = Ticket::query();

        if (isset($parameters['project_id'])) {
            $query->where('project_id', $parameters['project_id']);
        }

        if (isset($parameters['assigned_to'])) {
            $userId = $this->findUserId($parameters['assigned_to']);
            if ($userId) {
                $query->where('responsible_id', $userId);
            }
        }

        if (isset($parameters['status'])) {
            $query->whereHas('status', fn($q) => $q->where('name', $parameters['status']));
        }

        $tickets = $query->limit($parameters['limit'] ?? 20)->get();

        return [
            'success' => true,
            'message' => "Found {$tickets->count()} ticket(s)",
            'data' => $tickets,
        ];
    }

    /**
     * Assign user to project
     */
    public function assignUserToProject(array $parameters): array
    {
        $required = ['project_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $project = Project::find($parameters['project_id']);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found.'];
        }

        $userId = $this->findUserId($parameters['user_name'] ?? $parameters['user_id'] ?? null);
        if (!$userId) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $user = User::find($userId);
        $project->users()->syncWithoutDetaching([$userId]);

        return [
            'success' => true,
            'message' => "✅ {$user->name} assigned to '{$project->name}'!",
            'data' => ['project' => $project, 'user' => $user],
        ];
    }

    /**
     * Get project status and statistics
     */
    public function getProjectStatus(array $parameters): array
    {
        $required = ['project_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $project = Project::with(['tickets', 'users', 'backlogItems'])->find($parameters['project_id']);
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found.'];
        }

        $stats = [
            'total_tickets' => $project->tickets->count(),
            'open_tickets' => $project->tickets->whereIn('status.name', ['Open', 'In Progress'])->count(),
            'closed_tickets' => $project->tickets->where('status.name', 'Closed')->count(),
            'team_members' => $project->users->count(),
            'backlog_items' => $project->backlogItems->count(),
            'completion_rate' => $this->calculateCompletionRate($project),
        ];

        return [
            'success' => true,
            'message' => "Project '{$project->name}' statistics:",
            'data' => ['project' => $project, 'stats' => $stats],
        ];
    }

    /**
     * Create backlog item (Epic/Feature/Story)
     */
    public function createBacklogItem(array $parameters): array
    {
        $required = ['title', 'type', 'project_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        // Map type to correct format
        $typeMap = [
            'epic' => BacklogItem::TYPE_EPIC,
            'feature' => BacklogItem::TYPE_FEATURE,
            'user_story' => BacklogItem::TYPE_USER_STORY,
            'story' => BacklogItem::TYPE_USER_STORY,
            'task' => BacklogItem::TYPE_TASK,
        ];

        $type = $typeMap[strtolower($parameters['type'])] ?? BacklogItem::TYPE_USER_STORY;

        $item = BacklogItem::create([
            'project_id' => $parameters['project_id'],
            'parent_id' => $parameters['parent_id'] ?? null,
            'type' => $type,
            'title' => $parameters['title'],
            'description' => $parameters['description'] ?? null,
            'priority' => $parameters['priority'] ?? 'medium',
            'status' => 'backlog',
            'estimated_hours' => $parameters['estimated_hours'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return [
            'success' => true,
            'message' => "✅ {$type} '{$item->title}' created!",
            'data' => $item,
        ];
    }

    /**
     * Create sprint
     */
    public function createSprint(array $parameters): array
    {
        $required = ['name', 'project_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $sprint = Sprint::create([
            'name' => $parameters['name'],
            'project_id' => $parameters['project_id'],
            'goal' => $parameters['goal'] ?? null,
            'start_date' => $parameters['start_date'] ?? now(),
            'end_date' => $parameters['end_date'] ?? now()->addWeeks(2),
            'status' => 'planning',
        ]);

        return [
            'success' => true,
            'message' => "✅ Sprint '{$sprint->name}' created!",
            'data' => $sprint,
        ];
    }

    /**
     * Start sprint
     */
    public function startSprint(array $parameters): array
    {
        $required = ['sprint_id'];
        if (!$this->validateParameters($parameters, $required)) {
            return $this->missingParametersResponse($required);
        }

        $sprint = Sprint::find($parameters['sprint_id']);
        if (!$sprint) {
            return ['success' => false, 'message' => 'Sprint not found.'];
        }

        $sprint->update(['status' => 'active', 'start_date' => now()]);

        return [
            'success' => true,
            'message' => "✅ Sprint '{$sprint->name}' started!",
            'data' => $sprint,
        ];
    }

    /**
     * Get team workload
     */
    public function getTeamWorkload(array $parameters): array
    {
        $projectId = $parameters['project_id'] ?? null;
        
        $query = User::withCount(['assignedTickets' => function($q) use ($projectId) {
            if ($projectId) {
                $q->where('project_id', $projectId);
            }
            $q->whereHas('status', fn($sq) => $sq->where('name', '!=', 'Closed'));
        }]);

        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $query->whereHas('projects', fn($q) => $q->where('projects.id', $projectId));
            }
        }

        $users = $query->get();

        return [
            'success' => true,
            'message' => "Team workload statistics:",
            'data' => $users->map(fn($user) => [
                'name' => $user->name,
                'active_tickets' => $user->assigned_tickets_count,
                'status' => $user->assigned_tickets_count > 10 ? 'Overloaded' : 'Available',
            ]),
        ];
    }

    /**
     * Bulk update tickets
     */
    public function bulkUpdateTickets(array $parameters): array
    {
        $query = Ticket::query();

        if (isset($parameters['project_id'])) {
            $query->where('project_id', $parameters['project_id']);
        }

        if (isset($parameters['current_status'])) {
            $query->whereHas('status', fn($q) => $q->where('name', $parameters['current_status']));
        }

        $tickets = $query->get();
        $count = $tickets->count();

        if ($count === 0) {
            return ['success' => false, 'message' => 'No tickets found matching criteria.'];
        }

        $updateData = [];
        if (isset($parameters['new_status'])) {
            $status = \App\Models\Status::where('name', $parameters['new_status'])->first();
            if ($status) {
                $updateData['status_id'] = $status->id;
            }
        }

        if (!empty($updateData)) {
            $tickets->each->update($updateData);
        }

        return [
            'success' => true,
            'message' => "✅ Updated {$count} ticket(s)!",
            'data' => ['count' => $count],
        ];
    }

    /**
     * Find user ID by name or ID
     */
    protected function findUserId($identifier): ?int
    {
        if (!$identifier) {
            return null;
        }

        if (is_numeric($identifier)) {
            return (int)$identifier;
        }

        $user = User::where('name', 'like', "%{$identifier}%")
            ->orWhere('email', 'like', "%{$identifier}%")
            ->first();

        return $user?->id;
    }

    /**
     * Calculate project completion rate
     */
    protected function calculateCompletionRate($project): float
    {
        $total = $project->tickets->count();
        if ($total === 0) {
            return 0;
        }

        $closed = $project->tickets->where('status.name', 'Closed')->count();
        return round(($closed / $total) * 100, 2);
    }

    /**
     * Validate required parameters
     */
    protected function validateParameters(array $parameters, array $required): bool
    {
        foreach ($required as $param) {
            if (!isset($parameters[$param]) || empty($parameters[$param])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Missing parameters response
     */
    protected function missingParametersResponse(array $required): array
    {
        return [
            'success' => false,
            'message' => '❌ Missing required parameters: ' . implode(', ', $required),
            'requires_input' => true,
        ];
    }
}
