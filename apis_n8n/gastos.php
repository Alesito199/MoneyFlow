<?php
/**
 * MoneyFlow - API N8N - Gestión de Gastos
 * Endpoints: GET, POST, PUT, DELETE
 */

require_once __DIR__ . '/config.php';

// Validar API Key
validateApiKey();

// Obtener conexión a BD
$conn = getApiConnection();

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
        
        case 'POST':
            handlePost($conn);
            break;
        
        case 'PUT':
            handlePut($conn);
            break;
        
        case 'DELETE':
            handleDelete($conn);
            break;
        
        default:
            ApiResponse::error('Método no permitido', 405);
    }
} catch (Exception $e) {
    ApiResponse::error('Error del servidor: ' . $e->getMessage(), 500);
}

/**
 * GET - Obtener gastos
 * Parámetros opcionales:
 * - user_id: ID del usuario (requerido)
 * - id: ID específico de gasto
 * - fecha_desde: Filtrar desde fecha (YYYY-MM-DD)
 * - fecha_hasta: Filtrar hasta fecha (YYYY-MM-DD)
 * - tipo: necesario|opcional|emergencia
 * - categoria: comida|transporte|salud|entretenimiento|servicios|otros
 * - metodo: efectivo|gourmet
 * - limit: Límite de resultados (default: 100)
 */
function handleGet($conn) {
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        ApiResponse::error('Parámetro user_id es requerido', 400);
    }
    
    validateUser($userId, $conn);
    
    // Si se solicita un gasto específico
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("
            SELECT * FROM gastos 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$_GET['id'], $userId]);
        $gasto = $stmt->fetch();
        
        if (!$gasto) {
            ApiResponse::notFound('Gasto no encontrado');
        }
        
        ApiResponse::success($gasto);
    }
    
    // Construir consulta con filtros
    $sql = "SELECT * FROM gastos WHERE user_id = ?";
    $params = [$userId];
    
    if (isset($_GET['fecha_desde'])) {
        $sql .= " AND fecha >= ?";
        $params[] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta'])) {
        $sql .= " AND fecha <= ?";
        $params[] = $_GET['fecha_hasta'];
    }
    
    if (isset($_GET['tipo'])) {
        $sql .= " AND tipo = ?";
        $params[] = $_GET['tipo'];
    }
    
    if (isset($_GET['categoria'])) {
        $sql .= " AND categoria = ?";
        $params[] = $_GET['categoria'];
    }
    
    if (isset($_GET['metodo'])) {
        $sql .= " AND metodo = ?";
        $params[] = $_GET['metodo'];
    }
    
    $sql .= " ORDER BY fecha DESC, created_at DESC";
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $sql .= " LIMIT ?";
    $params[] = $limit;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $gastos = $stmt->fetchAll();
    
    ApiResponse::success([
        'gastos' => $gastos,
        'total' => count($gastos)
    ]);
}

/**
 * POST - Crear nuevo gasto
 * Body JSON:
 * {
 *   "user_id": 1,
 *   "fecha": "2026-04-23",
 *   "tipo": "necesario",
 *   "categoria": "comida",
 *   "descripcion": "Almuerzo",
 *   "monto": 15.50,
 *   "metodo": "efectivo"
 * }
 */
