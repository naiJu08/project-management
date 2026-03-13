<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WikiPage;
use App\Models\WikiSignoff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WikiPageSignedOff;

class WikiSignoffController extends Controller
{
    /**
     * Get all signoffs for a wiki page
     */
    public function index(WikiPage $wikiPage)
    {
        $signoffs = $wikiPage->signoffs()
            ->with(['client', 'signedOffBy'])
            ->get();
            
        return response()->json([
            'signoffs' => $signoffs,
            'status' => $wikiPage->getSignoffStatus(),
            'is_signed_off' => $wikiPage->isSignedOff(),
        ]);
    }

    /**
     * Create a signoff for a wiki page
     */
    public function store(Request $request, WikiPage $wikiPage)
    {
        $user = auth()->user();
        
        // Only clients can sign off
        if (!$user->hasRole('Client')) {
            return response()->json(['message' => 'Only clients can sign off documents'], 403);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string',
        ]);

        // Check if already signed off by this user for current version
        $existingSignoff = $wikiPage->signoffs()
            ->where('client_id', $user->id)
            ->where('version_signed', $wikiPage->version)
            ->where('is_outdated', false)
            ->first();

        if ($existingSignoff) {
            return response()->json(['message' => 'You have already signed off this version'], 400);
        }

        $signoff = $wikiPage->signoffs()->create([
            'client_id' => $user->id,
            'signed_off_by' => $user->id,
            'signed_off_at' => now(),
            'version_signed' => $wikiPage->version,
            'remarks' => $validated['remarks'] ?? null,
            'is_outdated' => false,
        ]);

        $signoff->load(['client', 'signedOffBy']);

        // Notify project members about signoff
        $this->notifyProjectMembers($wikiPage, $signoff);

        return response()->json($signoff, 201);
    }

    /**
     * Notify project members about signoff
     */
    protected function notifyProjectMembers(WikiPage $wikiPage, WikiSignoff $signoff)
    {
        $project = $wikiPage->project;
        $users = $project->users()->get();

        // Also notify project owner
        if ($project->owner) {
            $users->push($project->owner);
        }

        $users = $users->unique('id')->filter(function ($user) {
            return $user->id !== auth()->id();
        });

        if ($users->count() > 0) {
            Notification::send($users, new WikiPageSignedOff($wikiPage, $signoff));
        }
    }
}
