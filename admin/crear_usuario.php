<?php
/**
 * Interfaz Web para crear nuevos usuarios (Solo Admin)
 * Acceso: http://localhost/moneyflow/admin/crear_usuario.php
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';
    
    // Validaciones
    $errores = [];
    
    if (empty($username)) {
        $errores[] = "El usuario es obligatorio";
    }
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    if (empty($password)) {
        $errores[] = "La contraseña es obligatoria";
    } elseif (strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Email inválido";
    }
    if (!in_array($rol, ['admin', 'usuario'])) {
        $errores[] = "Rol inválido";
    }
    
    // Verificar si el usuario ya existe
    if (empty($errores)) {
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errores[] = "El usuario '$username' ya existe";
        }
    }
    
    // Crear usuario si no hay errores
    if (empty($errores)) {
        try {
            // Hash de la contraseña
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            // Email null si está vacío
            $emailValue = empty($email) ? null : $email;
            
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
            
            // Crear configuración inicial
            $fechaInicio = date('Y-m-01');
            $fechaFin = date('Y-m-t');
            
            $stmt = $db->prepare(
                "INSERT INTO configuracion (user_id, ingreso_mensual, monto_ahorro, monto_gourmet, 
                                           fecha_inicio, fecha_fin) 
                 VALUES (?, 0, 0, 0, ?, ?)"
            );
            
            $stmt->execute([$userId, $fechaInicio, $fechaFin]);
            
            $mensaje = "Usuario '$username' creado exitosamente con ID: $userId";
            $tipo_mensaje = 'success';
            
            // Limpiar formulario
            $_POST = [];
            
        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = 'error';
    }
}

// Obtener lista de usuarios existentes
$stmt = $db->query("SELECT id, username, nombre, email, rol, activo, created_at FROM usuarios ORDER BY created_at DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - MoneyFlow Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            transition: background 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: #5568d3;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-admin {
            background: #dc3545;
            color: white;
        }
        
        .badge-usuario {
            background: #28a745;
            color: white;
        }
        
        .badge-activo {
            background: #28a745;
            color: white;
        }
        
        .badge-inactivo {
            background: #6c757d;
            color: white;
        }
        
        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h1>
            <a href="../dashboard/index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
        
        <div class="card">
            <h2>Formulario de Nuevo Usuario</h2>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Usuario *</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               required>
                        <div class="help-text">Sin espacios, único en el sistema</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <div class="help-text">Opcional</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rol">Rol *</label>
                        <select id="rol" name="rol" required>
                            <option value="usuario" <?php echo (!isset($_POST['rol']) || $_POST['rol'] === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                            <option value="admin" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" required>
                        <div class="help-text">Mínimo 8 caracteres</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Crear Usuario
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2>Usuarios Existentes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Creado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['rol']; ?>">
                                <?php echo ucfirst($user['rol']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $user['activo'] ? 'activo' : 'inactivo'; ?>">
                                <?php echo $user['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
