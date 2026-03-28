<?php
/**
 * MoneyFlow - Constantes del Sistema
 */

// Zona horaria
date_default_timezone_set('America/Asuncion');

// Configuración de errores (cambiar en producción)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('API_PATH', ROOT_PATH . '/api');

// Límites y alertas
define('ALERTA_SALDO_MINIMO', 2000000); // 2 millones de guaraníes
define('MONEDA', 'Gs');
define('FORMATO_FECHA', 'd/m/Y');
define('FORMATO_FECHA_SQL', 'Y-m-d');

// Categorías permitidas
define('CATEGORIAS', [
    'electricidad' => 'Electricidad',
    'transporte' => 'Transporte',
    'supermercado' => 'Supermercado',
    'servicios' => 'Servicios',
    'otros' => 'Otros'
]);

// Tipos de gasto
define('TIPOS_GASTO', [
    'fijo' => 'Fijo',
    'variable' => 'Variable'
]);

// Métodos de pago
define('METODOS_PAGO', [
    'efectivo' => 'Efectivo',
    'gourmet' => 'Tarjeta Gourmet'
]);
