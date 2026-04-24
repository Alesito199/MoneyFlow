<?php
/**
 * MoneyFlow - API N8N - Resumen y Estadísticas
 * Consultas agregadas para dashboards
 */

require_once __DIR__ . '/config.php';

// Validar API Key
validateApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Método no permitido. Solo GET', 405);
}

$conn = getDbConnection();

$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    ApiResponse::error('Parámetro user_id es requerido', 400);
}

validateUser($userId, $conn);

// Fechas para filtrar (por defecto mes actual)
$fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-t');

try {
    // Resumen general
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_gastos,
            SUM(monto) as total_gastado,
            AVG(monto) as promedio_gasto,
            MIN(monto) as gasto_minimo,
            MAX(monto) as gasto_maximo
        FROM gastos 
        WHERE user_id = ? 
        AND fecha BETWEEN ? AND ?
    ");
    $stmt->execute([$userId, $fechaDesde, $fechaHasta]);
    $resumenGeneral = $stmt->fetch();
    
    // Gastos por tipo
    $stmt = $conn->prepare("
        SELECT 
            tipo,
            COUNT(*) as cantidad,
            SUM(monto) as total,
            ROUND(SUM(monto) * 100.0 / (SELECT SUM(monto) FROM gastos WHERE user_id = ? AND fecha BETWEEN ? AND ?), 2) as porcentaje
        FROM gastos 
        WHERE user_id = ? 
        AND fecha BETWEEN ? AND ?
        GROUP BY tipo
        ORDER BY total DESC
    ");
    $stmt->execute([$userId, $fechaDesde, $fechaHasta, $userId, $fechaDesde, $fechaHasta]);
    $gastosPorTipo = $stmt->fetchAll();
    
    // Gastos por categoría
    $stmt = $conn->prepare("
        SELECT 
            categoria,
            COUNT(*) as cantidad,
            SUM(monto) as total,
            ROUND(SUM(monto) * 100.0 / (SELECT SUM(monto) FROM gastos WHERE user_id = ? AND fecha BETWEEN ? AND ?), 2) as porcentaje
        FROM gastos 
        WHERE user_id = ? 
        AND fecha BETWEEN ? AND ?
        GROUP BY categoria
        ORDER BY total DESC
    ");
    $stmt->execute([$userId, $fechaDesde, $fechaHasta, $userId, $fechaDesde, $fechaHasta]);
    $gastosPorCategoria = $stmt->fetchAll();
    
    // Gastos por método de pago
    $stmt = $conn->prepare("
        SELECT 
            metodo,
            COUNT(*) as cantidad,
            SUM(monto) as total,
            ROUND(SUM(monto) * 100.0 / (SELECT SUM(monto) FROM gastos WHERE user_id = ? AND fecha BETWEEN ? AND ?), 2) as porcentaje
        FROM gastos 
        WHERE user_id = ? 
        AND fecha BETWEEN ? AND ?
        GROUP BY metodo
        ORDER BY total DESC
    ");
    $stmt->execute([$userId, $fechaDesde, $fechaHasta, $userId, $fechaDesde, $fechaHasta]);
    $gastosPorMetodo = $stmt->fetchAll();
    
    // Gastos por día (últimos 7 días)
    $stmt = $conn->prepare("
        SELECT 
            fecha,
            COUNT(*) as cantidad,
            SUM(monto) as total
        FROM gastos 
        WHERE user_id = ? 
        AND fecha BETWEEN ? AND ?
        GROUP BY fecha
        ORDER BY fecha DESC
        LIMIT 30
    ");
    $stmt->execute([$userId, $fechaDesde, $fechaHasta]);
    $gastosPorDia = $stmt->fetchAll();
    
    // Top 10 gastos más altos
    $stmt = $conn->prepare("
        SELECT 
            id,
            fecha,
            tipo,
            categoria,
            descripcion,
            monto,
            metodo
        FROM gastos 
        WHERE user_id = ? 
        AND fecha BETWEEN ? AND ?
        ORDER BY monto DESC
        LIMIT 10
    ");
    $stmt->execute([$userId, $fechaDesde, $fechaHasta]);
    $topGastos = $stmt->fetchAll();
    
    // Obtener configuración del usuario
    $stmt = $conn->prepare("
        SELECT 
            ingreso_mensual,
            monto_ahorro,
            objetivo_ahorro,
            saldo_inicial
        FROM configuracion 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $configuracion = $stmt->fetch();
    
    // Calcular porcentaje gastado vs ingreso
    $totalGastado = (float)$resumenGeneral['total_gastado'];
    $ingresoMensual = $configuracion ? (float)$configuracion['ingreso_mensual'] : 0;
    $porcentajeGastado = $ingresoMensual > 0 ? round(($totalGastado / $ingresoMensual) * 100, 2) : 0;
    
    ApiResponse::success([
        'periodo' => [
            'desde' => $fechaDesde,
            'hasta' => $fechaHasta
        ],
        'resumen_general' => [
            'total_gastos' => (int)$resumenGeneral['total_gastos'],
            'total_gastado' => (float)$resumenGeneral['total_gastado'],
            'promedio_gasto' => (float)$resumenGeneral['promedio_gasto'],
            'gasto_minimo' => (float)$resumenGeneral['gasto_minimo'],
            'gasto_maximo' => (float)$resumenGeneral['gasto_maximo']
        ],
        'presupuesto' => [
            'ingreso_mensual' => $ingresoMensual,
            'total_gastado' => $totalGastado,
            'porcentaje_gastado' => $porcentajeGastado,
            'disponible' => $ingresoMensual - $totalGastado
        ],
        'gastos_por_tipo' => $gastosPorTipo,
        'gastos_por_categoria' => $gastosPorCategoria,
        'gastos_por_metodo' => $gastosPorMetodo,
        'gastos_por_dia' => $gastosPorDia,
        'top_gastos' => $topGastos
    ]);
    
} catch (Exception $e) {
    ApiResponse::error('Error al generar resumen: ' . $e->getMessage(), 500);
}
