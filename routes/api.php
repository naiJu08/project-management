<?php

use App\Http\Controllers\Api\BacklogController;
use App\Http\Controllers\Api\BacklogItemController;
use App\Http\Controllers\Api\WikiController;
use App\Http\Controllers\Api\WikiCommentController;
use App\Http\Controllers\Api\WikiSignoffController;
use App\Http\Controllers\Api\ClientWikiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Wiki API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('projects/{project}')->group(function () {
        // Wiki endpoints
        Route::get('/wiki', [WikiController::class, 'index']);
        Route::post('/wiki', [WikiController::class, 'store']);
        
        // Backlog endpoints (legacy)
        Route::get('/backlog', [BacklogController::class, 'index']);
        Route::post('/sprints', [BacklogController::class, 'createSprint']);
        
        // Backlog Items endpoints (new hierarchical system)
        Route::get('/backlog-items', [BacklogItemController::class, 'index']);
        Route::get('/backlog-items/type/{type}', [BacklogItemController::class, 'byType']);
        Route::get('/backlog-items/sprint/{sprintId}', [BacklogItemController::class, 'bySprint']);
        Route::get('/backlog-items/backlog-only', [BacklogItemController::class, 'backlogOnly']);
        Route::post('/backlog-items', [BacklogItemController::class, 'store']);
        Route::post('/backlog-items/bulk-update', [BacklogItemController::class, 'bulkUpdate']);
    });
    
    // Wiki page operations
    Route::prefix('wiki')->group(function () {
        Route::get('/{wikiPage}', [WikiController::class, 'show']);
        Route::put('/{wikiPage}', [WikiController::class, 'update']);
        Route::delete('/{wikiPage}', [WikiController::class, 'destroy']);
        
        // Comments
        Route::get('/{wikiPage}/comments', [WikiCommentController::class, 'index']);
        Route::post('/{wikiPage}/comments', [WikiCommentController::class, 'store']);
        
        // Sign-offs
        Route::get('/{wikiPage}/signoffs', [WikiSignoffController::class, 'index']);
        Route::post('/{wikiPage}/signoff', [WikiSignoffController::class, 'store']);
    });
    
    // Comment operations
    Route::delete('/comments/{comment}', [WikiCommentController::class, 'destroy']);
    
    // Backlog Item operations
    Route::prefix('backlog-items')->group(function () {
        Route::get('/{backlogItem}', [BacklogItemController::class, 'show']);
        Route::put('/{backlogItem}', [BacklogItemController::class, 'update']);
        Route::delete('/{backlogItem}', [BacklogItemController::class, 'destroy']);
        Route::put('/{backlogItem}/move', [BacklogItemController::class, 'move']);
        Route::put('/{backlogItem}/assign-sprint', [BacklogItemController::class, 'assignToSprint']);
        
        // Comments on backlog items
        Route::get('/{backlogItem}/comments', [BacklogItemController::class, 'comments']);
        Route::post('/{backlogItem}/comments', [BacklogItemController::class, 'addComment']);
        
        // History
        Route::get('/{backlogItem}/history', [BacklogItemController::class, 'history']);
    });
    
    // Backlog item comment operations
    Route::delete('/backlog-comments/{comment}', [BacklogItemController::class, 'deleteComment']);
    
    // Task operations
    Route::prefix('tasks')->group(function () {
        Route::put('/{ticket}/status', [BacklogController::class, 'updateTaskStatus']);
        Route::put('/{ticket}/move-to-sprint', [BacklogController::class, 'moveToSprint']);
        Route::put('/{ticket}/move-to-backlog', [BacklogController::class, 'moveToBacklog']);
    });
    
    // Client Wiki View
    Route::prefix('client')->group(function () {
        Route::get('/projects/{project}/wiki', [ClientWikiController::class, 'index']);
        Route::get('/wiki/{wikiPage}', [ClientWikiController::class, 'show']);
    });
});
