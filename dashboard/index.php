<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Obtener estado financiero del usuario logueado
$estado = calcularEstadoFinanciero();

if (!$estado) {
    $userId = getUserId();
    die("
    <div style='font-family: Arial; padding: 40px; text-align: center;'>
        <h2 style='color: #dc2626;'>❌ Error de Configuración</h2>
        <p>No se encontró configuración para tu usuario (ID: {$userId})</p>
        <h3 style='margin-top: 30px;'>Posibles soluciones:</h3>
        <ol style='text-align: left; max-width: 600px; margin: 20px auto;'>
            <li><strong>Reimportar la base de datos:</strong><br>
                <code style='background: #f3f4f6; padding: 5px;'>mysql -u root -p moneyflaw < sql/schema.sql</code>
            </li>
        </ol>
        <a href='../logout.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4b5563; color: white; text-decoration: none; border-radius: 5px;'>Cerrar Sesión</a>
    </div>
    ");
}

// Obtener análisis de ritmo
$ritmo = analizarRitmoGasto();

// Obtener gastos recientes (últimos 10)
$gastosRecientes = obtenerGastosRecientes(null, 10);

// Obtener gastos por categoría para el gráfico
$gastosPorCategoria = obtenerGastosPorCategoria($estado['fecha_inicio'], $estado['fecha_fin']);

// Obtener presupuesto diario
$presupuestoDiario = calcularPresupuestoDiario();

// Obtener resumen semanal
$resumenSemanal = calcularResumenSemanal();

// Verificar si es tiempo de reiniciar
$verificarReinicio = verificarReinicioPeriodo();

// Verificar si el usuario es admin
$esAdmin = isAdmin();

// Obtener suscripciones
$suscripciones = obtenerSuscripciones();
$totalSuscripciones = calcularTotalSuscripciones();

// Obtener ahorro acumulado histórico
$ahorroAcumulado = obtenerAhorroAcumulado();
$ahorroActual = calcularAhorroPeriodoActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MoneyFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Barra superior móvil (hamburguesa + título) -->
    <div class="mobile-header">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-header-title">
            <i class="fas fa-wallet"></i>
            <span>MoneyFlow</span>
        </div>
    </div>

    <!-- Overlay para cerrar menú en móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-wallet"></i>
                <h2>MoneyFlow</h2>
                <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Cerrar men\u00fa">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sidebar-menu">
                <a href="index.php" class="menu-item active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="../forms/add_expense.php" class="menu-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Agregar Gasto</span>
                </a>
                <a href="expenses.php" class="menu-item">
                    <i class="fas fa-list"></i>
                    <span>Gastos Variables</span>
                </a>
                <a href="gastos_fijos.php" class="menu-item">
                    <i class="fas fa-receipt"></i>
                    <span>Gastos Fijos</span>
                </a>
                <a href="suscripciones_new.php" class="menu-item">
                    <i class="fas fa-sync-alt"></i>
                    <span>Suscripciones</span>
                </a>
                <a href="configuracion.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </div>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr(getUsername(), 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo getUsername(); ?></div>
                        <div class="user-role"><?php echo $esAdmin ? 'Administrador' : 'Usuario'; ?></div>
                    </div>
                </div>
                <a href="../logout.php" class="btn btn-secondary btn-block">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Vista general de tus finanzas personales</p>
            </div>

            <!-- Alerta de Reinicio de Periodo -->
            <?php if ($verificarReinicio['debe_reiniciar']): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <strong>¡Hoy es día 25!</strong> Es momento de iniciar un nuevo periodo de control.
                        <a href="configuracion.php" style="color: inherit; text-decoration: underline; font-weight: bold;">
                            Ir a Configuración →
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Alerta de Ritmo de Gasto -->
            <?php if ($ritmo['en_riesgo']): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Alerta:</strong> <?php echo $ritmo['mensaje']; ?>. 
                        Has gastado <?php echo formatearMoneda($ritmo['gasto_real']); ?> 
                        y se esperaba <?php echo formatearMoneda($ritmo['gasto_esperado']); ?>.
                    </div>
                </div>
            <?php endif; ?>

            <!-- KPIs Principales -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: #4b5563;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Ingreso Mensual</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($estado['ingreso_mensual']); ?></div>
                        <div class="kpi-label">Ingresos del mes</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background: #6b7280;">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Ahorro</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($estado['monto_ahorro']); ?></div>
                        <div class="kpi-label">Meta de ahorro mensual</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background: #4b5563;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Gastos Fijos</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($estado['gastos_fijos_tradicionales']); ?></div>
                        <div class="kpi-label">Gastos mensuales fijos</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background: #6b7280;">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Suscripciones</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($estado['gastos_suscripciones']); ?></div>
                        <div class="kpi-label">Total mensual suscripciones</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background: #6b7280;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Gastos Variables</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($estado['gastos_variables']); ?></div>
                        <div class="kpi-label">Del periodo actual</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background: #4b5563;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Disponible</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($estado['disponible']); ?></div>
                        <div class="kpi-label">Ingreso - Ahorro</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background: <?php echo $estado['disponible_real'] >= 0 ? '#4b5563' : '#dc2626'; ?>;">
                        <i class="fas fa-<?php echo $estado['disponible_real'] >= 0 ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Disponible Real</h3>
                        <div class="kpi-value" style="color: <?php echo $estado['disponible_real'] >= 0 ? '#10b981' : '#ef4444'; ?>">
                            <?php echo formatearMoneda($estado['disponible_real']); ?>
                        </div>
                        <div class="kpi-label">Después de gastos</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Ahorro Acumulado</h3>
                        <div class="kpi-value" style="color: #10b981;">
                            <?php echo formatearMoneda($ahorroAcumulado); ?>
                        </div>
                        <div class="kpi-label">Total histórico ahorrado</div>
                    </div>
                </div>
            </div>

            <!-- Presupuesto Diario (Destacado) -->
            <div class="card" style="background: linear-gradient(135deg, #4b5563 0%, #374151 100%); color: white; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
                <div style="padding: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; color: white;">
                            <i class="fas fa-calendar-day"></i> Presupuesto de HOY
                        </h2>
                        <span style="font-size: 14px; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px;">
                            <?php echo date('d/m/Y'); ?>
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                        <!-- Presupuesto Diario -->
                        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">
                                <i class="fas fa-coins"></i> Puedes gastar hoy
                            </div>
                            <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px;">
                                <?php echo formatearMoneda($presupuestoDiario['presupuesto_diario']); ?>
                            </div>
                            <div style="font-size: 12px; opacity: 0.8;">
                                Presupuesto diario
                            </div>
                        </div>

                        <!-- Gastado Hoy -->
                        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">
                                <i class="fas fa-receipt"></i> Ya gastaste
                            </div>
                            <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px; color: <?php echo $presupuestoDiario['dentro_presupuesto'] ? '#fff' : '#ffeb3b'; ?>;">
                                <?php echo formatearMoneda($presupuestoDiario['gastado_hoy']); ?>
                            </div>
                            <div style="font-size: 12px; opacity: 0.8;">
                                <?php echo round($presupuestoDiario['porcentaje_usado_hoy'], 1); ?>% del presupuesto
                            </div>
                        </div>

                        <!-- Disponible Hoy -->
                        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">
                                <i class="fas fa-wallet"></i> Te queda por hoy
                            </div>
                            <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px; color: <?php echo $presupuestoDiario['disponible_hoy'] >= 0 ? '#4ade80' : '#f87171'; ?>;">
                                <?php echo formatearMoneda(max(0, $presupuestoDiario['disponible_hoy'])); ?>
                            </div>
                            <div style="font-size: 12px; opacity: 0.8;">
                                <?php if ($presupuestoDiario['disponible_hoy'] < 0): ?>
                                    ⚠️ Excediste el límite
                                <?php else: ?>
                                    ✅ <?php echo $presupuestoDiario['mensaje']; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Días Restantes -->
                        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 12px; backdrop-filter: blur(10px);">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">
                                <i class="fas fa-calendar-check"></i> Días restantes
                            </div>
                            <div style="font-size: 32px; font-weight: 700; margin-bottom: 5px;">
                                <?php echo $presupuestoDiario['dias_restantes']; ?> días
                            </div>
                            <div style="font-size: 12px; opacity: 0.8;">
                                Hasta el próximo periodo
                            </div>
                        </div>
                    </div>

                    <!-- Barra de progreso del día -->
                    <div style="margin-top: 25px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                            <span>Progreso del gasto de hoy</span>
                            <span><strong><?php echo round($presupuestoDiario['porcentaje_usado_hoy'], 1); ?>%</strong></span>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); border-radius: 10px; height: 20px; overflow: hidden;">
                            <div style="background: <?php echo $presupuestoDiario['dentro_presupuesto'] ? 'linear-gradient(90deg, #4ade80, #22c55e)' : 'linear-gradient(90deg, #fbbf24, #f59e0b)'; ?>; 
                                        height: 100%; width: <?php echo min(100, $presupuestoDiario['porcentaje_usado_hoy']); ?>%; 
                                        transition: width 0.5s ease; border-radius: 10px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen Semanal -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Resumen de los Últimos 7 Días
                    </h3>
                </div>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-label">Presupuesto Semanal</div>
                        <div class="stat-value"><?php echo formatearMoneda($resumenSemanal['presupuesto_semanal']); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Gastado últimos 7 días</div>
                        <div class="stat-value" style="color: <?php echo $resumenSemanal['dentro_presupuesto'] ? 'var(--success)' : 'var(--danger)'; ?>">
                            <?php echo formatearMoneda($resumenSemanal['gastado_semanal']); ?>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Disponible Semanal</div>
                        <div class="stat-value">
                            <?php echo formatearMoneda(max(0, $resumenSemanal['disponible_semanal'])); ?>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">% Usado</div>
                        <div class="stat-value">
                            <?php echo round($resumenSemanal['porcentaje_usado'], 1); ?>%
                            <?php if ($resumenSemanal['dentro_presupuesto']): ?>
                                <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mis Suscripciones -->
            <?php if (!empty($suscripciones)): ?>
            <div class="card" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e5e7eb;">
                    <h3 class="card-title" style="font-size: 20px; font-weight: 700;">
                        <i class="fas fa-sync-alt" style="color: #4b5563;"></i> Mis Suscripciones Activas
                    </h3>
                    <a href="suscripciones_new.php" class="btn btn-sm btn-primary" style="background: linear-gradient(135deg, #4b5563 0%, #374151 100%); border: none; padding: 8px 16px; border-radius: 8px;">
                        <i class="fas fa-cog"></i> Administrar
                    </a>
                </div>
                
                <div style="padding: 25px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 18px; margin-bottom: 25px;">
                        <?php foreach ($suscripciones as $sub): ?>
                            <div style="background: white; border-radius: 16px; padding: 20px; text-align: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 2px solid transparent;" 
                                 onmouseover="this.style.borderColor='<?php echo htmlspecialchars($sub['color']); ?>'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.12)';"
                                 onmouseout="this.style.borderColor='transparent'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';"
                                 onclick="window.location.href='suscripciones_new.php'">
                                <div style="width: 60px; height: 60px; margin: 0 auto 12px; background: <?php echo htmlspecialchars($sub['color']); ?>; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
                                    <i class="fas <?php echo htmlspecialchars($sub['icono']); ?>"></i>
                                </div>
                                <div style="font-weight: 600; font-size: 15px; margin-bottom: 6px; color: #1f2937; line-height: 1.3;">
                                    <?php echo htmlspecialchars($sub['nombre']); ?>
                                </div>
                                <div style="display: inline-block; padding: 3px 8px; background: <?php echo $sub['moneda'] === 'USD' ? '#d1fae5' : ($sub['moneda'] === 'EUR' ? '#dbeafe' : '#f3f4f6'); ?>; color: <?php echo $sub['moneda'] === 'USD' ? '#065f46' : ($sub['moneda'] === 'EUR' ? '#1e40af' : '#4b5563'); ?>; border-radius: 6px; font-size: 10px; font-weight: 700; margin-bottom: 6px;">
                                    <?php echo $sub['moneda']; ?>
                                </div>
                                <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">
                                    <?php if ($sub['moneda'] !== 'PYG'): ?>
                                        <span style="display: block; font-size: 11px; color: #9ca3af;">
                                            <?php echo $sub['moneda']; ?> <?php echo number_format($sub['monto'], 2); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span style="font-weight: 700; color: #1f2937; font-size: 16px;">
                                        ₲<?php echo number_format($sub['monto_pyg'], 0); ?>
                                    </span>
                                </div>
                                <div style="font-size: 11px; color: #9ca3af; margin-top: 6px;">
                                    <i class="fas fa-calendar-alt"></i> Día <?php echo $sub['dia_cobro']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="padding: 20px; background: linear-gradient(135deg, #4b5563 0%, #374151 100%); border-radius: 12px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 12px rgba(75, 85, 99, 0.25);">
                        <div>
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">
                                <i class="fas fa-calculator"></i> Total mensual en suscripciones
                            </div>
                            <div style="font-size: 28px; font-weight: 700;">
                                <?php echo formatearMoneda($totalSuscripciones); ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 4px;">
                                <?php echo count($suscripciones); ?> suscripciones activas
                            </div>
                            <a href="suscripciones_new.php" style="font-size: 13px; color: white; text-decoration: underline; opacity: 0.9; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
                                Ver todas →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Gráfico de Gastos por Categoría -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Gastos por Categoría
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="gastosChart"></canvas>
                </div>
                <?php if (empty($gastosPorCategoria)): ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <p>No hay gastos para mostrar en el gráfico</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Progreso de Ahorro -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-piggy-bank"></i> Progreso del Ahorro
                    </h3>
                    <span>
                        <?php 
                        // Evitar división por cero
                        $progreso = ($estado['objetivo_ahorro'] > 0) 
                            ? ($estado['ahorro_actual'] / $estado['objetivo_ahorro']) * 100 
                            : 0;
                        echo round(max(0, min(100, $progreso)), 1); 
                        ?>%
                    </span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo min(100, max(0, $progreso)); ?>%">
                        <?php if ($progreso > 10): ?>
                            <?php echo round($progreso, 1); ?>%
                        <?php endif; ?>
                    </div>
                </div>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-label">Objetivo</div>
                        <div class="stat-value"><?php echo formatearMoneda($estado['objetivo_ahorro']); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Actual</div>
                        <div class="stat-value"><?php echo formatearMoneda(max(0, $estado['ahorro_actual'])); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Faltante</div>
                        <div class="stat-value">
                            <?php 
                            $faltante = $estado['objetivo_ahorro'] - $estado['ahorro_actual'];
                            echo formatearMoneda(max(0, $faltante)); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análisis de Ritmo -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tachometer-alt"></i> Análisis de Ritmo de Gasto
                    </h3>
                </div>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-label">Día del Periodo</div>
                        <div class="stat-value">
                            <?php echo $ritmo['dias_transcurridos']; ?>/<?php echo $ritmo['dias_totales']; ?>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Gasto Esperado</div>
                        <div class="stat-value"><?php echo formatearMoneda($ritmo['gasto_esperado']); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Gasto Real</div>
                        <div class="stat-value"><?php echo formatearMoneda($ritmo['gasto_real']); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Diferencia</div>
                        <div class="stat-value" style="color: <?php echo $ritmo['en_riesgo'] ? 'var(--danger)' : 'var(--success)'; ?>">
                            <?php echo formatearMoneda(abs($ritmo['diferencia'])); ?>
                            <?php if ($ritmo['diferencia'] > 0): ?>
                                <i class="fas fa-arrow-up"></i>
                            <?php else: ?>
                                <i class="fas fa-arrow-down"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: var(--light); border-radius: 6px; text-align: center;">
                    <i class="fas fa-<?php echo $ritmo['en_riesgo'] ? 'exclamation-circle' : 'check-circle'; ?>" 
                       style="color: <?php echo $ritmo['en_riesgo'] ? 'var(--warning)' : 'var(--success)'; ?>; 
                       margin-right: 8px;"></i>
                    <strong><?php echo $ritmo['mensaje']; ?></strong>
                </div>
            </div>

            <!-- Últimos Gastos -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Últimos Gastos
                    </h3>
                    <a href="../forms/add_expense.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Gasto
                    </a>
                </div>

                <?php if (empty($gastosRecientes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No hay gastos registrados</h3>
                        <p>Comienza registrando tu primer gasto</p>
                        <a href="../forms/add_expense.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Registrar Gasto
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Método</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastosRecientes as $gasto): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($gasto['fecha'])); ?></td>
                                        <td>
                                            <?php 
                                            $tipo = $gasto['tipo'] ?? 'necesario';
                                            if (isset(TIPOS_GASTO[$tipo])):
                                            ?>
                                                <span class="badge badge-<?php echo htmlspecialchars($tipo); ?>">
                                                    <?php echo TIPOS_GASTO[$tipo]; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-necesario">Sin tipo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo CATEGORIAS[$gasto['categoria']] ?? htmlspecialchars($gasto['categoria']); ?></td>
                                        <td><?php echo htmlspecialchars($gasto['descripcion'] ?? ''); ?></td>
                                        <td>
                                            <?php 
                                            $metodo = $gasto['metodo'] ?? 'efectivo';
                                            if (isset(METODOS_PAGO[$metodo])):
                                            ?>
                                                <span class="badge badge-<?php echo htmlspecialchars($metodo); ?>">
                                                    <?php echo METODOS_PAGO[$metodo]; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-efectivo">Sin método</span>
                                            <?php endif; ?>
                                        </td>
                                            </span>
                                        </td>
                                        <td class="text-right"><strong><?php echo formatearMoneda($gasto['monto']); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="padding: 15px; text-align: center;">
                        <a href="expenses.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Ver Todos los Gastos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Datos para el gráfico de gastos por categoría
        <?php if (!empty($gastosPorCategoria)): ?>
        const ctx = document.getElementById('gastosChart');
        
        const categorias = <?php echo json_encode(array_map(function($g) {
            $nombres = [
                'comida' => 'Comida',
                'transporte' => 'Transporte',
                'salud' => 'Salud',
                'entretenimiento' => 'Entretenimiento',
                'servicios' => 'Servicios',
                'otros' => 'Otros'
            ];
            return $nombres[$g['categoria']] ?? $g['categoria'];
        }, $gastosPorCategoria)); ?>;
        
        const montos = <?php echo json_encode(array_map(function($g) {
            return floatval($g['total']);
        }, $gastosPorCategoria)); ?>;

        const colores = [
            'rgba(102, 126, 234, 0.8)',
            'rgba(118, 75, 162, 0.8)',
            'rgba(237, 100, 166, 0.8)',
            'rgba(255, 154, 158, 0.8)',
            'rgba(250, 208, 196, 0.8)',
            'rgba(155, 93, 229, 0.8)'
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: categorias,
                datasets: [{
                    label: 'Monto Gastado',
                    data: montos,
                    backgroundColor: colores,
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 13
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('es-PY').format(context.parsed) + ' Gs.';
                                return label;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>

    <!-- Script para Men\u00fa Responsive -->
    <script>
        // Elementos del men\u00fa
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

        // Funci\u00f3n para abrir men\u00fa
        function openSidebar() {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Evitar scroll del body
        }

        // Funci\u00f3n para cerrar men\u00fa
        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = ''; // Restaurar scroll
        }

        // Event listeners
        hamburgerBtn.addEventListener('click', openSidebar);
        sidebarCloseBtn.addEventListener('click', closeSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        // Cerrar men\u00fa al hacer clic en un enlace (solo en m\u00f3vil)
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Cerrar men\u00fa al cambiar tama\u00f1o de ventana (si pasa a desktop)
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
    </script>
</body>
</html>
