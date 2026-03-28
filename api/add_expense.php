<?php
/**
 * MoneyFlow - API Add Expense Endpoint
 * Endpoint para registrar gastos desde n8n u otros servicios
 * 
 * USO: POST https://tudominio.com/api/add_expense.php
 * 
 * BODY (JSON):
 * {
 *   "fecha": "2026-03-28",
 *   "tipo": "variable",
 *   "categoria": "supermercado",
 *   "descripcion": "Compra semanal",
 *   "monto": 150000,
 *   "metodo": "efectivo"
 * }
 * 
 * RESPUESTA:
 * {
 *   "success": true,
 *   "timestamp": "2026-03-28 10:30:00",
 *   "data": {
 *     "id": 123,
 *     "mensaje": "Gasto registrado exitosamente"
 *   }
 * }
 */

// Headers CORS
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

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarJSON([
        'success' => false,
        'error' => 'Método no permitido. Usa POST.'
    ], 405);
}

try {
    // Obtener datos del body
    $json = file_get_contents('php://input');
    $datos = json_decode($json, true);
    
    if (!$datos) {
        throw new Exception('Datos JSON inválidos');
    }
    
    // Validar campos requeridos
    $camposRequeridos = ['fecha', 'tipo', 'categoria', 'descripcion', 'monto', 'metodo'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($datos[$campo]) || $datos[$campo] === '') {
            throw new Exception("Campo requerido faltante: {$campo}");
        }
    }
    
    // Validar monto
    if (!is_numeric($datos['monto']) || $datos['monto'] <= 0) {
        throw new Exception('El monto debe ser un número mayor a 0');
    }
    
    // Validar tipo
    if (!in_array($datos['tipo'], ['fijo', 'variable'])) {
        throw new Exception('Tipo inválido. Debe ser: fijo o variable');
    }
    
    // Validar categoría
    $categoriasValidas = array_keys(CATEGORIAS);
    if (!in_array($datos['categoria'], $categoriasValidas)) {
        throw new Exception('Categoría inválida. Debe ser: ' . implode(', ', $categoriasValidas));
    }
    
    // Validar método
    if (!in_array($datos['metodo'], ['efectivo', 'gourmet'])) {
        throw new Exception('Método inválido. Debe ser: efectivo o gourmet');
    }
    
    // Validar fecha
    $fecha = DateTime::createFromFormat('Y-m-d', $datos['fecha']);
    if (!$fecha) {
        throw new Exception('Formato de fecha inválido. Usa: YYYY-MM-DD');
    }
    
    // Registrar gasto
    $gastoId = registrarGasto($datos);
    
    if (!$gastoId) {
        throw new Exception('Error al guardar el gasto en la base de datos');
    }
    
    // Respuesta exitosa
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'id' => intval($gastoId),
            'mensaje' => 'Gasto registrado exitosamente',
            'gasto' => [
                'fecha' => $datos['fecha'],
                'categoria' => $datos['categoria'],
                'descripcion' => $datos['descripcion'],
                'monto' => floatval($datos['monto']),
                'metodo' => $datos['metodo']
            ]
        ]
    ];
    
    enviarJSON($response, 201);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage()
    ];
    
    enviarJSON($response, 400);
}
