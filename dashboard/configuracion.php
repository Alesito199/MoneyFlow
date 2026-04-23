<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';

// Auto-configurar si se detecta día 25
$autoConfigurar = false;
if (isset($_GET['autoconfigurar']) && $_GET['autoconfigurar'] === '1') {
    $autoConfigurar = true;
    $verificarReinicio = verificarReinicioPeriodo();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cerrar periodo y registrar ahorro
    if (isset($_POST['accion']) && $_POST['accion'] === 'cerrar_periodo') {
        $userId = getUserId();
        $ahorroActual = calcularAhorroPeriodoActual($userId);
        $notas = $_POST['notas'] ?? null;
        
        if (registrarAhorroPeriodo(
            $userId,
            $ahorroActual['periodo_inicio'],
            $ahorroActual['periodo_fin'],
            $ahorroActual['ingreso'],
            $ahorroActual['gastos_totales'],
            $notas
        )) {
            $ahorroAcumulado = obtenerAhorroAcumulado($userId);
            $montoAhorrado = $ahorroActual['ahorro_proyectado'];
            $mensaje = "¡Periodo cerrado exitosamente! Se registraron " . formatearMoneda($montoAhorrado) . " en tu ahorro. Total acumulado: " . formatearMoneda($ahorroAcumulado);
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al registrar el ahorro del periodo';
            $tipo_mensaje = 'error';
        }
    }
    // Actualizar configuración normal
    else {
        $ingresoMensual = floatval($_POST['ingreso_mensual'] ?? 0);
        $montoAhorro = floatval($_POST['monto_ahorro'] ?? 0);
        $montoGourmet = floatval($_POST['monto_gourmet'] ?? 0);
        $fechaInicio = $_POST['fecha_inicio'];
        $fechaFin = $_POST['fecha_fin'];
        
        if (actualizarConfiguracion($ingresoMensual, $montoAhorro, $montoGourmet, $fechaInicio, $fechaFin)) {
            $mensaje = 'Configuración actualizada exitosamente';
            $tipo_mensaje = 'success';
            $config = obtenerConfiguracion(); // Recargar configuración
        } else {
            $mensaje = 'Error al actualizar la configuración';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener configuración actual
if (!isset($config)) {
    $config = obtenerConfiguracion();
}

// Obtener verificación de reinicio
$verificarReinicio = verificarReinicioPeriodo();

// Obtener información de ahorro
$ahorroAcumulado = obtenerAhorroAcumulado();
$ahorroActual = calcularAhorroPeriodoActual();
$historialAhorro = obtenerHistorialAhorro(null, 5); // Últimos 5 periodos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - MoneyFlow</title>
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
                <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Cerrar menú">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sidebar-menu">
                <a href="index.php" class="menu-item">
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
                <a href="configuracion.php" class="menu-item active">
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
                        <div class="user-role"><?php echo isAdmin() ? 'Administrador' : 'Usuario'; ?></div>
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
                <h1>Configuración</h1>
                <p>Ajusta tu ingreso mensual, ahorro y periodo de control</p>
            </div>

            <?php if ($verificarReinicio['debe_reiniciar']): ?>
                <div class="alert" style="background: linear-gradient(135deg, #4b5563 0%, #374151 100%); color: white; border: none;">
                    <i class="fas fa-magic"></i>
                    <div style="flex: 1;">
                        <strong>🎉 ¡Hoy es día 25! Nuevo periodo disponible</strong><br>
                        <span style="font-size: 14px; opacity: 0.9;">
                            El sistema puede configurar automáticamente tu nuevo periodo desde hoy hasta el 24 del próximo mes.
                        </span>
                    </div>
                    <button onclick="autoConfigurarPeriodo()" class="btn" style="background: white; color: #4b5563; border: none;">
                        <i class="fas fa-bolt"></i> Configurar Automáticamente
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Configuración Financiera -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-dollar-sign"></i> Configuración Financiera
                    </h3>
                </div>

                <form method="POST" action="" class="form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ingreso_mensual">
                                <i class="fas fa-wallet"></i> Ingreso Mensual *
                            </label>
                            <input 
                                type="number" 
                                id="ingreso_mensual" 
                                name="ingreso_mensual" 
                                value="<?php echo $config['ingreso_mensual']; ?>" 
                                min="0"
                                step="0.01"
                                class="form-control"
                                required>
                            <small>Tu ingreso total del mes</small>
                        </div>

                        <div class="form-group">
                            <label for="monto_ahorro">
                                <i class="fas fa-piggy-bank"></i> Monto de Ahorro *
                            </label>
                            <input 
                                type="number" 
                                id="monto_ahorro" 
                                name="monto_ahorro" 
                                value="<?php echo $config['monto_ahorro']; ?>" 
                                min="0"
                                step="0.01"
                                class="form-control"
                                required>
                            <small>Cuánto quieres ahorrar cada mes</small>
                        </div>

                        <div class="form-group">
                            <label for="monto_gourmet">
                                <i class="fas fa-credit-card"></i> Monto Gourmet (Opcional)
                            </label>
                            <input 
                                type="number" 
                                id="monto_gourmet" 
                                name="monto_gourmet" 
                                value="<?php echo $config['monto_gourmet']; ?>" 
                                min="0"
                                step="0.01"
                                class="form-control">
                            <small>Presupuesto para tarjeta gourmet</small>
                        </div>
                    </div>

                    <hr style="margin: 30px 0; border: none; border-top: 1px solid var(--border);">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio">
                                <i class="fas fa-calendar-plus"></i> Fecha Inicio *
                            </label>
                            <input 
                                type="date" 
                                id="fecha_inicio" 
                                name="fecha_inicio" 
                                value="<?php echo $autoConfigurar && $verificarReinicio['debe_reiniciar'] ? $verificarReinicio['fecha_inicio_sugerida'] : $config['fecha_inicio']; ?>" 
                                class="form-control"
                                required>
                            <small>Día en que comienza el periodo (recomendado: día 25)</small>
                        </div>

                        <div class="form-group">
                            <label for="fecha_fin">
                                <i class="fas fa-calendar-check"></i> Fecha Fin *
                            </label>
                            <input 
                                type="date" 
                                id="fecha_fin" 
                                name="fecha_fin" 
                                value="<?php echo $autoConfigurar && $verificarReinicio['debe_reiniciar'] ? $verificarReinicio['fecha_fin_sugerida'] : $config['fecha_fin']; ?>" 
                                class="form-control"
                                required>
                            <small>Último día del periodo (recomendado: día 24 del mes siguiente)</small>
                        </div>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resumen de Cálculos -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator"></i> Resumen de Cálculos
                    </h3>
                </div>
                
                <table class="table">
                    <tr>
                        <th>Ingreso Mensual</th>
                        <td><strong><?php echo formatearMoneda($config['ingreso_mensual']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Monto de Ahorro</th>
                        <td><?php echo formatearMoneda($config['monto_ahorro']); ?></td>
                    </tr>
                    <tr style="background: var(--light);">
                        <th><strong>Disponible (Ingreso - Ahorro)</strong></th>
                        <td><strong><?php echo formatearMoneda($config['ingreso_mensual'] - $config['monto_ahorro']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Total Gastos Fijos</th>
                        <td><?php echo formatearMoneda(calcularTotalGastosFijos()); ?></td>
                    </tr>
                    <tr style="background: var(--light);">
                        <th><strong>Disponible para Gastos Variables</strong></th>
                        <td><strong><?php echo formatearMoneda($config['ingreso_mensual'] - $config['monto_ahorro'] - calcularTotalGastosFijos()); ?></strong></td>
                    </tr>
                    <tr style="background: linear-gradient(135deg, #4b5563 0%, #374151 100%); color: white;">
                        <th style="color: white;"><strong><i class="fas fa-calendar-day"></i> PRESUPUESTO DIARIO</strong></th>
                        <td style="color: white;">
                            <strong style="font-size: 18px;">
                                <?php 
                                $fechaInicio = new DateTime($config['fecha_inicio']);
                                $fechaFin = new DateTime($config['fecha_fin']);
                                $diasTotales = $fechaInicio->diff($fechaFin)->days + 1;
                                $disponibleTotal = $config['ingreso_mensual'] - $config['monto_ahorro'] - calcularTotalGastosFijos();
                                $presupuestoDiario = $diasTotales > 0 ? $disponibleTotal / $diasTotales : 0;
                                echo formatearMoneda($presupuestoDiario);
                                ?>
                            </strong>
                            <br>
                            <small style="opacity: 0.9;">
                                (<?php echo $diasTotales; ?> días en el periodo)
                            </small>
                        </td>
                    </tr>
                </table>

                <div style="padding: 20px; background: #dbeafe; border-radius: 6px; margin-top: 20px;">
                    <strong>💡 Cómo funciona:</strong><br>
                    • <strong>Disponible</strong> = Ingreso - Ahorro<br>
                    • <strong>Disponible para Gastos</strong> = Disponible - Gastos Fijos<br>
                    • <strong>Presupuesto Diario</strong> = Disponible para Gastos ÷ Días del Periodo<br>
                    • Este es el dinero que puedes gastar <strong>por día</strong> sin exceder tu presupuesto<br>
                    • El dashboard te mostrará cuánto has gastado hoy y cuánto te queda
                </div>

                <div style="padding: 20px; background: #fef3c7; border-radius: 6px; margin-top: 15px; border-left: 4px solid #f59e0b;">
                    <strong>📅 Recomendación:</strong><br>
                    Para un control mensual efectivo, configura tu periodo del <strong>25 de cada mes al 24 del siguiente mes</strong>.
                    El sistema te alertará cada día 25 para que inicies un nuevo periodo.
                </div>
            </div>

            <!-- Cerrar Periodo y Registrar Ahorro -->
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                    <h3 class="card-title">
                        <i class="fas fa-piggy-bank"></i> Cerrar Periodo y Registrar Ahorro
                    </h3>
                </div>

                <div style="padding: 30px;">
                    <!-- Resumen del Periodo Actual -->
                    <div style="background: #f0fdf4; padding: 20px; border-radius: 12px; margin-bottom: 25px;">
                        <h4 style="color: #059669; margin-bottom: 15px;">
                            <i class="fas fa-calendar-check"></i> Periodo Actual
                        </h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                            <div>
                                <strong>Fechas:</strong><br>
                                <span style="font-size: 14px;">
                                    <?php echo date('d/m/Y', strtotime($ahorroActual['periodo_inicio'])); ?> - 
                                    <?php echo date('d/m/Y', strtotime($ahorroActual['periodo_fin'])); ?>
                                </span>
                            </div>
                            <div>
                                <strong>Ingreso:</strong><br>
                                <span style="font-size: 18px; color: #10b981;">
                                    <?php echo formatearMoneda($ahorroActual['ingreso']); ?>
                                </span>
                            </div>
                            <div>
                                <strong>Gastos Totales:</strong><br>
                                <span style="font-size: 18px; color: #ef4444;">
                                    <?php echo formatearMoneda($ahorroActual['gastos_totales']); ?>
                                </span>
                            </div>
                            <div style="background: white; padding: 15px; border-radius: 8px; border: 2px solid #10b981;">
                                <strong>Ahorro del Periodo:</strong><br>
                                <span style="font-size: 22px; font-weight: bold; color: #059669;">
                                    <?php echo formatearMoneda($ahorroActual['ahorro_proyectado']); ?>
                                </span>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px; text-align: center;">
                            <strong style="font-size: 16px; color: #059669;">
                                💰 Ahorro Acumulado Total: <?php echo formatearMoneda($ahorroAcumulado); ?>
                            </strong>
                        </div>
                    </div>

                    <!-- Formulario para Cerrar Periodo -->
                    <form method="POST" style="margin-bottom: 25px;">
                        <input type="hidden" name="accion" value="cerrar_periodo">
                        <div class="form-group">
                            <label for="notas">
                                <i class="fas fa-sticky-note"></i> Notas del Periodo (Opcional)
                            </label>
                            <textarea 
                                id="notas" 
                                name="notas" 
                                class="form-control" 
                                rows="3" 
                                placeholder="Ej: Mes con gastos extra por reparaciones, vacaciones, etc."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; width: 100%;">
                            <i class="fas fa-check-circle"></i> Cerrar Periodo y Guardar Ahorro
                        </button>
                    </form>

                    <!-- Historial de Ahorros -->
                    <?php if (!empty($historialAhorro)): ?>
                        <div style="margin-top: 30px;">
                            <h4 style="color: #374151; margin-bottom: 15px;">
                                <i class="fas fa-history"></i> Historial de Ahorros (Últimos 5 periodos)
                            </h4>
                            <table class="table" style="font-size: 14px;">
                                <thead>
                                    <tr style="background: #f3f4f6;">
                                        <th>Periodo</th>
                                        <th>Ingreso</th>
                                        <th>Gastos</th>
                                        <th style="color: #059669;">Ahorrado</th>
                                        <th>Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historialAhorro as $registro): ?>
                                        <tr>
                                            <td>
                                                <small>
                                                    <?php echo date('d/m/Y', strtotime($registro['periodo_inicio'])); ?><br>
                                                    <?php echo date('d/m/Y', strtotime($registro['periodo_fin'])); ?>
                                                </small>
                                            </td>
                                            <td><?php echo formatearMoneda($registro['ingreso_real']); ?></td>
                                            <td><?php echo formatearMoneda($registro['gastos_totales']); ?></td>
                                            <td style="font-weight: bold; color: <?php echo $registro['monto_ahorrado'] >= 0 ? '#059669' : '#ef4444'; ?>">
                                                <?php echo formatearMoneda($registro['monto_ahorrado']); ?>
                                            </td>
                                            <td><small><?php echo htmlspecialchars($registro['notas'] ?? '-'); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; font-weight: bold;">
                                        <td colspan="3" style="color: white;">TOTAL ACUMULADO</td>
                                        <td colspan="2" style="color: white; font-size: 18px;">
                                            <?php echo formatearMoneda($ahorroAcumulado); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="padding: 20px; background: #fef3c7; border-radius: 8px; text-align: center;">
                            <i class="fas fa-info-circle" style="color: #f59e0b; font-size: 24px; margin-bottom: 10px;"></i><br>
                            <strong>No hay historial de ahorros registrado aún.</strong><br>
                            <small>Cierra tu primer periodo para comenzar a acumular tu historial de ahorros.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function autoConfigurarPeriodo() {
            if (confirm('¿Deseas configurar automáticamente el nuevo periodo desde hoy (25) hasta el 24 del próximo mes?')) {
                window.location.href = 'configuracion.php?autoconfigurar=1';
            }
        }

        // Calcular días del periodo en tiempo real
        document.getElementById('fecha_inicio').addEventListener('change', actualizarInfoPeriodo);
        document.getElementById('fecha_fin').addEventListener('change', actualizarInfoPeriodo);

        function actualizarInfoPeriodo() {
            const inicio = new Date(document.getElementById('fecha_inicio').value);
            const fin = new Date(document.getElementById('fecha_fin').value);
            
            if (inicio && fin && fin > inicio) {
                const diffTime = Math.abs(fin - inicio);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                // Mostrar tooltip o mensaje
                console.log(`Periodo de ${diffDays} días`);
            }
        }
    </script>

    <!-- Script para Menú Responsive -->
    <script>
        // Elementos del menú
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

        // Función para abrir menú
        function openSidebar() {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Función para cerrar menú
        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Event listeners
        hamburgerBtn.addEventListener('click', openSidebar);
        sidebarCloseBtn.addEventListener('click', closeSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        // Cerrar menú al hacer clic en un enlace (solo en móvil)
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Cerrar menú al cambiar tamaño de ventana
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
    </script>
</body>
</html>
