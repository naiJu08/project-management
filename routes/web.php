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

    broadcast(new CallOffer($request->offer));

    return response()->json(['status' => 'sent']);

});

Route::post('/send-answer', function (Request $request) {

    broadcast(new CallAnswer($request->answer));

    return response()->json(['status' => 'sent']);

});