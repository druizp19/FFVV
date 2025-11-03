<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ZonaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\HistorialController;

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

// Rutas de Empleados
Route::prefix('empleados')->name('empleados.')->group(function () {
    Route::get('/', [EmpleadoController::class, 'index'])->name('index');
    Route::get('/all', [EmpleadoController::class, 'getAll'])->name('all');
    Route::get('/search', [EmpleadoController::class, 'search'])->name('search');
    Route::get('/{id}', [EmpleadoController::class, 'show'])->name('show');
    Route::post('/', [EmpleadoController::class, 'store'])->name('store');
    Route::put('/{id}', [EmpleadoController::class, 'update'])->name('update');
    Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->name('destroy');
});

// Rutas de Zonas
Route::prefix('zonas')->name('zonas.')->group(function () {
    Route::get('/', [ZonaController::class, 'index'])->name('index');
    Route::get('/{id}', [ZonaController::class, 'show'])->name('show');
    Route::post('/', [ZonaController::class, 'store'])->name('store');
    Route::put('/{id}', [ZonaController::class, 'update'])->name('update');
    Route::delete('/{id}', [ZonaController::class, 'destroy'])->name('destroy');
    
    // Rutas para obtener asignaciones de una zona
    Route::get('/{id}/empleados', [ZonaController::class, 'getEmpleados'])->name('empleados');
    Route::get('/{id}/geosegmentos', [ZonaController::class, 'getGeosegmentos'])->name('geosegmentos');
    Route::get('/{id}/ubigeos', [ZonaController::class, 'getUbigeos'])->name('ubigeos');
    
    // Desactivar y reactivar geosegmento de una zona
    Route::put('/geosegmentos/{id}/deactivate', [ZonaController::class, 'deactivateGeosegmentFromZone'])->name('geosegmentos.deactivate');
    Route::put('/geosegmentos/{id}/activate', [ZonaController::class, 'activateGeosegmentFromZone'])->name('geosegmentos.activate');
    
    // Desactivar empleado de una zona
    Route::put('/empleados/{id}/deactivate', [ZonaController::class, 'deactivateEmployeeFromZone'])->name('empleados.deactivate');
    
    // Agregar empleado a una zona
    Route::post('/{id}/empleados', [ZonaController::class, 'addEmpleadoToZone'])->name('empleados.add');
    
    // Agregar geosegmento a una zona
    Route::post('/{id}/geosegmentos', [ZonaController::class, 'addGeosegmentToZone'])->name('geosegmentos.add');
});

// Rutas de Productos
Route::prefix('productos')->name('productos.')->group(function () {
    Route::get('/', [ProductoController::class, 'index'])->name('index');
    Route::get('/all', [ProductoController::class, 'getAll'])->name('all');
    Route::get('/{id}', [ProductoController::class, 'show'])->name('show');
    Route::post('/', [ProductoController::class, 'store'])->name('store');
    Route::put('/{id}', [ProductoController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProductoController::class, 'destroy'])->name('destroy');
});

// Rutas de Historial
Route::prefix('historial')->name('historial.')->group(function () {
    Route::get('/', [HistorialController::class, 'index'])->name('index');
    Route::get('/ciclo/{idCiclo}', [HistorialController::class, 'porCiclo'])->name('porCiclo');
    Route::get('/entidad/{entidad}/{idEntidad}', [HistorialController::class, 'porEntidad'])->name('porEntidad');
    Route::get('/estadisticas/{idCiclo}', [HistorialController::class, 'estadisticas'])->name('estadisticas');
    Route::post('/registrar', [HistorialController::class, 'registrar'])->name('registrar');
});
