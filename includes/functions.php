<?php
/**
 * MoneyFlow - Funciones del Sistema con Multiusuario
 * Lógica de negocio y cálculos financieros por usuario
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Obtener la configuración de un usuario específico
 * @param int $userId
 * @return array|false
 */
function obtenerConfiguracion($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtener todos los gastos de un usuario en un rango de fechas
 * @param string $fechaInicio
 * @param string $fechaFin
 * @param int $userId
 * @return array
 */
function obtenerGastos($fechaInicio, $fechaFin, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM gastos 
            WHERE user_id = ? 
            AND fecha BETWEEN ? AND ? 
            ORDER BY fecha DESC, created_at DESC
        ");
        $stmt->execute([$userId, $fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Calcular total de gastos en efectivo de un usuario
 * @param string $fechaInicio
 * @param string $fechaFin
 * @param int $userId
 * @return float
 */
function calcularGastosEfectivo($fechaInicio, $fechaFin, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total 
            FROM gastos 
            WHERE user_id = ? 
            AND metodo = 'efectivo' 
            AND fecha BETWEEN ? AND ?
        ");
        $stmt->execute([$userId, $fechaInicio, $fechaFin]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total']);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Calcular total de gastos en gourmet de un usuario
 * @param string $fechaInicio
 * @param string $fechaFin
 * @param int $userId
 * @return float
 */
function calcularGastosGourmet($fechaInicio, $fechaFin, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total 
            FROM gastos 
            WHERE user_id = ? 
            AND metodo = 'gourmet' 
            AND fecha BETWEEN ? AND ?
        ");
        $stmt->execute([$userId, $fechaInicio, $fechaFin]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total']);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Calcular el estado financiero completo de un usuario
 * @param int $userId
 * @return array|false
 */
function calcularEstadoFinanciero($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    $config = obtenerConfiguracion($userId);
    if (!$config) {
        return false;
    }
    
    $gastosEfectivo = calcularGastosEfectivo($config['fecha_inicio'], $config['fecha_fin'], $userId);
    $gastosGourmet = calcularGastosGourmet($config['fecha_inicio'], $config['fecha_fin'], $userId);
    $totalGastosFijos = calcularTotalGastosFijos($userId);
    $gastosFijosTradicionales = calcularTotalGastosFijosTradicionales($userId);
    $totalSuscripciones = calcularTotalSuscripciones($userId);
    
    // Cálculos según los requisitos
    $ingresoMensual = floatval($config['ingreso_mensual']);
    $montoAhorro = floatval($config['monto_ahorro']);
    $gastosVariables = $gastosEfectivo; // Los gastos en efectivo son variables
    
    // Disponible = ingreso - ahorro
    $disponible = $ingresoMensual - $montoAhorro;
    
    // Disponible real = disponible - gastos fijos - gastos variables
    $disponibleReal = $disponible - $totalGastosFijos - $gastosVariables;
    
    $saldoActual = $config['saldo_inicial'] - $gastosEfectivo;
    $gourmetDisponible = $config['gourmet_inicial'] - $gastosGourmet;
    $totalGastado = $gastosEfectivo + $gastosGourmet;
    $ahorroActual = $saldoActual - $config['objetivo_ahorro'];
    
    // Calcular porcentaje de progreso del ahorro
    $totalDisponible = $config['saldo_inicial'] + $config['gourmet_inicial'];
    $porcentajeAhorro = 0;
    if ($totalDisponible > 0) {
        $porcentajeAhorro = (($totalDisponible - $totalGastado) / $totalDisponible) * 100;
    }
    
    return [
        'ingreso_mensual' => $ingresoMensual,
        'monto_ahorro' => $montoAhorro,
        'monto_gourmet' => floatval($config['monto_gourmet']),
        'gastos_fijos' => $totalGastosFijos,
        'gastos_fijos_tradicionales' => $gastosFijosTradicionales,
        'gastos_suscripciones' => $totalSuscripciones,
        'gastos_variables' => $gastosVariables,
        'disponible' => $disponible,
        'disponible_real' => $disponibleReal,
        'saldo_inicial' => $config['saldo_inicial'],
        'saldo_actual' => $saldoActual,
        'gastos_efectivo' => $gastosEfectivo,
        'gourmet_inicial' => $config['gourmet_inicial'],
        'gourmet_disponible' => $gourmetDisponible,
        'gastos_gourmet' => $gastosGourmet,
        'total_gastado' => $totalGastado,
        'objetivo_ahorro' => $config['objetivo_ahorro'],
        'ahorro_actual' => $ahorroActual,
        'porcentaje_ahorro' => round($porcentajeAhorro, 2),
        'fecha_inicio' => $config['fecha_inicio'],
        'fecha_fin' => $config['fecha_fin']
    ];
}

/**
 * Analizar ritmo de gasto de un usuario
 * @param int $userId
 * @return array
 */
function analizarRitmoGasto($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    $estado = calcularEstadoFinanciero($userId);
    if (!$estado) {
        return [
            'dias_totales' => 0,
            'dias_transcurridos' => 0,
            'gasto_esperado' => 0,
            'gasto_real' => 0,
            'diferencia' => 0,
            'porcentaje_periodo' => 0,
            'mensaje' => 'No hay configuración disponible'
        ];
    }
    
    $fechaInicio = new DateTime($estado['fecha_inicio']);
    $fechaFin = new DateTime($estado['fecha_fin']);
    $fechaActual = new DateTime();
    
    $diasTotales = $fechaInicio->diff($fechaFin)->days + 1;
    $diasTranscurridos = $fechaInicio->diff($fechaActual)->days + 1;
    
    // Evitar división por cero y valores inválidos
    if ($diasTotales < 1) $diasTotales = 1;
    if ($diasTranscurridos < 1) $diasTranscurridos = 1;
    if ($diasTranscurridos > $diasTotales) $diasTranscurridos = $diasTotales;
    
    $porcentajePeriodo = ($diasTranscurridos / $diasTotales) * 100;
    
    // Calcular gasto esperado según el porcentaje del periodo
    $presupuestoTotal = $estado['saldo_inicial'] + $estado['gourmet_inicial'] - $estado['objetivo_ahorro'];
    $gastoEsperado = ($presupuestoTotal * $porcentajePeriodo) / 100;
    
    $diferencia = $estado['total_gastado'] - $gastoEsperado;
    $enRiesgo = $diferencia > ($presupuestoTotal * 0.1); // Más del 10% sobre lo esperado
    
    $mensaje = $enRiesgo 
        ? 'Estás gastando más rápido de lo esperado' 
        : 'Tu ritmo de gasto es saludable';
    
    return [
        'dias_totales' => $diasTotales,
        'dias_transcurridos' => $diasTranscurridos,
        'gasto_esperado' => round($gastoEsperado, 2),
        'gasto_real' => $estado['total_gastado'],
        'diferencia' => round($diferencia, 2),
        'porcentaje_periodo' => round($porcentajePeriodo, 2),
        'en_riesgo' => $enRiesgo,
        'mensaje' => $mensaje
    ];
}

/**
 * Registrar un nuevo gasto
 * @param string $tipo
 * @param string $categoria
 * @param string $metodo
 * @param float $monto
 * @param string $descripcion
 * @param string $fecha
 * @param int $userId
 * @return bool
 */
function registrarGasto($tipo, $categoria, $metodo, $monto, $descripcion, $fecha, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    // Validaciones básicas
    if (empty($tipo) || empty($categoria) || empty($metodo) || 
        empty($descripcion) || empty($fecha) || $monto <= 0) {
        return false;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO gastos (user_id, fecha, tipo, categoria, descripcion, monto, metodo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId,
            $fecha,
            $tipo,
            $categoria,
            $descripcion,
            $monto,
            $metodo
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtener gastos recientes (últimos 5)
 * @param int $userId
 * @param int $limite
 * @return array
 */
function obtenerGastosRecientes($userId = null, $limite = 5) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM gastos 
            WHERE user_id = ? 
            ORDER BY fecha DESC, created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
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
 * Calcular presupuesto diario disponible
 * @param int $userId
 * @return array
 */
function calcularPresupuestoDiario($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    $config = obtenerConfiguracion($userId);
    if (!$config) {
        return [
            'presupuesto_diario' => 0,
            'dias_totales' => 0,
            'dias_restantes' => 0,
            'gastado_hoy' => 0,
            'disponible_hoy' => 0,
            'mensaje' => 'Sin configuración'
        ];
    }
    
    // Calcular disponible total para gastar
    $ingresoMensual = floatval($config['ingreso_mensual']);
    $montoAhorro = floatval($config['monto_ahorro']);
    $totalGastosFijos = calcularTotalGastosFijos($userId);
    
    $disponibleParaGastar = $ingresoMensual - $montoAhorro - $totalGastosFijos;
    
    // Calcular días del periodo
    $fechaInicio = new DateTime($config['fecha_inicio']);
    $fechaFin = new DateTime($config['fecha_fin']);
    $diasTotales = $fechaInicio->diff($fechaFin)->days + 1;
    
    // Presupuesto diario
    $presupuestoDiario = $diasTotales > 0 ? $disponibleParaGastar / $diasTotales : 0;
    
    // Gastos de hoy
    $hoy = date('Y-m-d');
    $gastadoHoy = calcularGastosEfectivo($hoy, $hoy, $userId);
    
    // Disponible hoy
    $disponibleHoy = $presupuestoDiario - $gastadoHoy;
    
    // Días restantes
    $fechaActual = new DateTime();
    $diasRestantes = $fechaActual->diff($fechaFin)->days;
    
    // Mensaje según el estado
    $mensaje = '';
    if ($gastadoHoy > $presupuestoDiario) {
        $exceso = $gastadoHoy - $presupuestoDiario;
        $mensaje = 'Has excedido el presupuesto de hoy por ' . formatearMoneda($exceso);
    } elseif ($gastadoHoy > ($presupuestoDiario * 0.8)) {
        $mensaje = 'Estás cerca del límite diario';
    } else {
        $mensaje = 'Vas bien con tu presupuesto de hoy';
    }
    
    return [
        'presupuesto_diario' => $presupuestoDiario,
        'disponible_total' => $disponibleParaGastar,
        'dias_totales' => $diasTotales,
        'dias_restantes' => $diasRestantes,
        'gastado_hoy' => $gastadoHoy,
        'disponible_hoy' => $disponibleHoy,
        'porcentaje_usado_hoy' => $presupuestoDiario > 0 ? ($gastadoHoy / $presupuestoDiario) * 100 : 0,
        'dentro_presupuesto' => $gastadoHoy <= $presupuestoDiario,
        'mensaje' => $mensaje
    ];
}

/**
 * Calcular resumen semanal de gastos
 * @param int $userId
 * @return array
 */
function calcularResumenSemanal($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    $config = obtenerConfiguracion($userId);
    if (!$config) {
        return [];
    }
    
    $presupuestoDiario = calcularPresupuestoDiario($userId);
    $presupuestoSemanal = $presupuestoDiario['presupuesto_diario'] * 7;
    
    // Calcular gastos de los últimos 7 días
    $hoy = new DateTime();
    $hace7Dias = clone $hoy;
    $hace7Dias->modify('-7 days');
    
    $gastosSemanales = calcularGastosEfectivo($hace7Dias->format('Y-m-d'), $hoy->format('Y-m-d'), $userId);
    
    return [
        'presupuesto_semanal' => $presupuestoSemanal,
        'gastado_semanal' => $gastosSemanales,
        'disponible_semanal' => $presupuestoSemanal - $gastosSemanales,
        'porcentaje_usado' => $presupuestoSemanal > 0 ? ($gastosSemanales / $presupuestoSemanal) * 100 : 0,
        'dentro_presupuesto' => $gastosSemanales <= $presupuestoSemanal
    ];
}

/**
 * Verificar si es tiempo de reiniciar el periodo (día 25)
 * @return array
 */
function verificarReinicioPeriodo() {
    $diaActual = intval(date('d'));
    $mesActual = intval(date('m'));
    $anioActual = intval(date('Y'));
    
    // Si estamos en día 25, sugerir reinicio
    if ($diaActual === 25) {
        // Calcular próximo periodo
        $fechaInicio = date('Y-m-d');
        
        // Próximo mes, día 24
        $proximoMes = $mesActual + 1;
        $proximoAnio = $anioActual;
        if ($proximoMes > 12) {
            $proximoMes = 1;
            $proximoAnio++;
        }
        
        $fechaFin = sprintf('%04d-%02d-24', $proximoAnio, $proximoMes);
        
        return [
            'debe_reiniciar' => true,
            'fecha_inicio_sugerida' => $fechaInicio,
            'fecha_fin_sugerida' => $fechaFin,
            'mensaje' => '¡Hoy es día 25! Es momento de iniciar un nuevo periodo de control.'
        ];
    }
    
    return [
        'debe_reiniciar' => false,
        'dias_para_reinicio' => 25 - $diaActual,
        'mensaje' => 'Faltan ' . (25 - $diaActual) . ' días para el reinicio del periodo'
    ];
}

/**
 * Obtener todos los gastos fijos de un usuario
 * @param int $userId
 * @param bool $soloActivos
 * @return array
 */
function obtenerGastosFijos($userId = null, $soloActivos = true) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM gastos_fijos WHERE user_id = ?";
        if ($soloActivos) {
            $sql .= " AND activo = 1";
        }
        $sql .= " ORDER BY nombre ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Calcular total de gastos fijos de un usuario
 * @param int $userId
 * @return float
 */
function calcularTotalGastosFijos($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        
        // Sumar gastos fijos tradicionales
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total 
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1
        ");
        $stmt->execute([$userId]);
        $gastosFijos = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total']);
        
        // Sumar suscripciones activas (en guaraníes)
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto_pyg), 0) as total 
            FROM suscripciones 
            WHERE user_id = ? AND activo = 1
        ");
        $stmt->execute([$userId]);
        $suscripciones = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total']);
        
        // Retornar la suma de ambos
        return $gastosFijos + $suscripciones;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Calcular total de gastos fijos tradicionales (sin suscripciones)
 * @param int $userId
 * @return float
 */
function calcularTotalGastosFijosTradicionales($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total 
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1
        ");
        $stmt->execute([$userId]);
        return floatval($stmt->fetch(PDO::FETCH_ASSOC)['total']);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Agregar un gasto fijo
 * @param string $nombre
 * @param float $monto
 * @param int $userId
 * @return bool
 */
function agregarGastoFijo($nombre, $monto, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (empty($nombre) || $monto <= 0) {
        return false;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO gastos_fijos (user_id, nombre, monto) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $nombre, $monto]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Actualizar un gasto fijo
 * @param int $id
 * @param string $nombre
 * @param float $monto
 * @return bool
 */
function actualizarGastoFijo($id, $nombre, $monto) {
    if (empty($nombre) || $monto <= 0) {
        return false;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            UPDATE gastos_fijos 
            SET nombre = ?, monto = ? 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$nombre, $monto, $id, getUserId()]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Eliminar un gasto fijo (soft delete)
 * @param int $id
 * @return bool
 */
function eliminarGastoFijo($id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            UPDATE gastos_fijos 
            SET activo = 0 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, getUserId()]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtener un gasto fijo por ID
 * @param int $id
 * @return array|false
 */
function obtenerGastoFijoPorId($id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM gastos_fijos 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, getUserId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtener gastos por categoría para gráfico
 * @param string $fechaInicio
 * @param string $fechaFin
 * @param int $userId
 * @return array
 */
function obtenerGastosPorCategoria($fechaInicio, $fechaFin, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT categoria, SUM(monto) as total 
            FROM gastos 
            WHERE user_id = ? AND fecha BETWEEN ? AND ? 
            GROUP BY categoria 
            ORDER BY total DESC
        ");
        $stmt->execute([$userId, $fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Eliminar un gasto variable
 * @param int $id
 * @return bool
 */
function eliminarGasto($id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            DELETE FROM gastos 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, getUserId()]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtener un gasto por ID
 * @param int $id
 * @return array|false
 */
function obtenerGastoPorId($id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM gastos 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, getUserId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Actualizar un gasto
 * @param int $id
 * @param string $tipo
 * @param string $categoria
 * @param string $metodo
 * @param float $monto
 * @param string $descripcion
 * @param string $fecha
 * @return bool
 */
function actualizarGasto($id, $tipo, $categoria, $metodo, $monto, $descripcion, $fecha) {
    if (empty($tipo) || empty($categoria) || empty($metodo) || 
        empty($descripcion) || empty($fecha) || $monto <= 0) {
        return false;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            UPDATE gastos 
            SET fecha = ?, tipo = ?, categoria = ?, descripcion = ?, monto = ?, metodo = ? 
            WHERE id = ? AND user_id = ?
        ");
        
        return $stmt->execute([
            $fecha,
            $tipo,
            $categoria,
            $descripcion,
            $monto,
            $metodo,
            $id,
            getUserId()
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Actualizar configuración del usuario
 * @param float $ingresoMensual
 * @param float $montoAhorro
 * @param float $montoGourmet
 * @param string $fechaInicio
 * @param string $fechaFin
 * @param int $userId
 * @return bool
 */
function actualizarConfiguracion($ingresoMensual, $montoAhorro, $montoGourmet, $fechaInicio, $fechaFin, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            UPDATE configuracion 
            SET ingreso_mensual = ?, monto_ahorro = ?, monto_gourmet = ?, fecha_inicio = ?, fecha_fin = ?
            WHERE user_id = ?
        ");
        return $stmt->execute([$ingresoMensual, $montoAhorro, $montoGourmet, $fechaInicio, $fechaFin, $userId]);
    } catch (PDOException $e) {
        return false;
    }
}

// ===============================================
// FUNCIONES DE SUSCRIPCIONES
// ===============================================

/**
 * Obtener tasas de cambio desde API gratuita
 * Usa ExchangeRate-API (1500 requests/mes gratis)
 * @return array
 */
function obtenerTasasDesdeAPI() {
    try {
        // Verificar si cURL está habilitado
        if (!function_exists('curl_init')) {
            $_SESSION['error_message'] = "cURL no está habilitado en el servidor PHP";
            return false;
        }
        
        // API gratuita de tasas de cambio
        $url = "https://open.er-api.com/v6/latest/USD";
        
        // Usar cURL para obtener datos
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para evitar problemas SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            $_SESSION['error_message'] = "Error de conexión con API: " . $error;
            return false;
        }
        
        if ($httpCode !== 200) {
            $_SESSION['error_message'] = "API retornó código HTTP: " . $httpCode;
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['rates'])) {
            $_SESSION['error_message'] = "Respuesta de API inválida - no contiene tasas";
            return false;
        }
        
        // Verificar que existan las monedas necesarias
        if (!isset($data['rates']['PYG']) || !isset($data['rates']['EUR'])) {
            $_SESSION['error_message'] = "API no contiene tasas para PYG o EUR";
            return false;
        }
        
        // Calcular tasa USD a PYG y EUR a PYG
        $usdToPyg = $data['rates']['PYG'];
        
        // Para EUR, necesitamos calcular: EUR -> USD -> PYG
        $eurToUsd = 1 / $data['rates']['EUR'];
        $eurToPyg = $eurToUsd * $usdToPyg;
        
        return [
            'USD_PYG' => round($usdToPyg, 2),
            'EUR_PYG' => round($eurToPyg, 2),
            'PYG_PYG' => 1.00,
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Excepción al obtener tasas: " . $e->getMessage();
        error_log("Error obteniendo tasas de API: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar tasas de cambio desde API
 * @return bool
 */
function actualizarTasasDesdeAPI() {
    $tasas = obtenerTasasDesdeAPI();
    
    if ($tasas === false) {
        // El mensaje de error ya está en $_SESSION['error_message']
        return false;
    }
    
    // Actualizar cada tasa en la base de datos
    $resultado = true;
    $errores = [];
    
    if (isset($tasas['USD_PYG'])) {
        if (!actualizarTasaCambio('USD', 'PYG', $tasas['USD_PYG'])) {
            $resultado = false;
            $errores[] = "No se pudo actualizar USD->PYG";
        }
    }
    
    if (isset($tasas['EUR_PYG'])) {
        if (!actualizarTasaCambio('EUR', 'PYG', $tasas['EUR_PYG'])) {
            $resultado = false;
            $errores[] = "No se pudo actualizar EUR->PYG";
        }
    }
    
    if (!$resultado) {
        $_SESSION['error_message'] = "Error actualizando BD: " . implode(", ", $errores);
    } else {
        $_SESSION['success_message'] = "Tasas actualizadas: USD = ₲" . number_format($tasas['USD_PYG'], 0) . ", EUR = ₲" . number_format($tasas['EUR_PYG'], 0);
    }
    
    return $resultado;
}

/**
 * Obtener todas las suscripciones de un usuario
 * @param int $userId
 * @param bool $soloActivas
 * @return array
 */
function obtenerSuscripciones($userId = null, $soloActivas = true) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM suscripciones WHERE user_id = ?";
        
        if ($soloActivas) {
            $sql .= " AND activo = 1";
        }
        
        $sql .= " ORDER BY nombre ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Obtener una suscripción por ID
 * @param int $id
 * @return array|false
 */
function obtenerSuscripcionPorId($id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM suscripciones 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, getUserId()]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Agregar nueva suscripción
 * @param string $nombre
 * @param float $monto
 * @param string $moneda
 * @param string $icono
 * @param string $color
 * @param int $diaCobro
 * @param string $descripcion
 * @param int $userId
 * @return bool
 */
function agregarSuscripcion($nombre, $monto, $moneda, $icono, $color, $diaCobro, $descripcion = null, $userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (empty($nombre) || $monto <= 0 || empty($moneda) || empty($icono)) {
        return false;
    }
    
    // Actualizar tasas desde API si la moneda no es PYG
    if ($moneda !== 'PYG') {
        actualizarTasasDesdeAPI();
    }
    
    // Calcular monto en PYG con tasas actualizadas
    $montoPYG = calcularMontoEnPYG($monto, $moneda);
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO suscripciones 
            (user_id, nombre, monto, moneda, monto_pyg, icono, color, dia_cobro, descripcion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId,
            $nombre,
            $monto,
            $moneda,
            $montoPYG,
            $icono,
            $color,
            $diaCobro,
            $descripcion
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Actualizar una suscripción
 * @param int $id
 * @param string $nombre
 * @param float $monto
 * @param string $moneda
 * @param string $icono
 * @param string $color
 * @param int $diaCobro
 * @param string $descripcion
 * @return bool
 */
function actualizarSuscripcion($id, $nombre, $monto, $moneda, $icono, $color, $diaCobro, $descripcion = null) {
    if (empty($nombre) || $monto <= 0 || empty($moneda) || empty($icono)) {
        return false;
    }
    
    // Actualizar tasas desde API si la moneda no es PYG
    if ($moneda !== 'PYG') {
        actualizarTasasDesdeAPI();
    }
    
    // Calcular monto en PYG con tasas actualizadas
    $montoPYG = calcularMontoEnPYG($monto, $moneda);
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            UPDATE suscripciones 
            SET nombre = ?, monto = ?, moneda = ?, monto_pyg = ?, icono = ?, color = ?, dia_cobro = ?, descripcion = ?
            WHERE id = ? AND user_id = ?
        ");
        
        return $stmt->execute([
            $nombre,
            $monto,
            $moneda,
            $montoPYG,
            $icono,
            $color,
            $diaCobro,
            $descripcion,
            $id,
            getUserId()
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Eliminar (desactivar) una suscripción
 * @param int $id
 * @return bool
 */
function eliminarSuscripcion($id) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            UPDATE suscripciones 
            SET activo = 0 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$id, getUserId()]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Calcular el total de suscripciones en PYG
 * @param int $userId
 * @return float
 */
function calcularTotalSuscripciones($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto_pyg), 0) as total 
            FROM suscripciones 
            WHERE user_id = ? AND activo = 1
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total']);
    } catch (PDOException $e) {
        return 0;
    }
}

// ==========================================
// FUNCIONES DE AHORRO ACUMULADO
// ==========================================

/**
 * Registrar ahorro del periodo cerrado
 * @param int $userId
 * @param string $periodoInicio
 * @param string $periodoFin
 * @param float $ingresoReal
 * @param float $gastosTotales
 * @param string $notas
 * @return bool
 */
function registrarAhorroPeriodo($userId, $periodoInicio, $periodoFin, $ingresoReal, $gastosTotales, $notas = null) {
    try {
        $pdo = getDBConnection();
        $montoAhorrado = $ingresoReal - $gastosTotales;
        
        $stmt = $pdo->prepare("
            INSERT INTO ahorro_historico (user_id, periodo_inicio, periodo_fin, ingreso_real, gastos_totales, monto_ahorrado, notas)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId,
            $periodoInicio,
            $periodoFin,
            $ingresoReal,
            $gastosTotales,
            $montoAhorrado,
            $notas
        ]);
    } catch (PDOException $e) {
        error_log("Error al registrar ahorro: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener ahorro acumulado total del usuario
 * @param int $userId
 * @return float
 */
function obtenerAhorroAcumulado($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto_ahorrado), 0) as total_ahorrado
            FROM ahorro_historico
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total_ahorrado']);
    } catch (PDOException $e) {
        error_log("Error al obtener ahorro acumulado: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtener historial de ahorros por periodo
 * @param int $userId
 * @param int $limit
 * @return array
 */
function obtenerHistorialAhorro($userId = null, $limit = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    try {
        $pdo = getDBConnection();
        $sql = "
            SELECT 
                id,
                periodo_inicio,
                periodo_fin,
                ingreso_real,
                gastos_totales,
                monto_ahorrado,
                notas,
                created_at
            FROM ahorro_historico
            WHERE user_id = ?
            ORDER BY periodo_inicio DESC
        ";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener historial de ahorro: " . $e->getMessage());
        return [];
    }
}

/**
 * Calcular ahorro del periodo actual (sin guardar)
 * @param int $userId
 * @return array ['ingreso', 'gastos_totales', 'ahorro_proyectado']
 */
function calcularAhorroPeriodoActual($userId = null) {
    if ($userId === null) {
        $userId = getUserId();
    }
    
    $estado = calcularEstadoFinanciero($userId);
    
    if (!$estado) {
        return [
            'ingreso' => 0,
            'gastos_totales' => 0,
            'ahorro_proyectado' => 0
        ];
    }
    
    // Total de gastos = gastos_fijos + gastos_variables
    $gastosTotales = $estado['gastos_fijos'] + $estado['gastos_variables'];
    $ahorroProyectado = $estado['ingreso_mensual'] - $gastosTotales;
    
    return [
        'ingreso' => $estado['ingreso_mensual'],
        'gastos_totales' => $gastosTotales,
        'ahorro_proyectado' => $ahorroProyectado,
        'periodo_inicio' => $estado['fecha_inicio'],
        'periodo_fin' => $estado['fecha_fin']
    ];
}

/**
 * Obtener todas las tasas de cambio
 * @return array
 */
function obtenerTasasCambio() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM tasas_cambio ORDER BY moneda_origen ASC");
        $stmt->execute();
        $tasas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir a array asociativo para fácil acceso
        $resultado = [];
        foreach ($tasas as $tasa) {
            $key = $tasa['moneda_origen'] . '_' . $tasa['moneda_destino'];
            $resultado[$key] = floatval($tasa['tasa']);
        }
        
        return $resultado;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Actualizar tasa de cambio
 * @param string $monedaOrigen
 * @param string $monedaDestino
 * @param float $tasa
 * @return bool
 */
function actualizarTasaCambio($monedaOrigen, $monedaDestino, $tasa) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO tasas_cambio (moneda_origen, moneda_destino, tasa) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE tasa = ?, fecha_actualizacion = CURRENT_TIMESTAMP
        ");
        $resultado = $stmt->execute([$monedaOrigen, $monedaDestino, $tasa, $tasa]);
        
        if (!$resultado) {
            error_log("Error actualizando tasa $monedaOrigen->$monedaDestino: " . print_r($stmt->errorInfo(), true));
        }
        
        return $resultado;
    } catch (PDOException $e) {
        error_log("Excepción actualizando tasa de cambio: " . $e->getMessage());
        $_SESSION['error_message'] = "Error de base de datos: " . $e->getMessage();
        return false;
    }
}

/**
 * Convertir monto de una moneda a otra
 * @param float $monto
 * @param string $monedaOrigen
 * @param string $monedaDestino
 * @return float
 */
function convertirMoneda($monto, $monedaOrigen, $monedaDestino = 'PYG') {
    if ($monedaOrigen === $monedaDestino) {
        return $monto;
    }
    
    $tasas = obtenerTasasCambio();
    $key = $monedaOrigen . '_' . $monedaDestino;
    
    if (isset($tasas[$key])) {
        return $monto * $tasas[$key];
    }
    
    // Si no existe la tasa, retornar el monto original
    return $monto;
}

/**
 * Calcular monto en guaraníes (PYG)
 * @param float $monto
 * @param string $moneda
 * @return float
 */
function calcularMontoEnPYG($monto, $moneda) {
    return convertirMoneda($monto, $moneda, 'PYG');
}

