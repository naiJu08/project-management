<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WikiPage;
use App\Models\WikiComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WikiCommentAdded;

class WikiCommentController extends Controller
{
    /**
     * Get all comments for a wiki page
     */
    public function index(WikiPage $wikiPage)
    {
        $comments = $wikiPage->comments()->with(['user', 'replies.user'])->get();
        return response()->json($comments);
    }

    /**
     * Add a comment to a wiki page
     */
    public function store(Request $request, WikiPage $wikiPage)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_comment_id' => 'nullable|exists:wiki_comments,id',
        ]);

        $comment = $wikiPage->allComments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'parent_comment_id' => $validated['parent_comment_id'] ?? null,
        ]);

        $comment->load('user', 'replies');

        // Notify project members about new comment
        $this->notifyProjectMembers($wikiPage, $comment);

        return response()->json($comment, 201);
    }

    /**
     * Delete a comment
     */
    public function destroy(WikiComment $comment)
    {
        if (!$comment->canDelete()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * Notify project members about new comment
     */
    protected function notifyProjectMembers(WikiPage $wikiPage, WikiComment $comment)
    {
        $project = $wikiPage->project;
        $users = $project->users()
            ->where('users.id', '!=', auth()->id())
            ->get();

        if ($users->count() > 0) {
            Notification::send($users, new WikiCommentAdded($wikiPage, $comment));
        }
    }
}
