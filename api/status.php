<?php
/**
 * MoneyFlow - API Status Endpoint
 * Endpoint para integración con n8n y otros servicios
 * 
 * USO: GET https://tudominio.com/api/status.php
 * 
 * RESPUESTA JSON:
 * {
 *   "success": true,
 *   "timestamp": "2026-03-28 10:30:00",
 *   "data": {
 *     "saldo_actual": 3500000,
 *     "gourmet_disponible": 400000,
 *     "ahorro_actual": 2300000,
 *     "porcentaje_ahorro": 191.67,
 *     "estado": "OK",
 *     "gastos_efectivo": 787264,
 *     "gastos_gourmet": 161290,
 *     "alerta": false,
 *     "mensaje": "Tu situación financiera es saludable"
 *   }
 * }
 */

// Headers CORS (permitir acceso desde n8n)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

try {
    // Obtener estado financiero
    $estado = calcularEstadoFinanciero();
    
    if (!$estado) {
        throw new Exception('No se pudo obtener el estado financiero');
    }
    
    // Determinar si hay alerta
    $alerta = ($estado['estado'] === 'ALERTA' || $estado['estado'] === 'ALERTA_AVANZADA');
    
    // Generar mensaje personalizado
    $mensaje = generarMensajeEstado($estado);
    
    // Construir respuesta
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'saldo_actual' => floatval($estado['saldo_actual']),
            'gourmet_disponible' => floatval($estado['gourmet_disponible']),
            'ahorro_actual' => floatval($estado['ahorro_actual']),
            'porcentaje_ahorro' => floatval($estado['porcentaje_ahorro']),
            'estado' => $estado['estado'],
            'gastos_efectivo' => floatval($estado['gastos_efectivo']),
            'gastos_gourmet' => floatval($estado['gastos_gourmet']),
            'gastos_totales' => floatval($estado['gastos_totales']),
            'alerta' => $alerta,
            'mensaje' => $mensaje,
            'analisis_ritmo' => [
                'dia_actual' => $estado['analisis_ritmo']['dia_actual'],
                'dias_totales' => $estado['analisis_ritmo']['dias_totales'],
                'porcentaje_periodo' => floatval($estado['analisis_ritmo']['porcentaje_periodo']),
                'gasto_esperado' => floatval($estado['analisis_ritmo']['gasto_esperado']),
                'gasto_real' => floatval($estado['analisis_ritmo']['gasto_real']),
                'alerta_avanzada' => $estado['analisis_ritmo']['alerta_avanzada']
            ]
        ]
    ];
    
    // Enviar respuesta
    enviarJSON($response, 200);
    
} catch (Exception $e) {
    // Error en la API
    $response = [
        'success' => false,
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage()
    ];
    
    enviarJSON($response, 500);
}

/**
 * Generar mensaje personalizado según el estado
 * @param array $estado
 * @return string
 */
function generarMensajeEstado($estado) {
    $saldo = $estado['saldo_actual'];
    $ahorro = $estado['ahorro_actual'];
    $estadoActual = $estado['estado'];
    
    if ($estadoActual === 'ALERTA_AVANZADA') {
        return "⚠️ ALERTA: Estás gastando más rápido de lo esperado. " .
               "Te quedan " . formatearMoneda($saldo) . ". " .
               "Revisa tus gastos para cumplir tu objetivo.";
    }
    
    if ($estadoActual === 'ALERTA') {
        return "⚠️ PRECAUCIÓN: Tu saldo está por debajo del mínimo recomendado. " .
               "Saldo actual: " . formatearMoneda($saldo) . ".";
    }
    
    if ($ahorro >= $estado['objetivo_ahorro']) {
        return "🎉 ¡EXCELENTE! Ya cumpliste tu objetivo de ahorro. " .
               "Ahorro actual: " . formatearMoneda($ahorro) . ".";
    }
    
    $faltante = $estado['objetivo_ahorro'] - $ahorro;
    return "✅ Tu situación financiera es saludable. " .
           "Saldo: " . formatearMoneda($saldo) . ". " .
           "Faltan " . formatearMoneda($faltante) . " para tu objetivo.";
}
