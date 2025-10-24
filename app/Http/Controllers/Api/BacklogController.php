<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BacklogController extends Controller
{
    /**
     * Get backlog with tasks grouped by status
     */
    public function index(Project $project)
    {
        $backlogTickets = $project->tickets()
            ->whereNull('sprint_id')
            ->with(['responsible', 'priority', 'status'])
            ->orderBy('order')
            ->get();

        $sprints = $project->sprints()
            ->with(['tickets.responsible', 'tickets.priority', 'tickets.status'])
            ->orderBy('starts_at', 'desc')
            ->get();

        return response()->json([
            'backlog' => $backlogTickets,
            'sprints' => $sprints,
        ]);
    }

    /**
     * Create a new sprint
     */
    public function createSprint(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'description' => 'nullable|string',
        ]);

        $sprint = $project->sprints()->create($validated);

        return response()->json($sprint, 201);
    }

    /**
     * Update task status (for drag/drop)
     */
    public function updateTaskStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:ticket_statuses,id',
        ]);

        $ticket->update(['status_id' => $validated['status_id']]);

        return response()->json($ticket);
    }

    /**
     * Move task to sprint
     */
    public function moveToSprint(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'sprint_id' => 'required|exists:sprints,id',
        ]);

        $ticket->update(['sprint_id' => $validated['sprint_id']]);

        return response()->json($ticket);
    }

    /**
     * Move task to backlog
     */
    public function moveToBacklog(Ticket $ticket)
    {
        $ticket->update(['sprint_id' => null]);

        return response()->json($ticket);
    }
}
