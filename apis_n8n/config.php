<?php
/**
 * MoneyFlow - API N8N Config
 * Configuración y autenticación para APIs REST
 */

// Permitir acceso desde cualquier origen (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Content-Type: application/json; charset=UTF-8');

// Manejar solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Clase para manejar respuestas JSON
 */
class ApiResponse {
    public static function success($data = null, $message = 'Operación exitosa') {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    public static function error($message = 'Error en la operación', $code = 400, $details = null) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'details' => $details
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    public static function notFound($message = 'Recurso no encontrado') {
        self::error($message, 404);
    }
    
    public static function unauthorized($message = 'No autorizado') {
        self::error($message, 401);
    }
}

/**
 * Validar API Key (simple)
 * Puedes mejorar esto con tokens JWT o OAuth
 */
function validateApiKey() {
    $apiKey = null;
    
    // Buscar API key en headers
    if (isset($_SERVER['HTTP_X_API_KEY'])) {
        $apiKey = $_SERVER['HTTP_X_API_KEY'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $apiKey = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    }
    
    // Por ahora, API key simple (CAMBIAR EN PRODUCCIÓN)
    // Puedes generar keys únicas por usuario y guardarlas en BD
    $validKeys = [
        'moneyflow_n8n_key_2026',  // API Key para N8N
        'telegram_bot_key_2026'     // API Key para Telegram
    ];
    
    if (!in_array($apiKey, $validKeys)) {
        ApiResponse::unauthorized('API Key inválida o faltante');
    }
    
    return true;
}

/**
 * Obtener el cuerpo de la solicitud como array
 */
function getRequestBody() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

/**
 * Validar que un usuario existe
 */
function validateUser($userId, $conn) {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND activo = 1");
    $stmt->execute([$userId]);
    
    if (!$stmt->fetch()) {
        ApiResponse::error('Usuario no encontrado o inactivo', 404);
    }
    
    return true;
}

/**
 * Obtener conexión a la base de datos
 */
function getDbConnection() {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        ApiResponse::error('Error al conectar con la base de datos', 500);
    }
    
    return $conn;
}