function handlePost($conn) {
    $data = getRequestBody();
    
    // Validar campos requeridos
    $required = ['user_id', 'fecha', 'tipo', 'categoria', 'descripcion', 'monto'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            ApiResponse::error("Campo requerido faltante: {$field}", 400);
        }
    }
    
    validateUser($data['user_id'], $conn);
    
    // Valores por defecto
    $metodo = $data['metodo'] ?? 'efectivo';
    
    // Validar tipos ENUM
    $tiposValidos = ['necesario', 'opcional', 'emergencia'];
    $categoriasValidas = ['comida', 'transporte', 'salud', 'entretenimiento', 'servicios', 'otros'];
    $metodosValidos = ['efectivo', 'gourmet'];
    
    if (!in_array($data['tipo'], $tiposValidos)) {
        ApiResponse::error('Tipo inválido. Valores permitidos: ' . implode(', ', $tiposValidos), 400);
    }
    
    if (!in_array($data['categoria'], $categoriasValidas)) {
        ApiResponse::error('Categoría inválida. Valores permitidos: ' . implode(', ', $categoriasValidas), 400);
    }
    
    if (!in_array($metodo, $metodosValidos)) {
        ApiResponse::error('Método inválido. Valores permitidos: ' . implode(', ', $metodosValidos), 400);
    }
    
    // Validar monto
    if (!is_numeric($data['monto']) || $data['monto'] <= 0) {
        ApiResponse::error('El monto debe ser un número positivo', 400);
    }
    
    // Insertar gasto
    $stmt = $conn->prepare("
        INSERT INTO gastos (user_id, fecha, tipo, categoria, descripcion, monto, metodo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $data['user_id'],
        $data['fecha'],
        $data['tipo'],
        $data['categoria'],
        $data['descripcion'],
        $data['monto'],
        $metodo
    ]);
    
    if ($result) {
        $gastoId = $conn->lastInsertId();
        
        // Obtener el gasto creado
        $stmt = $conn->prepare("SELECT * FROM gastos WHERE id = ?");
        $stmt->execute([$gastoId]);
        $gasto = $stmt->fetch();
        
        ApiResponse::success($gasto, 'Gasto registrado exitosamente');
    } else {
        ApiResponse::error('Error al registrar el gasto', 500);
    }
}

/**
 * PUT - Actualizar gasto existente
 * Body JSON:
 * {
 *   "id": 123,
 *   "user_id": 1,
 *   "fecha": "2026-04-23",
 *   "tipo": "opcional",
 *   "categoria": "entretenimiento",
 *   "descripcion": "Cine",
 *   "monto": 25.00,
 *   "metodo": "gourmet"
 * }
 */
function handlePut($conn) {
    $data = getRequestBody();
    
    if (!isset($data['id']) || !isset($data['user_id'])) {
        ApiResponse::error('Campos id y user_id son requeridos', 400);
    }
    
    validateUser($data['user_id'], $conn);
    
    // Verificar que el gasto existe y pertenece al usuario
    $stmt = $conn->prepare("SELECT id FROM gastos WHERE id = ? AND user_id = ?");
    $stmt->execute([$data['id'], $data['user_id']]);
    
    if (!$stmt->fetch()) {
        ApiResponse::notFound('Gasto no encontrado o no pertenece al usuario');
    }
    
    // Construir UPDATE dinámico
    $updates = [];
    $params = [];
    
    $allowedFields = ['fecha', 'tipo', 'categoria', 'descripcion', 'monto', 'metodo'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "{$field} = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        ApiResponse::error('No hay campos para actualizar', 400);
    }
    
    $params[] = $data['id'];
    $params[] = $data['user_id'];
    
    $sql = "UPDATE gastos SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute($params)) {
        // Obtener el gasto actualizado
        $stmt = $conn->prepare("SELECT * FROM gastos WHERE id = ?");
        $stmt->execute([$data['id']]);
        $gasto = $stmt->fetch();
        
        ApiResponse::success($gasto, 'Gasto actualizado exitosamente');
    } else {
        ApiResponse::error('Error al actualizar el gasto', 500);
    }
}

/**
 * DELETE - Eliminar gasto
 * Parámetros requeridos:
 * - id: ID del gasto
 * - user_id: ID del usuario
 */
function handleDelete($conn) {
    $id = $_GET['id'] ?? null;
    $userId = $_GET['user_id'] ?? null;
    
    if (!$id || !$userId) {
        ApiResponse::error('Parámetros id y user_id son requeridos', 400);
    }
    
    validateUser($userId, $conn);
    
    // Verificar que el gasto existe
    $stmt = $conn->prepare("SELECT id FROM gastos WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    if (!$stmt->fetch()) {
        ApiResponse::notFound('Gasto no encontrado o no pertenece al usuario');
    }
    
    // Eliminar gasto
    $stmt = $conn->prepare("DELETE FROM gastos WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$id, $userId])) {
        ApiResponse::success(null, 'Gasto eliminado exitosamente');
    } else {
        ApiResponse::error('Error al eliminar el gasto', 500);
    }
}
