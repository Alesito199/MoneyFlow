<?php
/**
 * MoneyFlow - API N8N - Categorías
 * Obtener lista de categorías disponibles
 */

require_once __DIR__ . '/config.php';

// Validar API Key
validateApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Método no permitido. Solo GET', 405);
}

$categorias = [
    [
        'id' => 'comida',
        'nombre' => 'Comida',
        'icono' => '🍔',
        'descripcion' => 'Alimentos, restaurantes, supermercado'
    ],
    [
        'id' => 'transporte',
        'nombre' => 'Transporte',
        'icono' => '🚗',
        'descripcion' => 'Gasolina, Uber, transporte público'
    ],
    [
        'id' => 'salud',
        'nombre' => 'Salud',
        'icono' => '⚕️',
        'descripcion' => 'Medicinas, consultas médicas, farmacia'
    ],
    [
        'id' => 'entretenimiento',
        'nombre' => 'Entretenimiento',
        'icono' => '🎮',
        'descripcion' => 'Cine, juegos, streaming, diversión'
    ],
    [
        'id' => 'servicios',
        'nombre' => 'Servicios',
        'icono' => '💡',
        'descripcion' => 'Luz, agua, internet, teléfono'
    ],
    [
        'id' => 'otros',
        'nombre' => 'Otros',
        'icono' => '📦',
        'descripcion' => 'Otros gastos no categorizados'
    ]
];

ApiResponse::success([
    'categorias' => $categorias,
    'total' => count($categorias)
]);
