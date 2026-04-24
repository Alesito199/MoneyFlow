<?php
/**
 * MoneyFlow - API N8N - Usuarios
 * Consultar información de usuarios
 */

require_once __DIR__ . '/config.php';

// Validar API Key
validateApiKey();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ApiResponse::error('Método no permitido. Solo GET', 405);
}

$conn = getApiConnection();

try {
    // Si se solicita un usuario específico
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("
            SELECT 
                id,
                username,
                nombre,
                email,
                rol,
                activo,
                created_at
            FROM usuarios 
            WHERE id = ? AND activo = 1
        ");
        $stmt->execute([$_GET['id']]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            ApiResponse::notFound('Usuario no encontrado');
        }
        
        // Obtener configuración del usuario
        $stmt = $conn->prepare("
            SELECT 
                ingreso_mensual,
                monto_ahorro,
                monto_gourmet,
                objetivo_ahorro
            FROM configuracion 
            WHERE user_id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $configuracion = $stmt->fetch();
        
        $usuario['configuracion'] = $configuracion;
        
        ApiResponse::success($usuario);
    }
    
    // Buscar usuario por username
    if (isset($_GET['username'])) {
        $stmt = $conn->prepare("
            SELECT 
                id,
                username,
                nombre,
                email,
                rol,
                activo,
                created_at
            FROM usuarios 
            WHERE username = ? AND activo = 1
        ");
        $stmt->execute([$_GET['username']]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            ApiResponse::notFound('Usuario no encontrado');
        }
        
        ApiResponse::success($usuario);
    }
    
    // Listar todos los usuarios activos
    $stmt = $conn->prepare("
        SELECT 
            id,
            username,
            nombre,
            email,
            rol,
            created_at
        FROM usuarios 
        WHERE activo = 1
        ORDER BY nombre
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    ApiResponse::success([
        'usuarios' => $usuarios,
        'total' => count($usuarios)
    ]);
    
} catch (Exception $e) {
    ApiResponse::error('Error al consultar usuarios: ' . $e->getMessage(), 500);
}
