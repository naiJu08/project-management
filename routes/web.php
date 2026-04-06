<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Controllers\RoadMap\DataController;
use App\Http\Controllers\Auth\OidcAuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Events\CallOffer;
use App\Events\CallAnswer;
use App\Events\IceCandidate;
use Illuminate\Http\Request;

// Test AI Assistant
Route::get('/test-ai', function () {
    return view('test-ai');
})->name('test-ai');

// Chat page
Route::get('/chat', function () {
    return view('chat');
})->middleware(['auth'])->name('chat');

// Share ticket
Route::get('/tickets/share/{ticket:code}', function (Ticket $ticket) {
    return redirect()->to(route('filament.resources.tickets.view', $ticket));
})->name('filament.resources.tickets.share');

// Validate an account
Route::get('/validate-account/{user:creation_token}', function (User $user) {
    return view('validate-account', compact('user'));
})
    ->name('validate-account')
    ->middleware([
        'web',
        DispatchServingFilamentEvent::class
    ]);

// Login redirection
Route::redirect('/login-redirect', '/login')->name('login');

// Road map JSON data
Route::get('road-map/data/{project}', [DataController::class, 'data'])
    ->middleware(['verified', 'auth'])
    ->name('road-map.data');

// OIDC Authentication
Route::name('oidc.')
    ->prefix('oidc')
    ->group(function () {
        Route::get('redirect', [OidcAuthController::class, 'redirect'])->name('redirect');
        Route::get('callback', [OidcAuthController::class, 'callback'])->name('callback');
    });

// Email verification
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

// ==================== VOICE CALL ROUTES ====================
Route::get('/voice-call/{id}', function ($id) {
    $user = App\Models\User::findOrFail($id);
    return view('voice-call', compact('user'));
})->middleware(['auth'])->name('voice-call');

// ==================== VIDEO CALL ROUTES ====================
Route::get('/video-call/{id}', function ($id) {
    $user = App\Models\User::findOrFail($id);
    return view('video-call', compact('user'));
})->middleware(['auth'])->name('video-call');

Route::post('/send-offer', function (Request $request) {
    $offer = $request->offer;
    if (is_string($offer))
        $offer = json_decode($offer, true);

    $callerId = auth()->id();
    $callerName = auth()->user()->name;
    $receiverId = $request->receiverId;

    \Log::info('📞 Sending call offer', ['from' => $callerId, 'to' => $receiverId]);

    broadcast(new CallOffer($offer, $callerId, $callerName, $receiverId))->toOthers();
    return response()->json(['status' => 'offer sent']);
})->middleware(['auth']);

Route::post('/send-answer', function (Request $request) {
    $answer = $request->answer;
    if (is_string($answer))
        $answer = json_decode($answer, true);

    $answererId = auth()->id();
    $receiverId = $request->receiverId;

    \Log::info('📞 Sending call answer', ['from' => $answererId, 'to' => $receiverId]);

    broadcast(new CallAnswer($answer, $answererId, $receiverId))->toOthers();
    return response()->json(['status' => 'answer sent']);
})->middleware(['auth']);

Route::post('/send-ice', function (Request $request) {
    $senderId = auth()->id();
    $receiverId = $request->receiverId;

    broadcast(new IceCandidate($request->candidate, $senderId, $receiverId))->toOthers();
    return response()->json(['status' => 'ice sent']);
})->middleware(['auth']);

// ==================== BACKLOG EXPORT ROUTES ====================
Route::get('/backlog/export/json', function (Request $request) {
    $projectId = $request->get('project_id');
    
    if (!$projectId) {
        return redirect()->back()->with('error', 'Project not found');
    }
    
    $project = \App\Models\Project::findOrFail($projectId);
    $query = $project->backlogItems()
        ->with(['parent', 'children', 'assignee', 'sprint'])
        ->whereNull('deleted_at');
    
    // Apply filters
    if ($request->get('filterType') && $request->get('filterType') !== 'all') {
        $query->where('type', $request->get('filterType'));
    }
    
    if ($request->get('filterStatus') && $request->get('filterStatus') !== 'all') {
        $query->where('status', $request->get('filterStatus'));
    }
    
    if ($request->get('filterAssignee') && $request->get('filterAssignee') !== 'all') {
        $query->where('assignee_id', $request->get('filterAssignee'));
    }
    
    if ($request->get('filterSprint') && $request->get('filterSprint') !== 'all') {
        if ($request->get('filterSprint') === 'backlog') {
            $query->whereNull('sprint_id');
        } else {
            $query->where('sprint_id', $request->get('filterSprint'));
        }
    }
    
    if ($request->get('searchTerm')) {
        $query->where(function($q) use ($request) {
            $q->where('title', 'like', '%' . $request->get('searchTerm') . '%')
              ->orWhere('description', 'like', '%' . $request->get('searchTerm') . '%')
              ->orWhere('code', 'like', '%' . $request->get('searchTerm') . '%');
        });
    }
    
    $items = $query->get()->map(function($item) {
        return [
            'code' => $item->code,
            'type' => $item->type,
            'title' => $item->title,
            'description' => $item->description,
            'status' => $item->status,
            'priority' => $item->priority,
            'assignee' => $item->assignee ? $item->assignee->name : null,
            'sprint' => $item->sprint ? $item->sprint->name : null,
            'estimated_hours' => $item->estimated_hours,
            'start_date' => $item->start_date ? $item->start_date->format('Y-m-d') : null,
            'due_date' => $item->due_date ? $item->due_date->format('Y-m-d') : null,
            'parent_code' => $item->parent ? $item->parent->code : null,
            'children_count' => $item->children->count(),
            'completion_percentage' => $item->getCompletionPercentage(),
            'total_estimated_hours' => $item->getTotalEstimatedHours(),
            'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
        ];
    });
    
    $filename = 'backlog_export_' . date('Y-m-d_His') . '.json';
    
    return response()->stream(function() use ($items) {
        echo json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }, 200, [
        'Content-Type' => 'application/json',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
    
})->middleware(['auth'])->name('backlog.export.json');

// Test broadcast endpoint
Route::post('/api/test-broadcast', function (Request $request) {
    $userId = auth()->id();
    $userName = auth()->user()->name;

    \Log::info('🧪 Test broadcast', [
        'user_id' => $userId,
        'channel' => 'voice-call.' . $userId
    ]);

    broadcast(new \App\Events\TestBroadcast($userId, "Test from {$userName}"))->toOthers();

    return response()->json([
        'status' => 'test broadcast sent',
        'channel' => 'voice-call.' . $userId,
        'user_id' => $userId
    ]);
})->middleware(['auth']);