<?php
/**
 * MoneyFlow - Funciones del Sistema
 * Lógica de negocio y cálculos financieros
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Obtener la configuración actual del sistema
 * @return array|null
 */
function obtenerConfiguracion() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM configuracion WHERE id = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetch();
}

/**
 * Obtener todos los gastos en un rango de fechas
 * @param string $fechaInicio
 * @param string $fechaFin
 * @return array
 */
function obtenerGastos($fechaInicio, $fechaFin) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT * FROM gastos 
              WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin 
              ORDER BY fecha DESC, created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Calcular total de gastos en efectivo
 * @param string $fechaInicio
 * @param string $fechaFin
 * @return float
 */
function calcularGastosEfectivo($fechaInicio, $fechaFin) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT COALESCE(SUM(monto), 0) as total 
              FROM gastos 
              WHERE metodo = 'efectivo' 
              AND fecha BETWEEN :fecha_inicio AND :fecha_fin";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    $result = $stmt->fetch();
    return floatval($result['total']);
}

/**
 * Calcular total de gastos en gourmet
 * @param string $fechaInicio
 * @param string $fechaFin
 * @return float
 */
function calcularGastosGourmet($fechaInicio, $fechaFin) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT COALESCE(SUM(monto), 0) as total 
              FROM gastos 
              WHERE metodo = 'gourmet' 
              AND fecha BETWEEN :fecha_inicio AND :fecha_fin";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    $result = $stmt->fetch();
    return floatval($result['total']);
}

/**
 * Calcular el estado financiero completo
 * @return array
 */
function calcularEstadoFinanciero() {
    $config = obtenerConfiguracion();
    
    if (!$config) {
        return null;
    }
    
    $fechaInicio = $config['fecha_inicio'];
    $fechaFin = $config['fecha_fin'];
    
    $gastosEfectivo = calcularGastosEfectivo($fechaInicio, $fechaFin);
    $gastosGourmet = calcularGastosGourmet($fechaInicio, $fechaFin);
    
    // REGLA CLAVE: Gourmet NO afecta el saldo de efectivo
    $saldoActual = $config['saldo_inicial'] - $gastosEfectivo;
    $gourmetDisponible = $config['gourmet_inicial'] - $gastosGourmet;
    
    // Calcular ahorro actual (lo que sobra después de cumplir el objetivo)
    $ahorroActual = $saldoActual - $config['objetivo_ahorro'];
    
    // Porcentaje de progreso del ahorro
    $porcentajeAhorro = 0;
    if ($config['objetivo_ahorro'] > 0) {
        $porcentajeAhorro = ($ahorroActual / $config['objetivo_ahorro']) * 100;
        $porcentajeAhorro = max(0, min(100, $porcentajeAhorro)); // Entre 0 y 100
    }
    
    // Determinar estado
    $estado = $saldoActual < ALERTA_SALDO_MINIMO ? 'ALERTA' : 'OK';
    
    // Análisis de ritmo de gasto
    $analisisRitmo = analizarRitmoGasto($fechaInicio, $fechaFin, $gastosEfectivo, $config['saldo_inicial']);
    
    if ($analisisRitmo['alerta_avanzada']) {
        $estado = 'ALERTA_AVANZADA';
    }
    
    return [
        'saldo_inicial' => floatval($config['saldo_inicial']),
        'gourmet_inicial' => floatval($config['gourmet_inicial']),
        'objetivo_ahorro' => floatval($config['objetivo_ahorro']),
        'gastos_efectivo' => $gastosEfectivo,
        'gastos_gourmet' => $gastosGourmet,
        'gastos_totales' => $gastosEfectivo + $gastosGourmet,
        'saldo_actual' => $saldoActual,
        'gourmet_disponible' => $gourmetDisponible,
        'ahorro_actual' => $ahorroActual,
        'porcentaje_ahorro' => round($porcentajeAhorro, 2),
        'estado' => $estado,
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'analisis_ritmo' => $analisisRitmo
    ];
}

/**
 * Analizar el ritmo de gasto (Control Inteligente)
 * @param string $fechaInicio
 * @param string $fechaFin
 * @param float $gastoActual
 * @param float $presupuesto
 * @return array
 */
