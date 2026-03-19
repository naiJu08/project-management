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
    
    \Log::info('CallOffer received:', [
        'offer_type' => $offer['type'] ?? 'MISSING',
        'has_sdp' => !empty($offer['sdp']),
        'sdp_length' => strlen($offer['sdp'] ?? ''),
        'receiverId' => $request->receiverId
    ]);

    broadcast(new CallOffer(
        $offer,
        auth()->id(),
        auth()->user()->name,
        $request->receiverId
    ))->toOthers();

    return response()->json(['status' => 'offer sent']);
});


Route::post('/send-answer', function (Request $request) {

    $answer = $request->answer;
    
    // Ensure answer is an array/object with type and sdp
    if (is_string($answer)) {
        $answer = json_decode($answer, true);
    }
    
    \Log::info('CallAnswer received:', [
        'answer_type' => $answer['type'] ?? 'MISSING',
        'has_sdp' => !empty($answer['sdp']),
        'sdp_length' => strlen($answer['sdp'] ?? ''),
        'receiverId' => $request->receiverId
    ]);

    broadcast(new CallAnswer(
        $answer,
        auth()->id(),
        $request->receiverId
    ))->toOthers();

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