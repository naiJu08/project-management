<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\BacklogItem;
use App\Models\Epic;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration should be run via the command: php artisan backlog:migrate-tickets
        // It will skip automatic execution to prevent duplicate entries
        
        // Check if backlog items already exist
        $existingCount = DB::table('backlog_items')->count();
        
        if ($existingCount > 0) {
            // Already migrated, skip
            return;
        }
        
        // Check if there are any tickets to migrate
        $ticketCount = DB::table('tickets')->whereNull('deleted_at')->count();
        
        if ($ticketCount === 0) {
            // No tickets to migrate
            return;
        }
        
        // Only run if explicitly requested (not during normal migrations)
        // Use the artisan command instead: php artisan backlog:migrate-tickets
        return;
    }

    /**
     * Migrate existing Epics to BacklogItem type Epic
     */
    protected function migrateEpics(): void
    {
        if (!Schema::hasTable('epics')) {
            return;
        }

        $epics = DB::table('epics')->get();
        
        foreach ($epics as $epic) {
            $backlogItem = BacklogItem::create([
                'project_id' => $epic->project_id,
                'parent_id' => null, // Epics are top-level
                'type' => BacklogItem::TYPE_EPIC,
                'title' => $epic->name,
                'description' => $epic->description ?? '',
                'status' => $this->mapStatus($epic->status ?? 'To Do'),
                'priority' => 'Medium',
                'assignee_id' => null,
                'sprint_id' => null,
                'estimated_hours' => null,
                'start_date' => $epic->starts_at ?? null,
                'due_date' => $epic->ends_at ?? null,
                'order_index' => $epic->order ?? 0,
                'created_by' => $epic->owner_id ?? null,
                'updated_by' => $epic->owner_id ?? null,
                'created_at' => $epic->created_at,
                'updated_at' => $epic->updated_at,
            ]);

            // Store mapping for later reference
            DB::table('epic_backlog_mapping')->insert([
                'epic_id' => $epic->id,
                'backlog_item_id' => $backlogItem->id,
            ]);
        }
    }

    /**
     * Migrate existing Tickets to User Stories
     */
    protected function migrateTickets(): void
    {
        $tickets = DB::table('tickets')
            ->whereNull('deleted_at')
            ->orderBy('project_id')
            ->orderBy('order')
            ->get();
        
        foreach ($tickets as $ticket) {
            // Determine parent_id based on epic
            $parentId = null;
            if ($ticket->epic_id) {
                $mapping = DB::table('epic_backlog_mapping')
                    ->where('epic_id', $ticket->epic_id)
                    ->first();
                $parentId = $mapping->backlog_item_id ?? null;
            }

            $backlogItem = BacklogItem::create([
                'project_id' => $ticket->project_id,
                'parent_id' => $parentId,
                'type' => BacklogItem::TYPE_USER_STORY, // Default: tickets become User Stories
                'title' => $ticket->name,
                'description' => $ticket->content ?? '',
                'status' => $this->mapTicketStatus($ticket->status_id),
                'priority' => $this->mapTicketPriority($ticket->priority_id),
                'assignee_id' => $ticket->responsible_id,
                'sprint_id' => $ticket->sprint_id,
                'estimated_hours' => $ticket->estimation ?? null,
                'start_date' => null,
                'due_date' => null,
                'order_index' => $ticket->order ?? 0,
                'code' => $ticket->code, // Preserve original ticket code
                'created_by' => $ticket->owner_id,
                'updated_by' => $ticket->owner_id,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
            ]);

            // Store mapping
            DB::table('ticket_backlog_mapping')->insert([
                'ticket_id' => $ticket->id,
                'backlog_item_id' => $backlogItem->id,
            ]);

            // Update ticket with backlog_item_id
            DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['backlog_item_id' => $backlogItem->id]);
        }
    }

    /**
     * Handle parent-child relationships between tickets
     */
    protected function migrateTicketHierarchy(): void
    {
        // If you have parent-child ticket relationships, handle them here
        // For now, we'll skip this as the base schema doesn't have it
    }

    /**
     * Link tickets to their corresponding backlog items
     */
    protected function linkTicketsToBacklogItems(): void
    {
        // Already done in migrateTickets() method
        // This method is a placeholder for any additional linking logic
    }

    /**
     * Map ticket status_id to BacklogItem status string
     */
    protected function mapTicketStatus(?int $statusId): string
    {
        if (!$statusId) {
            return BacklogItem::STATUS_TODO;
        }

        $status = DB::table('ticket_statuses')->find($statusId);
        
        if (!$status) {
            return BacklogItem::STATUS_TODO;
        }

        // Map based on status name
        $statusName = strtolower($status->name);
        
        if (str_contains($statusName, 'done') || str_contains($statusName, 'complete') || str_contains($statusName, 'closed')) {
            return BacklogItem::STATUS_DONE;
        }
        
        if (str_contains($statusName, 'progress') || str_contains($statusName, 'working') || str_contains($statusName, 'active')) {
            return BacklogItem::STATUS_IN_PROGRESS;
        }
        
        if (str_contains($statusName, 'block') || str_contains($statusName, 'hold')) {
            return BacklogItem::STATUS_BLOCKED;
        }
        
        return BacklogItem::STATUS_TODO;
    }

    /**
     * Map ticket priority_id to BacklogItem priority string
     */
    protected function mapTicketPriority(?int $priorityId): string
    {
        if (!$priorityId) {
            return BacklogItem::PRIORITY_MEDIUM;
        }

        $priority = DB::table('ticket_priorities')->find($priorityId);
        
        if (!$priority) {
            return BacklogItem::PRIORITY_MEDIUM;
        }

        // Map based on priority name
        $priorityName = strtolower($priority->name);
        
        if (str_contains($priorityName, 'critical') || str_contains($priorityName, 'urgent')) {
            return BacklogItem::PRIORITY_CRITICAL;
        }
        
        if (str_contains($priorityName, 'high')) {
            return BacklogItem::PRIORITY_HIGH;
        }
        
        if (str_contains($priorityName, 'low')) {
            return BacklogItem::PRIORITY_LOW;
        }
        
        return BacklogItem::PRIORITY_MEDIUM;
    }

    /**
     * Map epic status to BacklogItem status
     */
    protected function mapStatus(?string $status): string
    {
        if (!$status) {
            return BacklogItem::STATUS_TODO;
        }

        $statusLower = strtolower($status);
        
        if (str_contains($statusLower, 'done') || str_contains($statusLower, 'complete')) {
            return BacklogItem::STATUS_DONE;
        }
        
        if (str_contains($statusLower, 'progress') || str_contains($statusLower, 'active')) {
            return BacklogItem::STATUS_IN_PROGRESS;
        }
        
        return BacklogItem::STATUS_TODO;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove backlog_item_id from tickets
        DB::table('tickets')->update(['backlog_item_id' => null]);
        
        // Drop mapping tables
        Schema::dropIfExists('ticket_backlog_mapping');
        Schema::dropIfExists('epic_backlog_mapping');
        
        // Note: We don't delete backlog_items as they may have been manually created
        // If you want to completely reverse, uncomment the line below
        // DB::table('backlog_items')->truncate();
    }
};
