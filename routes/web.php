<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ZonaController;
use App\Http\Controllers\GeosegmentoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BrickController;
use App\Http\Controllers\Auth\AzureAuthController;

// Rutas SSO (Single Sign-On) - Deben estar antes del middleware guest
Route::prefix('sso')->name('sso.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\SSOController::class, 'login'])->name('login');
    Route::get('/logout', [\App\Http\Controllers\SSOController::class, 'logout'])->name('logout');
});

// Rutas de autenticación (solo para invitados)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/auth/azure', [AzureAuthController::class, 'redirectToAzure'])->name('azure.login');
});

Route::get('/auth/azure/callback', [AzureAuthController::class, 'handleAzureCallback'])->name('azure.callback');
Route::post('/logout', [AzureAuthController::class, 'logout'])->name('logout');

// Rutas protegidas con autenticación
Route::middleware(['azure.auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });

// Rutas de Dashboard
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('chartData');
    Route::get('/estadisticas/{idCiclo}', [DashboardController::class, 'getEstadisticasCiclo'])->name('estadisticas');
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

// Rutas API de Empleados
Route::prefix('api/empleados')->name('api.empleados.')->group(function () {
    Route::get('/search', [\App\Http\Controllers\Api\EmpleadoController::class, 'search'])->name('search');
    Route::get('/{id}', [\App\Http\Controllers\Api\EmpleadoController::class, 'show'])->name('show')->where('id', '[0-9]+');
});

// Rutas de Geosegmentos
Route::prefix('geosegmentos')->name('geosegmentos.')->group(function () {
    Route::get('/', [GeosegmentoController::class, 'index'])->name('index');
    Route::get('/{id}', [GeosegmentoController::class, 'show'])->name('show');
    Route::get('/{id}/ubigeos-list', [GeosegmentoController::class, 'getUbigeosList'])->name('ubigeosList');
    Route::post('/', [GeosegmentoController::class, 'store'])->name('store');
    Route::post('/{id}/ubigeos', [GeosegmentoController::class, 'assignUbigeos'])->name('assignUbigeos');
    
    // Operaciones masivas
    Route::post('/bulk-delete', [GeosegmentoController::class, 'bulkDelete'])->name('bulkDelete');
    Route::post('/clone-from-zones', [GeosegmentoController::class, 'cloneFromZones'])->name('cloneFromZones');
});

// Rutas API
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/ubigeos/search', [\App\Http\Controllers\Api\UbigeoController::class, 'search'])->name('ubigeos.search');
    Route::get('/canales', [\App\Http\Controllers\Api\CanalController::class, 'index'])->name('canales.index');
    Route::get('/geosegmentos', [\App\Http\Controllers\Api\GeosegmentoController::class, 'index'])->name('geosegmentos.index');
    Route::get('/zonas', [\App\Http\Controllers\Api\ZonaController::class, 'index'])->name('zonas.index');
    Route::get('/zonas/count-unique-geosegmentos', [\App\Http\Controllers\Api\ZonaController::class, 'countUniqueGeosegmentos'])->name('zonas.countUniqueGeosegmentos');
    Route::get('/zonas/by-linea', [\App\Http\Controllers\Api\ZonaController::class, 'getZonasByLinea'])->name('zonas.byLinea');
    Route::get('/lineas', [\App\Http\Controllers\Api\LineaController::class, 'index'])->name('lineas.index');
    Route::get('/bricks/available', [\App\Http\Controllers\Api\BrickController::class, 'available'])->name('bricks.available');
    Route::get('/bricks/filters', [\App\Http\Controllers\Api\BrickFilterController::class, 'getFilters'])->name('bricks.filters');
    Route::get('/franqlineas', [\App\Http\Controllers\Api\FranqLineaController::class, 'index'])->name('franqlineas.index');
});

// Rutas de Zonas
Route::prefix('zonas')->name('zonas.')->group(function () {
    Route::get('/', [ZonaController::class, 'index'])->name('index');
    Route::get('/{id}', [ZonaController::class, 'show'])->name('show');
    Route::get('/{id}/detalles', [ZonaController::class, 'detalles'])->name('detalles');
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
    
    // Agregar representante médico a una zona
    Route::post('/{id}/representantes', [ZonaController::class, 'addRepresentanteToZone'])->name('representantes.add');
    
    // Quitar representante médico con registro de ausencia
    Route::post('/representantes/{idFuerza}/remove-with-ausencia', [ZonaController::class, 'removeRepresentanteWithAusencia'])->name('representantes.removeWithAusencia');
    
    // Obtener líneas de una zona
    Route::get('/{id}/lineas', [ZonaController::class, 'getLineasZona'])->name('lineas');
    
    // Unificar líneas
    Route::post('/unificar-linea', [ZonaController::class, 'unificarLinea'])->name('unificarLinea');
    
    // Reactivar empleado con licencia
    Route::post('/empleados/{idEmpleado}/reactivar-licencia', [ZonaController::class, 'reactivarEmpleadoLicencia'])->name('empleados.reactivarLicencia');
    
    // Cambiar supervisor
    Route::post('/empleados/{idZonaEmp}/cambiar-supervisor', [ZonaController::class, 'cambiarSupervisor'])->name('empleados.cambiarSupervisor');
    
    // Cambiar representante
    Route::post('/representantes/{idFuerza}/cambiar-representante', [ZonaController::class, 'cambiarRepresentante'])->name('representantes.cambiarRepresentante');
    
    // Agregar geosegmento a una zona
    Route::post('/{id}/geosegmentos', [ZonaController::class, 'addGeosegmentToZone'])->name('geosegmentos.add');
    
    // Operaciones masivas de geosegmentos
    Route::post('/{id}/geosegmentos/bulk-remove', [ZonaController::class, 'bulkRemoveGeosegmentos'])->name('geosegmentos.bulkRemove');
    Route::post('/{id}/geosegmentos/copy-from-zones', [ZonaController::class, 'copyGeosegmentosFromZones'])->name('geosegmentos.copyFromZones');
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

// Rutas de Bricks
Route::prefix('bricks')->name('bricks.')->group(function () {
    Route::get('/', [\App\Http\Controllers\BrickController::class, 'index'])->name('index');
    
    // Rutas de reasignación de bricks (API para modal)
    Route::get('/reasignacion/get-bricks', [\App\Http\Controllers\BrickReasignacionController::class, 'getBricksGeosegmento'])->name('reasignacion.get-bricks');
    Route::get('/reasignacion/get-destinos', [\App\Http\Controllers\BrickReasignacionController::class, 'getGeosegmentosDestino'])->name('reasignacion.get-destinos');
    Route::post('/reasignacion/reasignar', [\App\Http\Controllers\BrickReasignacionController::class, 'reasignarBricks'])->name('reasignacion.reasignar');
});

// Rutas de FranqLinea
Route::prefix('franqlinea')->name('franqlinea.')->group(function () {
    Route::get('/', [\App\Http\Controllers\FranqLineaController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\FranqLineaController::class, 'store'])->name('store');
    Route::put('/{id}/estado', [\App\Http\Controllers\FranqLineaController::class, 'updateEstado'])->name('updateEstado');
});

}); // Fin del grupo de rutas protegidas
