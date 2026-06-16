<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('offres', OffreController::class);

    Route::post('/offres/{offre}/candidatures', [CandidatureController::class, 'store'])
        ->name('offres.candidatures.store');

    Route::get('/offres/{offre}/analyses/{analyse}', [CandidatureController::class, 'show'])
        ->name('offres.analyses.show');

    Route::post('/offres/{offre}/analyses/{analyse}/assistant', AssistantController::class)
        ->name('offres.analyses.assistant');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
