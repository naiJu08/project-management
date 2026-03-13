<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WikiPage;
use Illuminate\Http\Request;

class ClientWikiController extends Controller
{
    /**
     * Get all client-visible wiki pages for a project
     */
    public function index(Project $project)
    {
        $user = auth()->user();
        
        // Check if user is a client or has access to the project
        if (!$user->hasRole('Client') && !$project->users->contains($user->id) && $project->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pages = $project->wikiPages()
            ->clientVisible()
            ->with(['children' => function ($query) {
                $query->clientVisible();
            }, 'creator', 'updater', 'activeSignoffs.client'])
            ->get();

        return response()->json($pages);
    }

    /**
     * Get a specific client-visible wiki page
     */
    public function show(WikiPage $wikiPage)
    {
        $user = auth()->user();
        
        // Check if page is client visible
        if (!$wikiPage->client_visible) {
            return response()->json(['message' => 'Page not available'], 404);
        }

        // Check if user has access to the project
        $project = $wikiPage->project;
        if (!$user->hasRole('Client') && !$project->users->contains($user->id) && $project->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $wikiPage->load([
            'children' => function ($query) {
                $query->clientVisible();
            },
            'creator',
            'updater',
            'comments.user',
            'comments.replies.user',
            'activeSignoffs.client',
            'signoffs' => function ($query) {
                $query->orderBy('signed_off_at', 'desc')->limit(5);
            }
        ]);

        return response()->json([
            'page' => $wikiPage,
            'signoff_status' => $wikiPage->getSignoffStatus(),
            'is_signed_off' => $wikiPage->isSignedOff(),
        ]);
    }
}
