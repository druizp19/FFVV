<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CicloController;

Route::get('/', function () {
    return redirect()->route('ciclos.index');
});

// Rutas de Ciclos
Route::prefix('ciclos')->name('ciclos.')->group(function () {
    Route::get('/', [CicloController::class, 'index'])->name('index');
    Route::post('/', [CicloController::class, 'store'])->name('store');
    Route::get('/ultimo', [CicloController::class, 'getUltimo'])->name('ultimo');
    Route::get('/{id}', [CicloController::class, 'show'])->name('show');
    Route::put('/{id}', [CicloController::class, 'update'])->name('update');
    Route::delete('/{id}', [CicloController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/copiar', [CicloController::class, 'copy'])->name('copy');
    Route::post('/{id}/copiar-completo', [CicloController::class, 'copiarCompleto'])->name('copyComplete');
});
