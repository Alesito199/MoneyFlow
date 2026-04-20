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
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total 
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total']);
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

