<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\BacklogItem;
use App\Models\Epic;

class MigrateTicketsToBacklog extends Command
{
    protected $signature = 'backlog:migrate-tickets 
                            {--dry-run : Run without making changes}
                            {--project= : Migrate specific project only}
                            {--force : Skip confirmation}';

    protected $description = 'Migrate existing tickets and epics to the new backlog items system';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $projectId = $this->option('project');
        $force = $this->option('force');

        $this->info('===========================================');
        $this->info('Ticket to Backlog Items Migration Tool');
        $this->info('===========================================');
        $this->newLine();

        // Get statistics
        $stats = $this->getStatistics($projectId);
        
        $this->info('Current System Statistics:');
        $this->table(
            ['Type', 'Count'],
            [
                ['Epics', $stats['epics']],
                ['Tickets', $stats['tickets']],
                ['Existing Backlog Items', $stats['backlog_items']],
            ]
        );
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Confirmation
        if (!$force && !$dryRun) {
            if (!$this->confirm('This will migrate all tickets to backlog items. Continue?')) {
                $this->error('Migration cancelled.');
                return 1;
            }
        }

        // Run migration
        try {
            DB::beginTransaction();

            $this->info('Step 1: Migrating Epics...');
            $epicCount = $this->migrateEpics($projectId, $dryRun);
            $this->info("✓ Migrated {$epicCount} epics");
            $this->newLine();

            $this->info('Step 2: Migrating Tickets to User Stories...');
            $ticketCount = $this->migrateTickets($projectId, $dryRun);
            $this->info("✓ Migrated {$ticketCount} tickets");
            $this->newLine();

            $this->info('Step 3: Linking relationships...');
            $this->linkRelationships($dryRun);
            $this->info("✓ Relationships linked");
            $this->newLine();

            if ($dryRun) {
                DB::rollBack();
                $this->warn('🔍 Dry run complete - no changes were saved');
            } else {
                DB::commit();
                $this->info('✅ Migration completed successfully!');
            }

            $this->newLine();
            $this->displaySummary($epicCount, $ticketCount);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    protected function getStatistics($projectId = null)
    {
        $query = $projectId ? ['project_id' => $projectId] : [];

        return [
            'epics' => DB::table('epics')->where($query)->count(),
            'tickets' => DB::table('tickets')->where($query)->whereNull('deleted_at')->count(),
            'backlog_items' => BacklogItem::where($query)->count(),
        ];
    }

    protected function migrateEpics($projectId, $dryRun)
    {
        $query = DB::table('epics');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $epics = $query->get();
        $count = 0;

        foreach ($epics as $epic) {
            // Check if already migrated
            $existing = DB::table('epic_backlog_mapping')
                ->where('epic_id', $epic->id)
                ->first();

            if ($existing) {
                $this->warn("  ⚠ Epic '{$epic->name}' already migrated, skipping");
                continue;
            }

            if (!$dryRun) {
                $backlogItem = BacklogItem::create([
                    'project_id' => $epic->project_id,
                    'parent_id' => null,
                    'type' => BacklogItem::TYPE_EPIC,
                    'title' => $epic->name,
                    'description' => $epic->description ?? '',
                    'status' => $this->mapStatus($epic->status ?? 'To Do'),
                    'priority' => 'Medium',
                    'start_date' => $epic->starts_at ?? null,
                    'due_date' => $epic->ends_at ?? null,
                    'order_index' => $epic->order ?? 0,
                    'created_by' => $epic->owner_id ?? null,
                    'updated_by' => $epic->owner_id ?? null,
                    'created_at' => $epic->created_at,
                    'updated_at' => $epic->updated_at,
                ]);

                DB::table('epic_backlog_mapping')->insert([
                    'epic_id' => $epic->id,
                    'backlog_item_id' => $backlogItem->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->line("  → Epic: {$epic->name}");
            $count++;
        }

        return $count;
    }

    protected function migrateTickets($projectId, $dryRun)
    {
        $query = DB::table('tickets')->whereNull('deleted_at');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $tickets = $query->orderBy('project_id')->orderBy('order')->get();
        $count = 0;

        foreach ($tickets as $ticket) {
            // Check if already migrated
            $existing = DB::table('ticket_backlog_mapping')
                ->where('ticket_id', $ticket->id)
                ->first();

            if ($existing) {
                $this->warn("  ⚠ Ticket '{$ticket->name}' already migrated, skipping");
                continue;
            }

            // Find parent (Epic) if exists
            $parentId = null;
            if ($ticket->epic_id) {
                $mapping = DB::table('epic_backlog_mapping')
                    ->where('epic_id', $ticket->epic_id)
                    ->first();
                $parentId = $mapping->backlog_item_id ?? null;
            }

            if (!$dryRun) {
                $backlogItem = BacklogItem::create([
                    'project_id' => $ticket->project_id,
                    'parent_id' => $parentId,
                    'type' => BacklogItem::TYPE_USER_STORY,
                    'title' => $ticket->name,
                    'description' => $ticket->content ?? '',
                    'status' => $this->mapTicketStatus($ticket->status_id),
                    'priority' => $this->mapTicketPriority($ticket->priority_id),
                    'assignee_id' => $ticket->responsible_id,
                    'sprint_id' => $ticket->sprint_id,
                    'estimated_hours' => $ticket->estimation ?? null,
                    'order_index' => $ticket->order ?? 0,
                    'code' => $ticket->code,
                    'created_by' => $ticket->owner_id,
                    'updated_by' => $ticket->owner_id,
                    'created_at' => $ticket->created_at,
                    'updated_at' => $ticket->updated_at,
                ]);

                DB::table('ticket_backlog_mapping')->insert([
                    'ticket_id' => $ticket->id,
                    'backlog_item_id' => $backlogItem->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update ticket with backlog_item_id
                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update(['backlog_item_id' => $backlogItem->id]);
            }

            $this->line("  → Ticket: {$ticket->code} - {$ticket->name}");
            $count++;
        }

        return $count;
    }

    protected function linkRelationships($dryRun)
    {
        // Additional relationship linking if needed
        // Currently handled in migrateTickets()
    }

    protected function mapStatus($status): string
    {
        $statusLower = strtolower($status ?? '');
        
        if (str_contains($statusLower, 'done') || str_contains($statusLower, 'complete')) {
            return BacklogItem::STATUS_DONE;
        }
        
        if (str_contains($statusLower, 'progress') || str_contains($statusLower, 'active')) {
            return BacklogItem::STATUS_IN_PROGRESS;
        }
        
        return BacklogItem::STATUS_TODO;
    }

    protected function mapTicketStatus($statusId): string
    {
        if (!$statusId) {
            return BacklogItem::STATUS_TODO;
        }

        $status = DB::table('ticket_statuses')->find($statusId);
        
        if (!$status) {
            return BacklogItem::STATUS_TODO;
        }

        $statusName = strtolower($status->name);
        
        if (str_contains($statusName, 'done') || str_contains($statusName, 'complete') || str_contains($statusName, 'closed')) {
            return BacklogItem::STATUS_DONE;
        }
        
        if (str_contains($statusName, 'progress') || str_contains($statusName, 'working')) {
            return BacklogItem::STATUS_IN_PROGRESS;
        }
        
        if (str_contains($statusName, 'block') || str_contains($statusName, 'hold')) {
            return BacklogItem::STATUS_BLOCKED;
        }
        
        return BacklogItem::STATUS_TODO;
    }

    protected function mapTicketPriority($priorityId): string
    {
        if (!$priorityId) {
            return BacklogItem::PRIORITY_MEDIUM;
        }

        $priority = DB::table('ticket_priorities')->find($priorityId);
        
        if (!$priority) {
            return BacklogItem::PRIORITY_MEDIUM;
        }

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

    protected function displaySummary($epicCount, $ticketCount)
    {
        $this->info('===========================================');
        $this->info('Migration Summary');
        $this->info('===========================================');
        $this->table(
            ['Item', 'Count'],
            [
                ['Epics Migrated', $epicCount],
                ['Tickets Migrated', $ticketCount],
                ['Total Backlog Items', $epicCount + $ticketCount],
            ]
        );
        $this->newLine();
        $this->info('Next Steps:');
        $this->line('  1. Review the migrated data in the Backlog tab');
        $this->line('  2. Organize items into Features if needed');
        $this->line('  3. Break down User Stories into Tasks');
        $this->line('  4. Assign items to sprints');
        $this->newLine();
    }
}