function analizarRitmoGasto($fechaInicio, $fechaFin, $gastoActual, $presupuesto) {
    $inicio = new DateTime($fechaInicio);
    $fin = new DateTime($fechaFin);
    $hoy = new DateTime();
    
    // Si estamos fuera del periodo, no aplicar análisis
    if ($hoy < $inicio || $hoy > $fin) {
        return [
            'dia_actual' => 0,
            'dias_totales' => 0,
            'porcentaje_periodo' => 0,
            'gasto_esperado' => 0,
            'gasto_real' => $gastoActual,
            'diferencia' => 0,
            'alerta_avanzada' => false
        ];
    }
    
    // Calcular día actual dentro del periodo
    $diaActual = $inicio->diff($hoy)->days + 1;
    $diasTotales = $inicio->diff($fin)->days + 1;
    
    // Porcentaje del periodo transcurrido
    $porcentajePeriodo = ($diaActual / $diasTotales) * 100;
    
    // Gasto esperado según el día (proporcional)
    $gastoEsperado = ($presupuesto * $porcentajePeriodo) / 100;
    
    // Diferencia
    $diferencia = $gastoActual - $gastoEsperado;
    
    // Alerta avanzada si el gasto supera lo esperado
    $alertaAvanzada = $gastoActual > $gastoEsperado;
    
    return [
        'dia_actual' => $diaActual,
        'dias_totales' => $diasTotales,
        'porcentaje_periodo' => round($porcentajePeriodo, 2),
        'gasto_esperado' => round($gastoEsperado, 2),
        'gasto_real' => $gastoActual,
        'diferencia' => round($diferencia, 2),
        'alerta_avanzada' => $alertaAvanzada,
        'mensaje' => $alertaAvanzada 
            ? "⚠️ Estás gastando más rápido de lo esperado" 
            : "✅ Tu ritmo de gasto es saludable"
    ];
}

/**
 * Registrar un nuevo gasto
 * @param array $datos
 * @return bool|string ID del gasto o false si falla
 */
function registrarGasto($datos) {
    // Validaciones
    if (empty($datos['fecha']) || empty($datos['tipo']) || empty($datos['categoria']) || 
        empty($datos['descripcion']) || !isset($datos['monto']) || empty($datos['metodo'])) {
        return false;
    }
    
    if ($datos['monto'] <= 0) {
        return false;
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "INSERT INTO gastos (fecha, tipo, categoria, descripcion, monto, metodo) 
              VALUES (:fecha, :tipo, :categoria, :descripcion, :monto, :metodo)";
    
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':fecha', $datos['fecha']);
    $stmt->bindParam(':tipo', $datos['tipo']);
    $stmt->bindParam(':categoria', $datos['categoria']);
    $stmt->bindParam(':descripcion', $datos['descripcion']);
    $stmt->bindParam(':monto', $datos['monto']);
    $stmt->bindParam(':metodo', $datos['metodo']);
    
    if ($stmt->execute()) {
        return $conn->lastInsertId();
    }
    
    return false;
}

/**
 * Obtener gastos agrupados por categoría
 * @param string $fechaInicio
 * @param string $fechaFin
 * @return array
 */
function obtenerGastosPorCategoria($fechaInicio, $fechaFin) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT 
                categoria,
                COUNT(*) as cantidad,
                SUM(monto) as total
              FROM gastos 
              WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin
              GROUP BY categoria
              ORDER BY total DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Obtener evolución diaria de gastos
 * @param string $fechaInicio
 * @param string $fechaFin
 * @return array
 */
function obtenerEvolucionDiaria($fechaInicio, $fechaFin) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT 
                fecha,
                SUM(CASE WHEN metodo = 'efectivo' THEN monto ELSE 0 END) as efectivo,
                SUM(CASE WHEN metodo = 'gourmet' THEN monto ELSE 0 END) as gourmet,
                SUM(monto) as total
              FROM gastos 
              WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin
              GROUP BY fecha
              ORDER BY fecha ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Formatear número como moneda paraguaya
 * @param float $numero
 * @return string
 */
function formatearMoneda($numero) {
    return number_format($numero, 0, ',', '.') . ' ' . MONEDA;
}

/**
 * Enviar respuesta JSON
 * @param mixed $data
 * @param int $httpCode
 */
function enviarJSON($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Sanitizar entrada de usuario
 * @param string $data
 * @return string
 */
function sanitizar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
