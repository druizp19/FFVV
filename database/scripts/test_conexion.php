<?php
/**
 * Script de prueba de conexión a SQL Server
 * 
 * Ejecutar desde la raíz del proyecto:
 * php database/scripts/test_conexion.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Cargar el framework de Laravel
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n==============================================\n";
echo "  PRUEBA DE CONEXIÓN A SQL SERVER\n";
echo "==============================================\n\n";

try {
    // Obtener la configuración de la base de datos
    $config = config('database.connections.sqlsrv');
    
    echo "📋 Configuración:\n";
    echo "   Host: " . $config['host'] . "\n";
    echo "   Puerto: " . $config['port'] . "\n";
    echo "   Base de datos: " . $config['database'] . "\n";
    echo "   Usuario: " . $config['username'] . "\n\n";
    
    // Intentar conectar
    echo "🔄 Intentando conectar...\n";
    DB::connection()->getPdo();
    echo "✅ Conexión exitosa!\n\n";
    
    // Verificar si la tabla existe
    echo "🔍 Verificando tabla ODS.TAB_CICLO...\n";
    $exists = DB::select("
        SELECT COUNT(*) as existe 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = 'ODS' 
        AND TABLE_NAME = 'TAB_CICLO'
    ");
    
    if ($exists[0]->existe > 0) {
        echo "✅ La tabla ODS.TAB_CICLO existe.\n\n";
        
        // Contar registros
        $count = DB::table('ODS.TAB_CICLO')->count();
        echo "📊 Registros en la tabla: " . $count . "\n\n";
        
        // Mostrar últimos 5 ciclos
        if ($count > 0) {
            echo "📋 Últimos 5 ciclos:\n";
            echo "-------------------------------------------\n";
            
            $ciclos = DB::table('ODS.TAB_CICLO')
                ->orderBy('FechaInicio', 'desc')
                ->limit(5)
                ->get();
            
            foreach ($ciclos as $ciclo) {
                echo sprintf(
                    "ID: %d | Inicio: %s | Fin: %s | Días: %d\n",
                    $ciclo->IdCiclo,
                    $ciclo->FechaInicio,
                    $ciclo->FechaFin,
                    $ciclo->DiasHabiles ?? 0
                );
            }
            echo "\n";
        }
    } else {
        echo "❌ La tabla ODS.TAB_CICLO no existe.\n";
        echo "   Por favor, ejecuta el script: database/scripts/crear_tabla_ciclo.sql\n\n";
    }
    
    echo "==============================================\n";
    echo "  ✅ PRUEBA COMPLETADA EXITOSAMENTE\n";
    echo "==============================================\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    echo "Posibles soluciones:\n";
    echo "1. Verifica que SQL Server esté ejecutándose\n";
    echo "2. Verifica las credenciales en el archivo .env\n";
    echo "3. Verifica que las extensiones php_pdo_sqlsrv y php_sqlsrv estén habilitadas\n";
    echo "4. Si usas SQL Server Express, el host puede ser: localhost\\SQLEXPRESS\n\n";
    
    echo "==============================================\n";
    echo "  ❌ PRUEBA FALLIDA\n";
    echo "==============================================\n\n";
    
    exit(1);
}

