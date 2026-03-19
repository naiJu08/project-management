<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Controllers\RoadMap\DataController;
use App\Http\Controllers\Auth\OidcAuthController;
use App\Http\Livewire\UserChat;
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

// Login default redirection
// Route::redirect('/login', '/admin/login');
Route::redirect('/login-redirect', '/login')->name('login');


// Road map JSON data
Route::get('road-map/data/{project}', [DataController::class, 'data'])
    ->middleware(['verified', 'auth'])
    ->name('road-map.data');

Route::name('oidc.')
    ->prefix('oidc')
    ->group(function () {
        Route::get('redirect', [OidcAuthController::class, 'redirect'])->name('redirect');
        Route::get('callback', [OidcAuthController::class, 'callback'])->name('callback');
    });



Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');



Route::get('/voice-call/{id}', function ($id) {

    $user = User::find($id);

    return view('voice-call', compact('user'));

});

Route::post('/send-offer', function (Request $request) {

    $offer = $request->offer;
    
    // Ensure offer is an array/object with type and sdp
    if (is_string($offer)) {
        $offer = json_decode($offer, true);
    }
    
    $callerId = auth()->id();
    $callerName = auth()->user()->name;
    $receiverId = $request->receiverId;
    
    \Log::info('CallOffer received:', [
        'offer_type' => $offer['type'] ?? 'MISSING',
        'has_sdp' => !empty($offer['sdp']),
        'sdp_length' => strlen($offer['sdp'] ?? ''),
        'callerId' => $callerId,
        'callerName' => $callerName,
        'receiverId' => $receiverId,
        'broadcast_channel' => 'voice-call.' . $receiverId,
    ]);

    \Log::info('BROADCAST: About to broadcast CallOffer event', [
        'channel' => 'voice-call.' . $receiverId,
        'event' => 'CallOffer',
        'sender' => $callerId,
        'recipient' => $receiverId,
    ]);

    broadcast(new CallOffer(
        $offer,
        $callerId,
        $callerName,
        $receiverId
    ))->toOthers();

    \Log::info('BROADCAST: CallOffer event broadcasted successfully', [
        'recipient' => $receiverId,
    ]);

    return response()->json(['status' => 'offer sent']);
});

// ✅ TEST ENDPOINT: Broadcast a test message
Route::post('/api/test-broadcast', function (Request $request) {
    $userId = auth()->id();
    $channelName = 'voice-call.' . $userId;
    
    \Log::info('TEST BROADCAST: Sending test message', [
        'user_id' => $userId,
        'channel' => $channelName,
        'timestamp' => now(),
    ]);
    
    broadcast(new \App\Events\TestBroadcast($userId, "Test message from " . auth()->user()->name))->toOthers();
    
    return response()->json([
        'status' => 'test broadcast sent',
        'channel' => $channelName,
        'user_id' => $userId,
        'message' => 'Check receiver console for message'
    ]);
});


Route::post('/send-answer', function (Request $request) {

    // Log that we received something
    \Log::info('INCOMING REQUEST: /send-answer endpoint hit', [
        'timestamp' => now(),
        'method' => $request->method(),
        'has_body' => $request->getContent() !== '',
        'body_length' => strlen($request->getContent())
    ]);

    $answer = $request->answer;
    
    // Ensure answer is an array/object with type and sdp
    if (is_string($answer)) {
        $answer = json_decode($answer, true);
    }
    
    \Log::info('CallAnswer received:', [
        'answer_type' => $answer['type'] ?? 'MISSING',
        'has_sdp' => !empty($answer['sdp']),
        'sdp_length' => strlen($answer['sdp'] ?? ''),
        'receiverId' => $request->receiverId,
        'authenticated_user' => auth()->id()
    ]);

    broadcast(new CallAnswer(
        $answer,
        auth()->id(),
        $request->receiverId
    ))->toOthers();

    \Log::info('CallAnswer broadcast sent successfully', [
        'to_user' => $request->receiverId,
        'from_user' => auth()->id()
    ]);

    return response()->json(['status' => 'answer sent']);
});


Route::post('/send-ice', function (Request $request) {

    broadcast(new IceCandidate(
        $request->candidate,
        auth()->id(),
        $request->receiverId
    ))->toOthers();
    return response()->json(['status' => 'ice sent']);
});