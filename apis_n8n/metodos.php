<?php
/**
 * MoneyFlow - API N8N - Métodos de Pago
 * Obtener lista de métodos de pago disponibles
 */

require_once __DIR__ . '/config.php';

// Validar API Key
validateApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Método no permitido. Solo GET', 405);
}

$metodos = [
    [
        'id' => 'efectivo',
        'nombre' => 'Efectivo',
        'icono' => '💵',
        'descripcion' => 'Pago en efectivo o débito'
    ],
    [
        'id' => 'gourmet',
        'nombre' => 'Gourmet',
        'icono' => '🍽️',
        'descripcion' => 'Tarjeta de beneficios/gourmet'
    ]
];

ApiResponse::success([
    'metodos' => $metodos,
    'total' => count($metodos)
]);
