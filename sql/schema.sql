-- ======================================
-- MoneyFlow - Base de Datos con Multiusuario
-- Sistema de Control de Gastos Personales
-- ======================================

-- Eliminar tablas si existen (para re-ejecución limpia)
DROP TABLE IF EXISTS gastos;
DROP TABLE IF EXISTS gastos_fijos;
DROP TABLE IF EXISTS configuracion;
DROP TABLE IF EXISTS usuarios;

-- ======================================
-- TABLA: usuarios
-- Almacena usuarios del sistema
-- ======================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    rol ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- TABLA: configuracion
-- Almacena configuración financiera por usuario
-- ======================================
CREATE TABLE configuracion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ingreso_mensual DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    monto_ahorro DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    monto_gourmet DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    saldo_inicial DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    gourmet_inicial DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    objetivo_ahorro DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_config (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- TABLA: gastos
-- Almacena todos los gastos registrados por usuario
-- ======================================
CREATE TABLE gastos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('necesario', 'opcional', 'emergencia') NOT NULL DEFAULT 'necesario',
    categoria ENUM('comida', 'transporte', 'salud', 'entretenimiento', 'servicios', 'otros') NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo ENUM('efectivo', 'gourmet') NOT NULL DEFAULT 'efectivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_user_fecha (user_id, fecha),
    INDEX idx_fecha (fecha),
    INDEX idx_metodo (metodo),
    INDEX idx_categoria (categoria),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- TABLA: gastos_fijos
-- Almacena gastos fijos mensuales por usuario
-- ======================================
CREATE TABLE gastos_fijos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_user_activo (user_id, activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- DATOS INICIALES - USUARIOS
-- Password: admin123 (hash con password_hash de PHP)
-- Password: maria123 (hash con password_hash de PHP)
-- ======================================
INSERT INTO usuarios (username, password, nombre, email, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@moneyflow.com', 'admin'),
('maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'maria@example.com', 'usuario');

-- Nota: Para crear nuevas contraseñas, usar en PHP:
-- echo password_hash('tu_password', PASSWORD_DEFAULT);

-- ======================================
-- DATOS INICIALES - CONFIGURACIÓN
-- Configuración para admin
-- ======================================
INSERT INTO configuracion (
    user_id,
    ingreso_mensual,
    monto_ahorro,
    monto_gourmet,
    saldo_inicial, 
    gourmet_inicial, 
    objetivo_ahorro, 
    fecha_inicio, 
    fecha_fin
) VALUES (
    1,  -- admin
    5000000.00,  -- Ingreso mensual
    1200000.00,  -- Monto de ahorro mensual
    300000.00,   -- Monto gourmet mensual (opcional)
    4287264.00,  -- Saldo inicial en efectivo
    561290.00,   -- Saldo tarjeta gourmet
    1200000.00,  -- Objetivo de ahorro
    '2026-04-01',  -- Inicio del periodo
    '2026-04-30'   -- Fin del periodo
);

-- ======================================
-- DATOS INICIALES - GASTOS FIJOS (Ejemplo para admin)
-- ======================================
INSERT INTO gastos_fijos (user_id, nombre, monto) VALUES
(1, 'Arriendo', 800000.00),
(1, 'Internet + Luz', 120000.00),
(1, 'Suscripción Netflix', 35000.00),
(1, 'Gimnasio', 50000.00),
(1, 'Seguro', 45000.00);

-- Configuración para María
INSERT INTO configuracion (
    user_id,
    saldo_inicial, 
    gourmet_inicial, 
    objetivo_ahorro, 
    fecha_inicio, 
    fecha_fin
) VALUES (
    2,  -- maria
    2000000.00,  -- Saldo inicial en efectivo
    300000.00,   -- Saldo tarjeta gourmet
    500000.00,   -- Objetivo de ahorro
    '2026-04-01',  -- Inicio del periodo
    '2026-04-25'   -- Fin del periodo
);

-- ======================================
-- VISTA: resumen_gastos_por_usuario
-- Calcula automáticamente resúmenes por usuario
-- ======================================
CREATE OR REPLACE VIEW resumen_gastos_por_usuario AS
SELECT 
    g.user_id,
    u.nombre as nombre_usuario,
    DATE_FORMAT(g.fecha, '%Y-%m') as periodo,
    COUNT(*) as total_registros,
    SUM(CASE WHEN g.metodo = 'efectivo' THEN g.monto ELSE 0 END) as total_efectivo,
    SUM(CASE WHEN g.metodo = 'gourmet' THEN g.monto ELSE 0 END) as total_gourmet,
    SUM(g.monto) as total_general,
    SUM(CASE WHEN g.tipo = 'necesario' THEN g.monto ELSE 0 END) as total_necesarios,
    SUM(CASE WHEN g.tipo = 'opcional' THEN g.monto ELSE 0 END) as total_opcionales,
    SUM(CASE WHEN g.tipo = 'emergencia' THEN g.monto ELSE 0 END) as total_emergencias
FROM gastos g
INNER JOIN usuarios u ON g.user_id = u.id
GROUP BY g.user_id, u.nombre, DATE_FORMAT(g.fecha, '%Y-%m');

-- ======================================
-- PROCEDIMIENTO: obtener_estado_financiero
-- Retorna el estado completo de las finanzas de un usuario
-- ======================================
DELIMITER //
CREATE PROCEDURE obtener_estado_financiero(
    IN p_user_id INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT 
        c.saldo_inicial,
        c.gourmet_inicial,
        c.objetivo_ahorro,
        COALESCE(SUM(CASE WHEN g.metodo = 'efectivo' THEN g.monto ELSE 0 END), 0) as gastos_efectivo,
        COALESCE(SUM(CASE WHEN g.metodo = 'gourmet' THEN g.monto ELSE 0 END), 0) as gastos_gourmet,
        (c.saldo_inicial - COALESCE(SUM(CASE WHEN g.metodo = 'efectivo' THEN g.monto ELSE 0 END), 0)) as saldo_actual,
        (c.gourmet_inicial - COALESCE(SUM(CASE WHEN g.metodo = 'gourmet' THEN g.monto ELSE 0 END), 0)) as gourmet_disponible,
        ((c.saldo_inicial - COALESCE(SUM(CASE WHEN g.metodo = 'efectivo' THEN g.monto ELSE 0 END), 0)) - c.objetivo_ahorro) as ahorro_posible
    FROM configuracion c
    LEFT JOIN gastos g ON g.fecha BETWEEN p_fecha_inicio AND p_fecha_fin AND g.user_id = p_user_id
    WHERE c.user_id = p_user_id
    GROUP BY c.id;
END //
DELIMITER ;

-- ======================================
-- Verificación de instalación
-- ======================================
SELECT 'Base de datos MoneyFlow creada exitosamente con sistema multiusuario' as mensaje;
SELECT id, username, nombre, rol FROM usuarios;
SELECT c.id, u.username, c.saldo_inicial, c.gourmet_inicial, c.objetivo_ahorro 
FROM configuracion c 
INNER JOIN usuarios u ON c.user_id = u.id;
