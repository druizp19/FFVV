# 📋 Sistema de Clonación de Ciclos

## 🎯 Descripción

Sistema completo para clonar un ciclo con **todas sus relaciones** y **nuevos IDs automáticos**. 

Cuando creas un nuevo ciclo, se copian automáticamente:
- ✅ **Productos** (con nuevos IDs secuenciales)
- ✅ **ZonaEmp** (asignaciones zona-empleado)
- ✅ **ZonaGeo** (asignaciones zona-geosegmento)
- ✅ **FuerzaVenta** (con referencias a los nuevos productos)

---

## 🔧 Cómo funciona

### Ejemplo práctico:

**Ciclo Original (ID: 1)**
- 1000 Productos: IDs del 1 al 1000
- 50 ZonasEmpleados
- 30 ZonasGeosegmentos
- 500 FuerzasVenta

**Ciclo Clonado (ID: 2)**
- 1000 Productos: IDs del 1001 al 2000 ⭐ (nuevos IDs)
- 50 ZonasEmpleados: IDs del 51 al 100 ⭐ (nuevos IDs)
- 30 ZonasGeosegmentos: IDs del 31 al 60 ⭐ (nuevos IDs)
- 500 FuerzasVenta: IDs del 501 al 1000 ⭐ (con referencias actualizadas a los nuevos productos)

---

## 🚀 Uso desde el Controller

### Endpoint REST API

```http
POST /ciclos/{id}/copiar-completo
Content-Type: application/json

{
  "ciclo": "Ciclo 2025-02",
  "fechaInicio": "2025-02-01",
  "fechaFin": "2025-02-28"
}
```

### Respuesta Exitosa

```json
{
  "success": true,
  "message": "Ciclo clonado exitosamente. Se creó el ciclo 2 con todas sus relaciones.",
  "data": {
    "ciclo": {
      "idCiclo": 2,
      "ciclo": "Ciclo 2025-02",
      "fechaInicio": "2025-02-01",
      "fechaFin": "2025-02-28",
      "diasHabiles": 27
    },
    "productos_clonados": 1000,
    "estadisticas": {
      "productos": 1000,
      "zonas_empleados": 50,
      "zonas_geosegmentos": 30,
      "fuerzas_venta": 500
    }
  }
}
```

---

## 💻 Uso desde el Service

### Desde cualquier parte de tu aplicación

```php
use App\Services\CicloService;

class MiController extends Controller
{
    protected $cicloService;

    public function __construct(CicloService $cicloService)
    {
        $this->cicloService = $cicloService;
    }

    public function clonarCiclo()
    {
        $result = $this->cicloService->copiarCicloCompleto(
            idCicloOriginal: 1,
            datosCicloNuevo: [
                'ciclo' => 'Ciclo 2025-02',
                'fechaInicio' => '2025-02-01',
                'fechaFin' => '2025-02-28'
            ]
        );

        if ($result['success']) {
            // Éxito!
            $cicloNuevo = $result['data']['ciclo'];
            $stats = $result['data']['estadisticas'];
            
            echo "Nuevo ciclo: {$cicloNuevo->idCiclo}";
            echo "Productos clonados: {$stats['productos']}";
        } else {
            // Error
            echo $result['message'];
        }
    }
}
```

---

## 🔒 Características de Seguridad

### Transacciones de Base de Datos

Todo el proceso se ejecuta en una **transacción**:
- Si algo falla, se hace **rollback** automático
- No quedan datos inconsistentes
- Todo o nada

### Validaciones

1. ✅ Verifica que el ciclo original existe
2. ✅ Valida fechas (fin > inicio)
3. ✅ Verifica que no haya solapamiento de fechas
4. ✅ Solo clona productos que existen
5. ✅ Mantiene integridad referencial

### Logs

Todos los eventos se registran en `storage/logs/laravel.log`:

