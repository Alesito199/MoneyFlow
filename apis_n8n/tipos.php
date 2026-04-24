<?php
/**
 * MoneyFlow - API N8N - Tipos de Gasto
 * Obtener lista de tipos de gasto disponibles
 */

require_once __DIR__ . '/config.php';

// Validar API Key
validateApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Método no permitido. Solo GET', 405);
}

$tipos = [
    [
        'id' => 'necesario',
        'nombre' => 'Necesario',
        'icono' => '✅',
        'descripcion' => 'Gastos esenciales e imprescindibles',
        'prioridad' => 1
    ],
    [
        'id' => 'opcional',
        'nombre' => 'Opcional',
        'icono' => '⭕',
        'descripcion' => 'Gastos que se pueden evitar',
        'prioridad' => 2
    ],
    [
        'id' => 'emergencia',
        'nombre' => 'Emergencia',
        'icono' => '🚨',
        'descripcion' => 'Gastos urgentes e imprevistos',
        'prioridad' => 0
    ]
];

ApiResponse::success([
    'tipos' => $tipos,
    'total' => count($tipos)
]);
