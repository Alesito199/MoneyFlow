<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'agregar') {
        $nombre = trim($_POST['nombre'] ?? '');
        $monto = floatval($_POST['monto'] ?? 0);
        $moneda = $_POST['moneda'] ?? 'PYG';
        $icono = trim($_POST['icono'] ?? 'fa-star');
        $color = trim($_POST['color'] ?? '#4b5563');
        $diaCobro = intval($_POST['dia_cobro'] ?? 1);
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if (agregarSuscripcion($nombre, $monto, $moneda, $icono, $color, $diaCobro, $descripcion)) {
            $mensaje = 'Suscripción agregada exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al agregar la suscripción';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $monto = floatval($_POST['monto'] ?? 0);
        $moneda = $_POST['moneda'] ?? 'PYG';
        $icono = trim($_POST['icono'] ?? 'fa-star');
        $color = trim($_POST['color'] ?? '#4b5563');
        $diaCobro = intval($_POST['dia_cobro'] ?? 1);
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if (actualizarSuscripcion($id, $nombre, $monto, $moneda, $icono, $color, $diaCobro, $descripcion)) {
            $mensaje = 'Suscripción actualizada exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al actualizar la suscripción';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        
        if (eliminarSuscripcion($id)) {
            $mensaje = 'Suscripción eliminada exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar la suscripción';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'actualizar_tasa') {
        $monedaOrigen = $_POST['moneda_origen'] ?? '';
        $tasa = floatval($_POST['tasa'] ?? 0);
        
        if (actualizarTasaCambio($monedaOrigen, 'PYG', $tasa)) {
            $mensaje = 'Tasa de cambio actualizada exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al actualizar la tasa de cambio';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener suscripciones y tasas
$suscripciones = obtenerSuscripciones();
$totalSuscripciones = calcularTotalSuscripciones();
$tasas = obtenerTasasCambio();

// Iconos populares para suscripciones
$iconosPopulares = [
    'fa-film' => 'Películas/Video',
    'fa-music' => 'Música',
    'fa-gamepad' => 'Juegos',
    'fa-dumbbell' => 'Gimnasio',
    'fa-wifi' => 'Internet',
    'fa-mobile-alt' => 'Teléfono',
    'fa-cloud' => 'Almacenamiento',
    'fa-newspaper' => 'Noticias',
    'fa-book' => 'Libros/Educación',
    'fa-utensils' => 'Comida',
    'fa-coffee' => 'Café',
    'fa-tv' => 'TV',
    'fa-headphones' => 'Audio',
    'fa-code' => 'Software',
    'fa-camera' => 'Fotografía',
    'fa-heart' => 'Salud',
    'fa-star' => 'Otros'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripciones - MoneyFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .subscription-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .subscription-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .subscription-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .subscription-info {
            flex: 1;
        }

        .subscription-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .subscription-description {
            font-size: 13px;
            color: #6b7280;
        }

        .subscription-amount {
            text-align: right;
        }

        .subscription-original {
            font-size: 14px;
            color: #6b7280;
        }

        .subscription-pyg {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .subscription-day {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .subscription-actions {
            display: flex;
            gap: 5px;
        }

        .icon-picker {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            gap: 10px;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: #f9fafb;
            border-radius: 5px;
        }

        .icon-option {
            width: 50px;
            height: 50px;
            border: 2px solid #e5e7eb;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .icon-option:hover {
            border-color: #4b5563;
            transform: scale(1.1);
        }

        .icon-option.selected {
            border-color: #4b5563;
            background: #4b5563;
            color: white;
        }

        .currency-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            background: #e5e7eb;
            color: #4b5563;
        }

        .currency-badge.usd {
            background: #dcfce7;
            color: #166534;
        }

        .currency-badge.eur {
            background: #dbeafe;
            color: #1e40af;
        }

        .conversion-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            min-width: 400px;
            display: none;
        }

        .conversion-popup.show {
            display: block;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .popup-overlay.show {
            display: block;
        }

        .conversion-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .conversion-row:last-child {
            border-bottom: none;
        }

        .conversion-label {
            font-weight: 600;
            color: #4b5563;
        }

        .conversion-value {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .tasas-section {
            margin-top: 30px;
        }

        .tasa-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .tasa-label {
            flex: 1;
            font-weight: 600;
        }

        .tasa-value {
            font-size: 18px;
            color: #10b981;
            font-weight: 600;
            min-width: 120px;
        }
    </style>
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
                <a href="suscripciones.php" class="menu-item active">
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
                <h1><i class="fas fa-sync-alt"></i> Mis Suscripciones</h1>
                <p>Administra tus suscripciones mensuales en diferentes monedas</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Resumen -->
            <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 30px;">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background: #4b5563;">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Total Suscripciones</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($totalSuscripciones); ?></div>
                        <div class="kpi-label"><?php echo count($suscripciones); ?> suscripciones activas</div>
                    </div>
                </div>

                <div class="kpi-card" style="cursor: pointer;" onclick="mostrarConversiones()">
                    <div class="kpi-icon" style="background: #10b981;">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Tasas de Cambio</h3>
                        <div class="kpi-value">
                            <span style="font-size: 16px;">USD: <?php echo number_format($tasas['USD_PYG'] ?? 0, 0); ?></span>
                        </div>
                        <div class="kpi-label">Click para ver todas las tasas</div>
                    </div>
                </div>
            </div>

            <!-- Formulario para agregar suscripción -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Agregar Nueva Suscripción
                    </h3>
                </div>

                <form method="POST" action="" class="form">
                    <input type="hidden" name="accion" value="agregar">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre de la Suscripción *</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" 
                                   placeholder="Ej: Netflix, Spotify, Gym" required>
                        </div>

                        <div class="form-group">
                            <label for="monto">Monto *</label>
                            <input type="number" id="monto" name="monto" class="form-control" 
                                   placeholder="0.00" min="0" step="0.01" required 
                                   onchange="calcularConversionPreview()">
                        </div>

                        <div class="form-group">
                            <label for="moneda">Moneda *</label>
                            <select id="moneda" name="moneda" class="form-control" required onchange="calcularConversionPreview()">
                                <option value="PYG">PYG - Guaraníes</option>
                                <option value="USD">USD - Dólares</option>
                                <option value="EUR">EUR - Euros</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dia_cobro">Día de Cobro</label>
                            <input type="number" id="dia_cobro" name="dia_cobro" class="form-control" 
                                   value="1" min="1" max="31" required>
                        </div>

                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="color" id="color" name="color" class="form-control" 
                                   value="#4b5563" style="height: 42px;">
                        </div>

                        <div class="form-group">
                            <label for="icono_hidden">Icono *</label>
                            <input type="hidden" id="icono_hidden" name="icono" value="fa-star" required>
                            <button type="button" class="btn btn-secondary btn-block" onclick="mostrarSelectorIconos()">
                                <i id="icono_preview" class="fa-star fas"></i> Seleccionar Icono
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción (Opcional)</label>
                        <input type="text" id="descripcion" name="descripcion" class="form-control" 
                               placeholder="Ej: Plan Premium, Membresía Anual">
                    </div>

                    <div id="preview_conversion" style="padding: 15px; background: #f3f4f6; border-radius: 5px; margin-bottom: 15px; display: none;">
                        <strong>Conversión a Guaraníes:</strong>
                        <div style="font-size: 18px; color: #10b981; font-weight: 600;" id="preview_amount"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="background: #4b5563 !important;">
                        <i class="fas fa-plus"></i> Agregar Suscripción
                    </button>
                </form>
            </div>

            <!-- Lista de suscripciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Mis Suscripciones
                    </h3>
                </div>

                <?php if (empty($suscripciones)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No hay suscripciones registradas</h3>
                        <p>Agrega tus suscripciones mensuales usando el formulario de arriba</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($suscripciones as $sub): ?>
                        <div class="subscription-card">
                            <div class="subscription-icon" style="background: <?php echo htmlspecialchars($sub['color']); ?>">
                                <i class="fas <?php echo htmlspecialchars($sub['icono']); ?>"></i>
                            </div>
                            
                            <div class="subscription-info">
                                <div class="subscription-name">
                                    <?php echo htmlspecialchars($sub['nombre']); ?>
                                    <span class="currency-badge <?php echo strtolower($sub['moneda']); ?>">
                                        <?php echo $sub['moneda']; ?>
                                    </span>
                                </div>
                                <?php if ($sub['descripcion']): ?>
                                    <div class="subscription-description">
                                        <?php echo htmlspecialchars($sub['descripcion']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="subscription-day">
                                    <i class="fas fa-calendar-alt"></i> Día <?php echo $sub['dia_cobro']; ?> de cada mes
                                </div>
                            </div>
                            
                            <div class="subscription-amount">
                                <?php if ($sub['moneda'] !== 'PYG'): ?>
                                    <div class="subscription-original">
                                        <?php echo $sub['moneda']; ?> <?php echo number_format($sub['monto'], 2); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="subscription-pyg">
                                    <?php echo formatearMoneda($sub['monto_pyg']); ?>
                                </div>
                            </div>
                            
                            <div class="subscription-actions">
                                <button onclick="verDetalles(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['nombre'], ENT_QUOTES); ?>', <?php echo $sub['monto']; ?>, '<?php echo $sub['moneda']; ?>', <?php echo $sub['monto_pyg']; ?>)" 
                                        class="btn btn-sm btn-secondary" title="Ver conversión">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                                <button onclick="editarSuscripcion(<?php echo htmlspecialchars(json_encode($sub), ENT_QUOTES); ?>)" 
                                        class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="eliminarSuscripcion(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['nombre'], ENT_QUOTES); ?>')" 
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="padding: 20px; background: var(--light); border-radius: 8px; margin-top: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong style="font-size: 18px;">TOTAL MENSUAL EN GUARANÍES:</strong>
                            <strong style="font-size: 24px; color: #1f2937;"><?php echo formatearMoneda($totalSuscripciones); ?></strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sección de Tasas de Cambio -->
            <div class="card tasas-section">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Tasas de Cambio
                    </h3>
                </div>

                <div style="padding: 20px;">
                    <p style="color: #6b7280; margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i> Las tasas de cambio se utilizan para convertir tus suscripciones en diferentes monedas a guaraníes.
                    </p>

                    <?php foreach (['USD' => 'Dólar', 'EUR' => 'Euro'] as $moneda => $nombre): ?>
                        <form method="POST" action="" class="tasa-item">
                            <input type="hidden" name="accion" value="actualizar_tasa">
                            <input type="hidden" name="moneda_origen" value="<?php echo $moneda; ?>">
                            
                            <div class="tasa-label">
                                <i class="fas fa-<?php echo $moneda === 'USD' ? 'dollar-sign' : 'euro-sign'; ?>"></i>
                                1 <?php echo $nombre; ?> (<?php echo $moneda; ?>) =
                            </div>
                            
                            <input type="number" name="tasa" class="form-control" 
                                   value="<?php echo $tasas[$moneda . '_PYG'] ?? 0; ?>" 
                                   step="0.01" min="0" required 
                                   style="max-width: 150px;">
                            
                            <span style="margin-left: 10px; color: #6b7280;">PYG</span>
                            
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </form>
                    <?php endforeach; ?>

                    <div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 5px; font-size: 13px;">
                        <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>
                        <strong>Nota:</strong> Actualiza las tasas de cambio regularmente para mantener los cálculos precisos.
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para editar suscripción -->
    <div id="modalEditar" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Suscripción</h3>
                <button onclick="cerrarModal()" class="modal-close">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_nombre">Nombre *</label>
                            <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_monto">Monto *</label>
                            <input type="number" id="edit_monto" name="monto" class="form-control" 
                                   min="0" step="0.01" required onchange="calcularConversionEdit()">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_moneda">Moneda *</label>
                            <select id="edit_moneda" name="moneda" class="form-control" required onchange="calcularConversionEdit()">
                                <option value="PYG">PYG - Guaraníes</option>
                                <option value="USD">USD - Dólares</option>
                                <option value="EUR">EUR - Euros</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_dia_cobro">Día de Cobro</label>
                            <input type="number" id="edit_dia_cobro" name="dia_cobro" class="form-control" 
                                   min="1" max="31" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_color">Color</label>
                            <input type="color" id="edit_color" name="color" class="form-control" 
                                   style="height: 42px;">
                        </div>

                        <div class="form-group">
                            <label for="edit_icono_hidden">Icono *</label>
                            <input type="hidden" id="edit_icono_hidden" name="icono" required>
                            <button type="button" class="btn btn-secondary btn-block" onclick="mostrarSelectorIconosEdit()">
                                <i id="edit_icono_preview" class="fas"></i> Cambiar Icono
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_descripcion">Descripción</label>
                        <input type="text" id="edit_descripcion" name="descripcion" class="form-control">
                    </div>

                    <div id="edit_preview_conversion" style="padding: 15px; background: #f3f4f6; border-radius: 5px; margin-top: 15px; display: none;">
                        <strong>Conversión a Guaraníes:</strong>
                        <div style="font-size: 18px; color: #10b981; font-weight: 600;" id="edit_preview_amount"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="cerrarModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Selector de Iconos -->
    <div id="modalIconos" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-icons"></i> Seleccionar Icono</h3>
                <button onclick="cerrarModalIconos()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="icon-picker">
                    <?php foreach ($iconosPopulares as $icono => $descripcion): ?>
                        <div class="icon-option" onclick="seleccionarIcono('<?php echo $icono; ?>')" 
                             data-icon="<?php echo $icono; ?>" title="<?php echo $descripcion; ?>">
                            <i class="fas <?php echo $icono; ?>" style="font-size: 24px;"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="cerrarModalIconos()" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Popup de Conversión -->
    <div class="popup-overlay" id="popupOverlay" onclick="cerrarConversion()"></div>
    <div class="conversion-popup" id="conversionPopup">
        <h3 style="margin-bottom: 20px;">
            <i class="fas fa-exchange-alt"></i> Conversión de Moneda
        </h3>
        <div id="conversionContent"></div>
        <button onclick="cerrarConversion()" class="btn btn-primary btn-block" style="margin-top: 20px;">
            <i class="fas fa-times"></i> Cerrar
        </button>
    </div>

    <!-- Formulario oculto para eliminar -->
    <form id="formEliminar" method="POST" action="" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <script>
        const tasas = <?php echo json_encode($tasas); ?>;
        let modoEdicion = false;

        function calcularConversionPreview() {
            const monto = parseFloat(document.getElementById('monto').value) || 0;
            const moneda = document.getElementById('moneda').value;
            
            if (monto > 0 && moneda !== 'PYG') {
                const tasaKey = moneda + '_PYG';
                const tasa = tasas[tasaKey] || 0;
                const montoPYG = monto * tasa;
                
                document.getElementById('preview_conversion').style.display = 'block';
                document.getElementById('preview_amount').textContent = 
                    '₲ ' + montoPYG.toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
            } else {
                document.getElementById('preview_conversion').style.display = 'none';
            }
        }

        function calcularConversionEdit() {
            const monto = parseFloat(document.getElementById('edit_monto').value) || 0;
            const moneda = document.getElementById('edit_moneda').value;
            
            if (monto > 0 && moneda !== 'PYG') {
                const tasaKey = moneda + '_PYG';
                const tasa = tasas[tasaKey] || 0;
                const montoPYG = monto * tasa;
                
                document.getElementById('edit_preview_conversion').style.display = 'block';
                document.getElementById('edit_preview_amount').textContent = 
                    '₲ ' + montoPYG.toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
            } else {
                document.getElementById('edit_preview_conversion').style.display = 'none';
            }
        }

        function editarSuscripcion(sub) {
            document.getElementById('edit_id').value = sub.id;
            document.getElementById('edit_nombre').value = sub.nombre;
            document.getElementById('edit_monto').value = sub.monto;
            document.getElementById('edit_moneda').value = sub.moneda;
            document.getElementById('edit_dia_cobro').value = sub.dia_cobro;
            document.getElementById('edit_color').value = sub.color;
            document.getElementById('edit_icono_hidden').value = sub.icono;
            document.getElementById('edit_descripcion').value = sub.descripcion || '';
            
            // Actualizar preview del icono
            document.getElementById('edit_icono_preview').className = 'fas ' + sub.icono;
            
            calcularConversionEdit();
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        function eliminarSuscripcion(id, nombre) {
            if (confirm('¿Estás seguro de eliminar la suscripción "' + nombre + '"?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formEliminar').submit();
            }
        }

        function mostrarSelectorIconos() {
            modoEdicion = false;
            document.getElementById('modalIconos').style.display = 'flex';
        }

        function mostrarSelectorIconosEdit() {
            modoEdicion = true;
            document.getElementById('modalIconos').style.display = 'flex';
        }

        function cerrarModalIconos() {
            document.getElementById('modalIconos').style.display = 'none';
        }

        function seleccionarIcono(icono) {
            // Remover selección previa
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            
            // Marcar como seleccionado
            event.currentTarget.classList.add('selected');
            
            // Actualizar el campo oculto y el preview
            if (modoEdicion) {
                document.getElementById('edit_icono_hidden').value = icono;
                document.getElementById('edit_icono_preview').className = 'fas ' + icono;
            } else {
                document.getElementById('icono_hidden').value = icono;
                document.getElementById('icono_preview').className = 'fas ' + icono;
            }
        }

        function verDetalles(id, nombre, monto, moneda, montoPYG) {
            let html = '<h4 style="margin-bottom: 15px;">' + nombre + '</h4>';
            
            if (moneda !== 'PYG') {
                const tasaKey = moneda + '_PYG';
                const tasa = tasas[tasaKey] || 0;
                
                html += '<div class="conversion-row">';
                html += '<div class="conversion-label">Monto Original:</div>';
                html += '<div class="conversion-value">' + moneda + ' ' + monto.toFixed(2) + '</div>';
                html += '</div>';
                
                html += '<div class="conversion-row">';
                html += '<div class="conversion-label">Tasa de Cambio:</div>';
                html += '<div class="conversion-value">1 ' + moneda + ' = ₲ ' + tasa.toLocaleString('es-PY') + '</div>';
                html += '</div>';
                
                html += '<div class="conversion-row">';
                html += '<div class="conversion-label">Cálculo:</div>';
                html += '<div class="conversion-value">' + monto.toFixed(2) + ' × ' + tasa.toLocaleString('es-PY') + '</div>';
                html += '</div>';
            }
            
            html += '<div class="conversion-row" style="background: #f3f4f6; padding: 20px; border-radius: 5px; margin-top: 10px;">';
            html += '<div class="conversion-label" style="font-size: 18px;">Total en Guaraníes:</div>';
            html += '<div class="conversion-value" style="font-size: 24px; color: #10b981;">₲ ' + 
                    montoPYG.toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0}) + '</div>';
            html += '</div>';
            
            document.getElementById('conversionContent').innerHTML = html;
            document.getElementById('popupOverlay').classList.add('show');
            document.getElementById('conversionPopup').classList.add('show');
        }

        function mostrarConversiones() {
            let html = '<h4 style="margin-bottom: 15px;">Tasas de Cambio Actuales</h4>';
            
            html += '<div class="conversion-row">';
            html += '<div class="conversion-label"><i class="fas fa-dollar-sign"></i> 1 Dólar (USD):</div>';
            html += '<div class="conversion-value">₲ ' + (tasas['USD_PYG'] || 0).toLocaleString('es-PY') + '</div>';
            html += '</div>';
            
            html += '<div class="conversion-row">';
            html += '<div class="conversion-label"><i class="fas fa-euro-sign"></i> 1 Euro (EUR):</div>';
            html += '<div class="conversion-value">₲ ' + (tasas['EUR_PYG'] || 0).toLocaleString('es-PY') + '</div>';
            html += '</div>';
            
            html += '<div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 5px; font-size: 13px;">';
            html += '<i class="fas fa-info-circle" style="color: #f59e0b;"></i> ';
            html += 'Puedes actualizar estas tasas en la sección "Tasas de Cambio" más abajo.';
            html += '</div>';
            
            document.getElementById('conversionContent').innerHTML = html;
            document.getElementById('popupOverlay').classList.add('show');
            document.getElementById('conversionPopup').classList.add('show');
        }

        function cerrarConversion() {
            document.getElementById('popupOverlay').classList.remove('show');
            document.getElementById('conversionPopup').classList.remove('show');
        }

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            const modalEditar = document.getElementById('modalEditar');
            const modalIconos = document.getElementById('modalIconos');
            
            if (event.target === modalEditar) {
                cerrarModal();
            }
            if (event.target === modalIconos) {
                cerrarModalIconos();
            }
        }
    </script>
</body>
</html>