```log
[2024-01-15 10:30:00] local.INFO: Ciclo 2 creado. Iniciando clonación desde ciclo 1
[2024-01-15 10:30:05] local.INFO: Productos clonados: 1000
[2024-01-15 10:30:06] local.INFO: ZonasEmpleados clonadas: 50
[2024-01-15 10:30:07] local.INFO: ZonasGeosegmentos clonadas: 30
[2024-01-15 10:30:10] local.INFO: FuerzasVenta clonadas: 500
[2024-01-15 10:30:10] local.INFO: Clonación completada exitosamente. Ciclo 2 creado con todas sus relaciones.
```

---

## 📊 Detalles Técnicos

### Método: `copiarCicloCompleto()`

**Ubicación:** `App\Services\CicloService`

**Parámetros:**
- `int $idCicloOriginal` - ID del ciclo a clonar
- `array $datosCicloNuevo` - Datos del nuevo ciclo (ciclo, fechaInicio, fechaFin)

**Retorna:** `array`
```php
[
    'success' => bool,
    'message' => string,
    'data' => [
        'ciclo' => Ciclo,
        'productos_clonados' => int,
        'estadisticas' => [
            'productos' => int,
            'zonas_empleados' => int,
            'zonas_geosegmentos' => int,
            'fuerzas_venta' => int
        ]
    ]
]
```

### Proceso Interno

```php
DB::beginTransaction();
try {
    // 1. Crear nuevo ciclo
    $cicloNuevo = Ciclo::create($datos);
    
    // 2. Clonar productos (genera mapeo de IDs)
    $mapeo = clonarProductos($cicloOriginal, $cicloNuevo);
    // $mapeo = [1 => 1001, 2 => 1002, ..., 1000 => 2000]
    
    // 3. Clonar zona-empleados
    clonarZonasEmpleados($cicloOriginal, $cicloNuevo);
    
    // 4. Clonar zona-geosegmentos
    clonarZonasGeosegmentos($cicloOriginal, $cicloNuevo);
    
    // 5. Clonar fuerza venta (usa el mapeo para actualizar referencias)
    clonarFuerzaVenta($cicloOriginal, $cicloNuevo, $mapeo);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Manejo de error
}
```

---

## ⚠️ Consideraciones

### Campos que se resetean en el nuevo ciclo:

**Productos:**
- ✅ `fechaModificacion` → fecha actual
- ✅ `fechaCierre` → NULL (productos abiertos)

**FuerzaVenta:**
- ✅ `fechaModificacion` → fecha actual
- ✅ `fechaCierre` → NULL (fuerzas abiertas)
- ✅ `idProducto` → nuevo ID del producto clonado

### Lo que NO se clona:

- ❌ Estados específicos de productos cerrados
- ❌ Fechas de cierre de productos/fuerzas
- ❌ Timestamps de modificación antiguos

---

## 🧪 Testing

### Ejemplo de prueba manual

```php
// 1. Crear ciclo con datos
$ciclo1 = Ciclo::create([...]);
$producto1 = Producto::create(['idCiclo' => 1, ...]);
$zonaEmp1 = ZonaEmp::create(['idCiclo' => 1, ...]);

// 2. Clonar
$result = $cicloService->copiarCicloCompleto(1, [
    'ciclo' => 'Ciclo 2',
    'fechaInicio' => '2025-02-01',
    'fechaFin' => '2025-02-28'
]);

// 3. Verificar
$ciclo2 = Ciclo::find(2);
$productos2 = Producto::where('idCiclo', 2)->count();
$zonasEmp2 = ZonaEmp::where('idCiclo', 2)->count();

assert($productos2 === 1); // ✓
assert($zonasEmp2 === 1);  // ✓
```

---

## 📞 Soporte

Para dudas o problemas, revisar los logs en:
```
storage/logs/laravel.log
```

Buscar por:
- `"Ciclo {id} creado"`
- `"Productos clonados"`
- `"Error al clonar ciclo"`

---

## 🎉 ¡Listo!

Tu sistema de clonación está completamente funcional y listo para usar. Cada vez que crees un nuevo ciclo con este método, obtendrás una copia exacta con todos los datos e IDs nuevos.

