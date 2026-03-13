<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketDependency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TicketController extends Controller
{
    /**
     * Get ticket details with all relationships
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load([
            'approvals.approver',
            'dependencies.dependsOnTicket',
            'dependentTickets.ticket',
            'childTickets',
            'parentTicket',
            'comments.user',
            'hours.user',
            'owner',
            'responsible',
            'status',
            'priority',
            'type'
        ]);

        return response()->json([
            'data' => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'name' => $ticket->name,
                'description' => $ticket->content,
                'status' => $ticket->status->name,
                'priority' => $ticket->priority->name,
                'type' => $ticket->type->name,
                'component' => $ticket->component,
                'severity' => $ticket->severity,
                'owner' => $ticket->owner->name,
                'assignee' => $ticket->responsible?->name,
                'estimated_hours' => $ticket->estimated_hours,
                'logged_hours' => $ticket->getTotalLoggedHours(),
                'remaining_hours' => $ticket->getRemainingHours(),
                'progress_percentage' => $ticket->getProgressPercentage(),
                'start_date' => $ticket->start_date?->toDateString(),
                'due_date' => $ticket->due_date?->toDateString(),
                'is_blocked' => $ticket->isBlocked(),
                'blocking_reasons' => $ticket->getBlockingReasons(),
                'risk_level' => $ticket->risk_level,
                'risk_description' => $ticket->risk_description,
                'mitigation_plan' => $ticket->mitigation_plan,
                'budget_allocated' => $ticket->budget_allocated,
                'budget_spent' => $ticket->budget_spent,
                'is_over_budget' => $ticket->isOverBudget(),
                'remaining_budget' => $ticket->getRemainingBudget(),
                'sla_status' => $ticket->sla_status,
                'sla_due_at' => $ticket->sla_due_at?->toIso8601String(),
                'requires_approval' => $ticket->requires_approval,
                'approval_status' => $ticket->approval_status,
                'pending_approvals' => $ticket->getPendingApprovals(),
                'all_approvals_completed' => $ticket->getAllApprovalsCompleted(),
                'reopened_count' => $ticket->reopened_count,
                'comment_count' => $ticket->comments->count(),
                'attachment_count' => $ticket->attachment_count,
                'watcher_count' => $ticket->watcher_count,
                'created_at' => $ticket->created_at->toIso8601String(),
                'updated_at' => $ticket->updated_at->toIso8601String(),
                'first_response_at' => $ticket->first_response_at?->toIso8601String(),
                'resolved_at' => $ticket->resolved_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Update ticket
     */
    public function update(Request $ticket, Ticket $model): JsonResponse
    {
        $validated = $ticket->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'component' => 'sometimes|string|max:255',
            'severity' => 'sometimes|in:critical,major,minor,trivial',
            'start_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after:start_date',
            'estimated_hours' => 'sometimes|integer|min:1',
            'status_id' => 'sometimes|exists:ticket_statuses,id',
            'priority_id' => 'sometimes|exists:ticket_priorities,id',
            'responsible_id' => 'sometimes|exists:users,id',
            'risk_level' => 'sometimes|in:low,medium,high,critical',
            'risk_description' => 'sometimes|string',
            'mitigation_plan' => 'sometimes|string',
            'budget_allocated' => 'sometimes|numeric|min:0',
            'is_blocked' => 'sometimes|boolean',
            'blocked_reason' => 'sometimes|string',
        ]);

        $model->update($validated);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'data' => $model
        ]);
    }

    /**
     * Get ticket approvals
     */
    public function getApprovals(Ticket $ticket): JsonResponse
    {
        $approvals = $ticket->approvals()->with('approver')->get();

        return response()->json([
            'data' => $approvals->map(fn($approval) => [
                'id' => $approval->id,
                'type' => $approval->type,
                'status' => $approval->status,
                'approver' => $approval->approver->name,
                'comments' => $approval->comments,
                'approved_at' => $approval->approved_at?->toIso8601String(),
            ])
        ]);
    }

    /**
     * Create approval
     */
    public function createApproval(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:code_review,qa_signoff,product_approval,client_approval,management_approval',
            'approver_id' => 'required|exists:users,id',
        ]);

        $approval = $ticket->approvals()->create([
            ...$validated,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Approval created successfully',
            'data' => $approval
        ], 201);
    }

    /**
     * Approve ticket
     */
    public function approveTicket(Request $request, Ticket $ticket, TicketApproval $approval): JsonResponse
    {
        if ($approval->ticket_id !== $ticket->id) {
            return response()->json(['message' => 'Approval not found'], 404);
        }

        $validated = $request->validate([
            'comments' => 'nullable|string',
        ]);

        $approval->approve($validated['comments'] ?? null);

        return response()->json([
            'message' => 'Ticket approved successfully',
            'data' => $approval
        ]);
    }

    /**
     * Reject ticket
     */
    public function rejectTicket(Request $request, Ticket $ticket, TicketApproval $approval): JsonResponse
    {
        if ($approval->ticket_id !== $ticket->id) {
            return response()->json(['message' => 'Approval not found'], 404);
        }

        $validated = $request->validate([
            'comments' => 'required|string',
        ]);

        $approval->reject($validated['comments']);

        return response()->json([
            'message' => 'Ticket rejected',
            'data' => $approval
        ]);
    }

    /**
     * Get ticket dependencies
     */
    public function getDependencies(Ticket $ticket): JsonResponse
    {
        $dependencies = $ticket->dependencies()->with('dependsOnTicket')->get();

        return response()->json([
            'data' => $dependencies->map(fn($dep) => [
                'id' => $dep->id,
                'type' => $dep->type,
                'ticket_id' => $dep->depends_on_ticket_id,
                'ticket_code' => $dep->dependsOnTicket->code,
                'ticket_name' => $dep->dependsOnTicket->name,
                'description' => $dep->description,
            ])
        ]);
    }

    /**
     * Add dependency
     */
    public function addDependency(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'depends_on_ticket_id' => 'required|exists:tickets,id',
            'type' => 'required|in:blocks,blocked_by,related_to,duplicates,duplicated_by',
            'description' => 'nullable|string',
        ]);

        $dependency = $ticket->dependencies()->create($validated);

        return response()->json([
            'message' => 'Dependency added successfully',
            'data' => $dependency
        ], 201);
    }

    /**
     * Remove dependency
     */
    public function removeDependency(Ticket $ticket, TicketDependency $dependency): JsonResponse
    {
        if ($dependency->ticket_id !== $ticket->id) {
            return response()->json(['message' => 'Dependency not found'], 404);
        }

        $dependency->delete();

        return response()->json([
            'message' => 'Dependency removed successfully'
        ]);
    }

    /**
     * Update SLA status
     */
    public function updateSLAStatus(Ticket $ticket): JsonResponse
    {
        $ticket->updateSLAStatus();

        return response()->json([
            'message' => 'SLA status updated',
            'data' => [
                'sla_status' => $ticket->sla_status,
                'is_at_risk' => $ticket->isSLAAtRisk(),
                'is_breached' => $ticket->isSLABreached(),
            ]
        ]);
    }

    /**
     * Get ticket metrics
     */
    public function getMetrics(Ticket $ticket): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_logged_hours' => $ticket->getTotalLoggedHours(),
                'remaining_hours' => $ticket->getRemainingHours(),
                'progress_percentage' => $ticket->getProgressPercentage(),
                'is_over_budget' => $ticket->isOverBudget(),
                'remaining_budget' => $ticket->getRemainingBudget(),
                'is_blocked' => $ticket->isBlocked(),
                'blocking_reasons' => $ticket->getBlockingReasons(),
                'is_sla_at_risk' => $ticket->isSLAAtRisk(),
                'is_sla_breached' => $ticket->isSLABreached(),
                'requires_approval' => $ticket->requiresApproval(),
                'pending_approvals' => $ticket->getPendingApprovals(),
                'all_approvals_completed' => $ticket->getAllApprovalsCompleted(),
            ]
        ]);
    }

    /**
     * Get ticket activity
     */
    public function getActivity(Ticket $ticket): JsonResponse
    {
        $activities = $ticket->activities()
            ->with('user', 'oldStatus', 'newStatus')
            ->latest()
            ->paginate(50);

        return response()->json([
            'data' => $activities->map(fn($activity) => [
                'id' => $activity->id,
                'user' => $activity->user->name,
                'old_status' => $activity->oldStatus->name ?? null,
                'new_status' => $activity->newStatus->name ?? null,
                'created_at' => $activity->created_at->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
            ]
        ]);
    }
}
