<?php
/**
 * Script CLI para crear nuevos usuarios en MoneyFlow
 * 
 * Uso desde línea de comandos:
 *   php crear_usuario.php
 * 
 * O con parámetros:
 *   php crear_usuario.php username "Nombre Completo" email password admin
 * 
 * Parámetros:
 *   1. username      - Nombre de usuario (único)
 *   2. nombre        - Nombre completo
 *   3. email         - Email (opcional, usar "null" para omitir)
 *   4. password      - Contraseña (mínimo 8 caracteres)
 *   5. rol           - admin o usuario (opcional, default: usuario)
 */

// Solo permitir ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde línea de comandos.\n");
}

require_once __DIR__ . '/config/database.php';

// Colores para terminal
class Color {
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const RESET = "\033[0m";
}

/**
 * Mostrar mensaje con color
 */
function mensaje($texto, $color = Color::RESET) {
    echo $color . $texto . Color::RESET . "\n";
}

/**
 * Solicitar input del usuario
 */
function solicitar($prompt, $requerido = true, $ocultar = false) {
    echo Color::BLUE . $prompt . Color::RESET;
    
    if ($ocultar) {
        // Ocultar contraseña en la entrada
        system('stty -echo');
        $input = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
    } else {
        $input = trim(fgets(STDIN));
    }
    
    if ($requerido && empty($input)) {
        mensaje("✗ Este campo es obligatorio.", Color::RED);
        return solicitar($prompt, $requerido, $ocultar);
    }
    
    return $input;
}

/**
 * Validar email
 */
function validarEmail($email) {
    if (empty($email) || $email === 'null') {
        return true; // Email es opcional
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Crear usuario en la base de datos
 */
function crearUsuario($db, $username, $nombre, $email, $password, $rol) {
    try {
        // Verificar si el usuario ya existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            mensaje("✗ Error: El usuario '$username' ya existe.", Color::RED);
            return false;
        }
        
        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Email null si está vacío
        $emailValue = (empty($email) || $email === 'null') ? null : $email;
        
        // Insertar usuario
        $stmt = $db->prepare(
            "INSERT INTO usuarios (username, password, nombre, email, rol, activo) 
             VALUES (?, ?, ?, ?, ?, 1)"
        );
        
        $stmt->execute([
            $username,
            $passwordHash,
            $nombre,
            $emailValue,
            $rol
        ]);
        
        $userId = $db->lastInsertId();
        
        // Crear configuración inicial para el usuario
        $fechaInicio = date('Y-m-01'); // Primer día del mes actual
        $fechaFin = date('Y-m-t');     // Último día del mes actual
        
        $stmt = $db->prepare(
            "INSERT INTO configuracion (user_id, ingreso_mensual, monto_ahorro, monto_gourmet, 
                                       fecha_inicio, fecha_fin) 
             VALUES (?, 0, 0, 0, ?, ?)"
        );
        
        $stmt->execute([$userId, $fechaInicio, $fechaFin]);
        
        return $userId;
        
    } catch (PDOException $e) {
        mensaje("✗ Error de base de datos: " . $e->getMessage(), Color::RED);
        return false;
    }
}

// ============================================
// MAIN SCRIPT
// ============================================

mensaje("\n╔════════════════════════════════════════════╗", Color::GREEN);
mensaje("║   MoneyFlow - Creador de Usuarios CLI    ║", Color::GREEN);
mensaje("╚════════════════════════════════════════════╝\n", Color::GREEN);

// Conectar a la base de datos
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    mensaje("✗ Error de conexión: " . $e->getMessage(), Color::RED);
    mensaje("Verifica la configuración en config/database.php", Color::YELLOW);
    exit(1);
}

