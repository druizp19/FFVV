# Módulo de Historial

## Descripción
El módulo de historial registra automáticamente todos los cambios realizados en las entidades principales del sistema, organizados por ciclo. Permite auditar y rastrear modificaciones con información detallada sobre qué cambió, quién lo cambió y cuándo.

## Características

### 1. Registro Automático
- Captura automática de creaciones, actualizaciones y eliminaciones
- Almacena datos anteriores y nuevos para comparación
- Registra usuario, fecha/hora e IP del cambio

### 2. Filtros Avanzados
- Filtrar por ciclo
- Filtrar por entidad (Zona, Empleado, Producto, etc.)
- Filtrar por acción (Crear, Actualizar, Eliminar, etc.)
- Búsqueda por texto en descripción
- Filtro por rango de fechas

### 3. Visualización Timeline
- Diseño moderno tipo timeline
- Códigos de color por tipo de acción
- Información detallada de cada cambio
- Modal con comparación de datos anteriores y nuevos

## Arquitectura

### Estructura de Archivos
```
app/
├── Models/
│   └── Historial.php                 # Modelo principal
├── Repositories/
│   └── HistorialRepository.php       # Capa de acceso a datos
├── Services/
│   └── HistorialService.php          # Lógica de negocio
├── Http/Controllers/
│   └── HistorialController.php       # Controlador
└── Traits/
    └── RegistraHistorial.php         # Trait para registro automático

resources/
├── views/historial/
│   └── index.blade.php               # Vista principal
├── css/
│   └── historial.css                 # Estilos
└── js/
    └── historial.js                  # JavaScript

database/migrations/
└── 2024_01_15_000000_create_historial_table.php
```

### Base de Datos

#### Tabla: ODS.TAB_HISTORIAL
```sql
- idHistorial (PK)
- idCiclo (FK a TAB_CICLO)
- entidad (varchar) - Nombre de la entidad
- idEntidad (int) - ID del registro afectado
- accion (varchar) - Tipo de acción
- descripcion (text) - Descripción del cambio
- datosAnteriores (json) - Estado anterior
- datosNuevos (json) - Estado nuevo
- idUsuario (FK a users)
- fechaHora (timestamp)
```

## Uso

### 1. Habilitar Registro Automático en un Modelo

Para que un modelo registre automáticamente sus cambios en el historial:

```php
<?php

namespace App\Models;

use App\Traits\RegistraHistorial;
use Illuminate\Database\Eloquent\Model;

class MiModelo extends Model
{
    use RegistraHistorial;
    
    // ... resto del modelo
}
```

### 2. Registro Manual

Para registrar eventos manualmente:

```php
use App\Services\HistorialService;

$historialService = app(HistorialService::class);

$historialService->registrarEvento(
    entidad: 'Zona',
    accion: 'Asignar',
    descripcion: 'Se asignó el empleado Juan Pérez a la Zona Norte',
    opciones: [
        'idCiclo' => 1,
        'idEntidad' => 5,
        'datosNuevos' => ['idEmpleado' => 10, 'idZona' => 5]
    ]
);
```

### 3. Consultar Historial

#### Por Ciclo
```php
$historial = $historialService->getHistorialPorCiclo($idCiclo);
```

#### Por Entidad
```php
$historial = $historialService->getHistorialPorEntidad('Zona', $idZona);
```

#### Con Filtros
```php
$filtros = [
    'ciclo' => 1,
    'entidad' => 'Empleado',
    'accion' => 'Crear',
    'desde' => '2024-01-01',
    'hasta' => '2024-12-31',
    'search' => 'Juan'
];

$historial = $historialService->getHistorial($filtros, 15);
```

## API Endpoints

### GET /historial
Lista el historial con filtros y paginación

**Query Parameters:**
- `ciclo` - ID del ciclo
- `entidad` - Nombre de la entidad
- `accion` - Tipo de acción
- `desde` - Fecha inicio (Y-m-d)
- `hasta` - Fecha fin (Y-m-d)
- `search` - Búsqueda en descripción

### GET /historial/ciclo/{idCiclo}
Obtiene el historial de un ciclo específico

### GET /historial/entidad/{entidad}/{idEntidad}
Obtiene el historial de una entidad específica

### GET /historial/estadisticas/{idCiclo}
Obtiene estadísticas del historial por ciclo

### POST /historial/registrar
Registra un evento manualmente

**Body:**
```json
{
    "entidad": "Zona",
    "accion": "Crear",
    "descripcion": "Se creó la Zona Norte",
    "idCiclo": 1,
    "idEntidad": 5,
    "datosNuevos": {...}
}
```

## Tipos de Acciones

- **Crear**: Creación de nuevos registros
- **Actualizar**: Modificación de registros existentes
- **Eliminar**: Eliminación o desactivación de registros
- **Asignar**: Asignación de relaciones (ej: empleado a zona)
- **Desasignar**: Eliminación de relaciones
- **Activar**: Activación de registros
- **Desactivar**: Desactivación de registros

## Personalización

### Personalizar Descripción del Historial

Sobrescribe el método `getDescripcionHistorial()` en tu modelo:

```php
protected function getDescripcionHistorial(): string
{
    return $this->nombre . ' - ' . $this->codigo;
}
```

### Personalizar Datos Guardados

Sobrescribe el método `getDatosParaHistorial()`:

```php
protected function getDatosParaHistorial(): array
{
    return [
        'nombre' => $this->nombre,
        'estado' => $this->estado->estado,
        'ciclo' => $this->ciclo->ciclo,
    ];
}
```

### Personalizar ID de Ciclo

Sobrescribe el método `getIdCicloParaHistorial()`:

```php
protected function getIdCicloParaHistorial(): ?int
{
    return $this->miCampoPersonalizado;
}
```

## Migración

Para crear la tabla del historial:

```bash
php artisan migrate
```

## Consideraciones

1. **Performance**: El registro automático agrega una pequeña sobrecarga. Para operaciones masivas, considera deshabilitarlo temporalmente.

2. **Almacenamiento**: Los datos JSON pueden crecer. Considera implementar una política de limpieza para registros antiguos.

3. **Privacidad**: Asegúrate de no registrar información sensible (contraseñas, tokens, etc.). El trait excluye automáticamente campos comunes.

4. **Errores**: Los errores al registrar en el historial no interrumpen la operación principal, solo se registran en logs.

## Mantenimiento

### Limpiar Historial Antiguo

```php
// Eliminar registros mayores a 1 año
Historial::where('fechaHora', '<', now()->subYear())->delete();
```

### Exportar Historial

```php
// Exportar a CSV
$historial = Historial::porCiclo($idCiclo)->get();
// ... lógica de exportación
```

## Soporte

Para reportar problemas o sugerencias, contacta al equipo de desarrollo.
