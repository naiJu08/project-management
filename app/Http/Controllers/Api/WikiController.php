<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WikiPage;
use Illuminate\Http\Request;

class WikiController extends Controller
{
    /**
     * Get all wiki pages for a project
     */
    public function index(Project $project)
    {
        $pages = $project->wikiPages()->with('children')->get();
        return response()->json($pages);
    }

    /**
     * Create a new wiki page
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'parent_id' => 'nullable|exists:wiki_pages,id',
        ]);

        $page = $project->wikiPages()->create([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'parent_id' => $validated['parent_id'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json($page, 201);
    }

    /**
     * Get a specific wiki page
     */
    public function show(WikiPage $wikiPage)
    {
        $wikiPage->load('children', 'creator', 'updater');
        return response()->json($wikiPage);
    }

    /**
     * Update a wiki page
     */
    public function update(Request $request, WikiPage $wikiPage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $wikiPage->update([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'updated_by' => auth()->id(),
            'version' => $wikiPage->version + 1,
        ]);

        return response()->json($wikiPage);
    }

    /**
     * Delete a wiki page
     */
    public function destroy(WikiPage $wikiPage)
    {
        $wikiPage->delete();
        return response()->json(['message' => 'Wiki page deleted successfully']);
    }
}