// Modo interactivo o con parámetros
if ($argc > 1) {
    // Modo con parámetros
    $username = $argv[1] ?? '';
    $nombre = $argv[2] ?? '';
    $email = $argv[3] ?? null;
    $password = $argv[4] ?? '';
    $rol = $argv[5] ?? 'usuario';
    
    // Validaciones
    if (empty($username) || empty($nombre) || empty($password)) {
        mensaje("✗ Parámetros insuficientes.", Color::RED);
        mensaje("\nUso: php crear_usuario.php <username> \"Nombre\" <email> <password> [rol]", Color::YELLOW);
        mensaje("Ejemplo: php crear_usuario.php juan \"Juan Perez\" juan@email.com mipass123 usuario\n", Color::BLUE);
        exit(1);
    }
    
} else {
    // Modo interactivo
    mensaje("Ingresa los datos del nuevo usuario:\n");
    
    $username = solicitar("Usuario (sin espacios): ", true);
    $nombre = solicitar("Nombre completo: ", true);
    $email = solicitar("Email (opcional, Enter para omitir): ", false);
    
    // Solicitar contraseña
    $password = solicitar("Contraseña (mínimo 8 caracteres): ", true, true);
    
    // Validar longitud de contraseña
    while (strlen($password) < 8) {
        mensaje("✗ La contraseña debe tener al menos 8 caracteres.", Color::RED);
        $password = solicitar("Contraseña (mínimo 8 caracteres): ", true, true);
    }
    
    $confirmPassword = solicitar("Confirmar contraseña: ", true, true);
    
    while ($password !== $confirmPassword) {
        mensaje("✗ Las contraseñas no coinciden. Intenta de nuevo.", Color::RED);
        $password = solicitar("Contraseña (mínimo 8 caracteres): ", true, true);
        $confirmPassword = solicitar("Confirmar contraseña: ", true, true);
    }
    
    $rol = solicitar("Rol (admin/usuario) [usuario]: ", false);
    $rol = empty($rol) ? 'usuario' : $rol;
}

// Validaciones finales
if (!in_array($rol, ['admin', 'usuario'])) {
    mensaje("✗ Rol inválido. Debe ser 'admin' o 'usuario'.", Color::RED);
    exit(1);
}

if (!validarEmail($email)) {
    mensaje("✗ Email inválido.", Color::RED);
    exit(1);
}

if (strlen($password) < 8) {
    mensaje("✗ La contraseña debe tener al menos 8 caracteres.", Color::RED);
    exit(1);
}

// Confirmar creación
mensaje("\n" . str_repeat("─", 46), Color::BLUE);
mensaje("Resumen del usuario a crear:", Color::BLUE);
mensaje(str_repeat("─", 46), Color::BLUE);
mensaje("Usuario:  $username");
mensaje("Nombre:   $nombre");
mensaje("Email:    " . (empty($email) || $email === 'null' ? '(sin email)' : $email));
mensaje("Rol:      $rol");
mensaje(str_repeat("─", 46) . "\n", Color::BLUE);

if ($argc === 1) {
    // Solo pedir confirmación en modo interactivo
    $confirmar = solicitar("¿Crear este usuario? (s/n): ", true);
    if (strtolower($confirmar) !== 's') {
        mensaje("Operación cancelada.", Color::YELLOW);
        exit(0);
    }
}

// Crear usuario
mensaje("\nCreando usuario...", Color::YELLOW);

$userId = crearUsuario($db, $username, $nombre, $email, $password, $rol);

if ($userId) {
    mensaje("\n✓ ¡Usuario creado exitosamente!", Color::GREEN);
    mensaje("  ID: $userId", Color::GREEN);
    mensaje("  Usuario: $username", Color::GREEN);
    mensaje("  Rol: $rol", Color::GREEN);
    mensaje("\nEl usuario puede iniciar sesión en: http://localhost/moneyflow", Color::BLUE);
    mensaje("Credenciales: $username / [contraseña ingresada]\n", Color::BLUE);
    exit(0);
} else {
    mensaje("\n✗ Error al crear el usuario.", Color::RED);
    exit(1);
}
