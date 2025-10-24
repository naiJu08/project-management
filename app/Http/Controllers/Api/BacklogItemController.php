<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BacklogItem;
use App\Models\BacklogItemComment;
use App\Models\BacklogItemHistory;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BacklogItemController extends Controller
{
    /**
     * Get full backlog hierarchy for a project
     */
    public function index(Project $project)
    {
        $backlogItems = $project->backlogItems()
            ->topLevel()
            ->with([
                'children.children.children.children', // Load 4 levels deep
                'assignee',
                'sprint',
            ])
            ->orderBy('order_index')
            ->get();

        return response()->json([
            'items' => $backlogItems,
            'stats' => [
                'total_items' => $project->backlogItems()->count(),
                'epics' => $project->backlogItems()->epics()->count(),
                'features' => $project->backlogItems()->features()->count(),
                'user_stories' => $project->backlogItems()->userStories()->count(),
                'tasks' => $project->backlogItems()->tasks()->count(),
                'subtasks' => $project->backlogItems()->subtasks()->count(),
            ],
        ]);
    }

    /**
     * Get backlog items by type
     */
    public function byType(Project $project, string $type)
    {
        $items = $project->backlogItems()
            ->where('type', $type)
            ->with(['assignee', 'sprint', 'parent'])
            ->orderBy('order_index')
            ->get();

        return response()->json($items);
    }

    /**
     * Get backlog items in a sprint
     */
    public function bySprint(Project $project, int $sprintId)
    {
        $items = $project->backlogItems()
            ->inSprint($sprintId)
            ->with(['assignee', 'children'])
            ->orderBy('order_index')
            ->get();

        return response()->json($items);
    }

    /**
     * Get backlog items not in any sprint
     */
    public function backlogOnly(Project $project)
    {
        $items = $project->backlogItems()
            ->inBacklog()
            ->topLevel()
            ->with(['children', 'assignee'])
            ->orderBy('order_index')
            ->get();

        return response()->json($items);
    }

    /**
     * Show a specific backlog item with full details
     */
    public function show(BacklogItem $backlogItem)
    {
        $backlogItem->load([
            'children',
            'parent',
            'assignee',
            'sprint',
            'creator',
            'updater',
            'comments.user',
            'comments.replies.user',
            'history.user',
        ]);

        return response()->json([
            'item' => $backlogItem,
            'hierarchy_path' => $backlogItem->getHierarchyPath(),
            'total_estimated_hours' => $backlogItem->getTotalEstimatedHours(),
            'completion_percentage' => $backlogItem->getCompletionPercentage(),
        ]);
    }

    /**
     * Create a new backlog item
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:backlog_items,id',
            'type' => 'required|in:Epic,Feature,UserStory,Task,Subtask',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Validate parent-child relationship
        if ($validated['parent_id']) {
            $parent = BacklogItem::find($validated['parent_id']);
            if (!$parent->canBeParentOf($validated['type'])) {
                return response()->json([
                    'message' => "Cannot create {$validated['type']} under {$parent->type}",
                ], 422);
            }
        }

        $item = $project->backlogItems()->create([
            ...$validated,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // Log creation
        BacklogItemHistory::create([
            'backlog_item_id' => $item->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'new_value' => ['title' => $item->title],
        ]);

        $item->load(['assignee', 'sprint', 'parent']);

        return response()->json($item, 201);
    }

    /**
     * Update a backlog item
     */
    public function update(Request $request, BacklogItem $backlogItem)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|string',
            'priority' => 'sometimes|string',
            'assignee_id' => 'nullable|exists:users,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        // Track changes for history
        $changes = [];
        foreach ($validated as $field => $value) {
            if ($backlogItem->$field != $value) {
                $changes[$field] = [
                    'old' => $backlogItem->$field,
                    'new' => $value,
                ];
            }
        }

        $backlogItem->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        // Log changes
        foreach ($changes as $field => $change) {
            BacklogItemHistory::create([
                'backlog_item_id' => $backlogItem->id,
                'user_id' => auth()->id(),
                'field' => $field,
                'old_value' => [$field => $change['old']],
                'new_value' => [$field => $change['new']],
                'action' => 'updated',
            ]);
        }

        $backlogItem->load(['assignee', 'sprint', 'parent']);

        return response()->json($backlogItem);
    }

    /**
     * Delete a backlog item
     */
    public function destroy(BacklogItem $backlogItem)
    {
        // Log deletion
        BacklogItemHistory::create([
            'backlog_item_id' => $backlogItem->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'old_value' => ['title' => $backlogItem->title],
        ]);

        $backlogItem->delete();

        return response()->json(['message' => 'Backlog item deleted successfully']);
    }

    /**
     * Move/reorder a backlog item
     */
    public function move(Request $request, BacklogItem $backlogItem)
    {
        $validated = $request->validate([
            'new_order_index' => 'required|integer|min:0',
            'new_parent_id' => 'nullable|exists:backlog_items,id',
        ]);

        try {
            $backlogItem->reorder(
                $validated['new_order_index'],
                $validated['new_parent_id'] ?? $backlogItem->parent_id
            );

            // Log move
            BacklogItemHistory::create([
                'backlog_item_id' => $backlogItem->id,
                'user_id' => auth()->id(),
                'action' => 'moved',
                'old_value' => [
                    'order' => $backlogItem->order_index,
                    'parent' => $backlogItem->parent_id,
                ],
                'new_value' => [
                    'order' => $validated['new_order_index'],
                    'parent' => $validated['new_parent_id'],
                ],
            ]);

            return response()->json($backlogItem->fresh(['parent', 'children']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Assign backlog item to sprint
     */
    public function assignToSprint(Request $request, BacklogItem $backlogItem)
    {
        $validated = $request->validate([
            'sprint_id' => 'nullable|exists:sprints,id',
        ]);

        $oldSprint = $backlogItem->sprint_id;
        $backlogItem->moveToSprint($validated['sprint_id']);

        // Log sprint assignment
        BacklogItemHistory::create([
            'backlog_item_id' => $backlogItem->id,
            'user_id' => auth()->id(),
            'field' => 'sprint_id',
            'old_value' => ['sprint_id' => $oldSprint],
            'new_value' => ['sprint_id' => $validated['sprint_id']],
            'action' => 'updated',
        ]);

        return response()->json($backlogItem->fresh('sprint'));
    }

    /**
     * Get comments for a backlog item
     */
    public function comments(BacklogItem $backlogItem)
    {
        $comments = $backlogItem->comments()->with(['user', 'replies.user'])->get();
        return response()->json($comments);
    }

    /**
     * Add a comment to a backlog item
     */
    public function addComment(Request $request, BacklogItem $backlogItem)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_comment_id' => 'nullable|exists:backlog_item_comments,id',
        ]);

        $comment = $backlogItem->allComments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'parent_comment_id' => $validated['parent_comment_id'] ?? null,
        ]);

        $comment->load('user', 'replies');

        return response()->json($comment, 201);
    }

    /**
     * Delete a comment
     */
    public function deleteComment(BacklogItemComment $comment)
    {
        if (!$comment->canDelete()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * Get history for a backlog item
     */
    public function history(BacklogItem $backlogItem)
    {
        $history = $backlogItem->history()->with('user')->get();
        return response()->json($history);
    }

    /**
     * Bulk update backlog items
     */
    public function bulkUpdate(Request $request, Project $project)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:backlog_items,id',
            'items.*.order_index' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:backlog_items,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $itemData) {
                $item = BacklogItem::find($itemData['id']);
                $item->update([
                    'order_index' => $itemData['order_index'],
                    'parent_id' => $itemData['parent_id'] ?? $item->parent_id,
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        return response()->json(['message' => 'Items updated successfully']);
    }
}
