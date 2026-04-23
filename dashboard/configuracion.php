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

// Obtener configuración actual
if (!isset($config)) {
    $config = obtenerConfiguracion();
}

// Obtener verificación de reinicio
$verificarReinicio = verificarReinicioPeriodo();
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
    <div class="app-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-wallet"></i>
                <h2>MoneyFlow</h2>
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
</body>
</html>
