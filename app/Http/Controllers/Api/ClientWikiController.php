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
        
        if (!$this->canAccessProject($user, $project)) {
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
        if (!$this->canAccessProject($user, $project)) {
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

    private function canAccessProject($user, Project $project): bool
    {
        if ($this->isClientWikiUser($user)) {
            return WikiPage::where('project_id', $project->id)
                ->clientVisible()
                ->exists();
        }

        return $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists();
    }

    private function isClientWikiUser($user): bool
    {
        return $user->roles->contains(fn ($role) => strtolower($role->name) === 'client')
            && $user->can('View client wiki');
    }
}
