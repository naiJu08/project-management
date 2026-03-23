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

// ==================== VOICE CALL ROUTES ====================

// ==================== VOICE CALL ROUTES ====================
Route::get('/voice-call/{id}', function ($id) {
    $user = App\Models\User::findOrFail($id);
    return view('voice-call', compact('user'));
})->middleware(['auth'])->name('voice-call');

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