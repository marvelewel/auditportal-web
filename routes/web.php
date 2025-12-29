<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintNotulenController;

// Redirect root URL langsung ke panel admin
Route::redirect('/', '/admin');

Route::middleware(['auth'])->group(function () {
    // ... route lain ...
    
    // ✅ Route Export PDF
    Route::get('/rko/{record}/print-notulen', [PrintNotulenController::class, 'download'])
        ->name('rko.print-notulen');
});