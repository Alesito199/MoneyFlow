-- ======================================
-- MoneyFlow - Base de Datos
-- Sistema de Control de Gastos Personales
-- ======================================

-- Eliminar tablas si existen (para re-ejecución limpia)
DROP TABLE IF EXISTS gastos;
DROP TABLE IF EXISTS configuracion;

-- ======================================
-- TABLA: configuracion
-- Almacena configuración global del sistema
-- ======================================
CREATE TABLE configuracion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    saldo_inicial DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    gourmet_inicial DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    objetivo_ahorro DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- TABLA: gastos
-- Almacena todos los gastos registrados
-- ======================================
CREATE TABLE gastos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    tipo ENUM('fijo', 'variable') NOT NULL DEFAULT 'variable',
    categoria ENUM('electricidad', 'transporte', 'supermercado', 'servicios', 'otros') NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo ENUM('efectivo', 'gourmet') NOT NULL DEFAULT 'efectivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha),
    INDEX idx_metodo (metodo),
    INDEX idx_categoria (categoria),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- DATOS INICIALES
-- Insertar configuración base
-- ======================================
INSERT INTO configuracion (
    saldo_inicial, 
    gourmet_inicial, 
    objetivo_ahorro, 
    fecha_inicio, 
    fecha_fin
) VALUES (
    4287264.00,  -- Saldo inicial en efectivo
    561290.00,   -- Saldo tarjeta gourmet
    1200000.00,  -- Objetivo de ahorro
    '2026-04-01',  -- Inicio del periodo
    '2026-04-25'   -- Fin del periodo
);

-- ======================================
-- VISTA: resumen_gastos
-- Calcula automáticamente resúmenes útiles
-- ======================================
CREATE OR REPLACE VIEW resumen_gastos AS
SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as periodo,
    COUNT(*) as total_registros,
    SUM(CASE WHEN metodo = 'efectivo' THEN monto ELSE 0 END) as total_efectivo,
    SUM(CASE WHEN metodo = 'gourmet' THEN monto ELSE 0 END) as total_gourmet,
    SUM(monto) as total_general,
    SUM(CASE WHEN tipo = 'fijo' THEN monto ELSE 0 END) as total_fijos,
    SUM(CASE WHEN tipo = 'variable' THEN monto ELSE 0 END) as total_variables
FROM gastos
GROUP BY DATE_FORMAT(fecha, '%Y-%m');

-- ======================================
-- PROCEDIMIENTO: obtener_estado_financiero
-- Retorna el estado completo de las finanzas
-- ======================================
DELIMITER //
CREATE PROCEDURE obtener_estado_financiero(
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
    LEFT JOIN gastos g ON g.fecha BETWEEN p_fecha_inicio AND p_fecha_fin
    WHERE c.id = 1
    GROUP BY c.id;
END //
DELIMITER ;

-- ======================================
-- Verificación de instalación
-- ======================================
SELECT 'Base de datos MoneyFlow creada exitosamente' as mensaje;
SELECT * FROM configuracion;
