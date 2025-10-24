<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-ai', function () {
    return view('test-ai');
})->name('test-ai');
