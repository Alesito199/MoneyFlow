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
            $mensaje = '✓ Suscripción agregada exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '✗ Error al agregar la suscripción';
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
            $mensaje = '✓ Suscripción actualizada exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '✗ Error al actualizar la suscripción';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        
        if (eliminarSuscripcion($id)) {
            $mensaje = '✓ Suscripción eliminada exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '✗ Error al eliminar la suscripción';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'actualizar_tasas_api') {
        if (actualizarTasasDesdeAPI()) {
            // El mensaje de éxito ya está en $_SESSION['success_message']
            $mensaje = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '✓ Tasas actualizadas desde API exitosamente';
            $tipo_mensaje = 'success';
            unset($_SESSION['success_message']);
        } else {
            // Obtener el mensaje de error detallado si existe
            $mensaje = isset($_SESSION['error_message']) ? '✗ ' . $_SESSION['error_message'] : '✗ Error al actualizar tasas desde API';
            $tipo_mensaje = 'error';
            unset($_SESSION['error_message']);
        }
    }
}

// Obtener suscripciones y tasas
$suscripciones = obtenerSuscripciones();
$totalSuscripciones = calcularTotalSuscripciones();
$tasas = obtenerTasasCambio();

// Colores predefinidos vibrantes
$coloresPopulares = [
    '#e50914', '#1db954', '#ff9900', '#0066ff', '#9146ff',
    '#ff6900', '#1da1f2', '#0088cc', '#ff4500', '#00d9ff',
    '#fc427b', '#f59e0b', '#8b5cf6', '#10b981', '#ec4899',
    '#4b5563' // Gris oscuro por defecto
];

// Iconos populares para suscripciones
$iconosPopulares = [
    'fa-film', 'fa-music', 'fa-gamepad', 'fa-dumbbell', 'fa-wifi',
    'fa-mobile-alt', 'fa-cloud', 'fa-tv', 'fa-headphones', 'fa-code',
    'fa-camera', 'fa-utensils', 'fa-coffee', 'fa-shopping-cart', 'fa-heart',
    'fa-book', 'fa-newspaper', 'fa-graduation-cap', 'fa-star', 'fa-bolt'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Suscripciones - MoneyFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=3.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f8f9fa;
        }

        .main-content {
            background: #f8f9fa;
            padding: 30px;
        }

        /* Header Moderno */
        .page-header-modern {
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(75, 85, 99, 0.3);
        }

        .page-header-modern h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-header-modern p {
            font-size: 16px;
            opacity: 0.9;
        }

        .header-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 25px;
        }

        .header-stat {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            border-radius: 12px;
        }

        .header-stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header-stat-label {
            font-size: 13px;
            opacity: 0.9;
        }

        /* Masonry Grid para Suscripciones */
        .subscriptions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .subscription-card-modern {
            background: white;
            border-radius: 20px;
            padding: 25px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .subscription-card-modern:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .subscription-icon-modern {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .subscription-name-modern {
            font-size: 16px;
            font-weight: 600;
            color: #1a202c;
            line-height: 1.3;
        }

        .subscription-price-modern {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .subscription-original-price {
            font-size: 11px;
            color: #718096;
            font-weight: 500;
        }

        .subscription-pyg-price {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
        }

        .subscription-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-usd {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-eur {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-pyg {
            background: #f3f4f6;
            color: #4b5563;
        }

        .subscription-day {
            font-size: 11px;
            color: #9ca3af;
            margin-top: auto;
        }

        .subscription-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .subscription-card-modern:hover .subscription-actions {
            opacity: 1;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: scale(1.1);
        }

        .action-btn.edit {
            background: #3b82f6;
        }

        .action-btn.delete {
            background: #ef4444;
        }

        /* Botón Flotante para Agregar */
        .fab-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(75, 85, 99, 0.4);
            transition: all 0.3s;
            z-index: 1000;
        }

        .fab-button:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 32px rgba(75, 85, 99, 0.5);
        }

        /* Modal Mejorado */
        .modal-modern {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-modern.show {
            display: flex;
        }

        .modal-content-modern {
            background: white;
            border-radius: 24px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header-modern {
            padding: 30px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-header-modern h3 {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
        }

        .modal-body-modern {
            padding: 30px;
        }

        .form-group-modern {
            margin-bottom: 20px;
        }

        .form-group-modern label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control-modern {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s;
            background: white;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row-modern {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        /* Selector de Colores */
        .color-picker-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .color-option {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
        }

        .color-option:hover {
            transform: scale(1.1);
        }

        .color-option.selected {
            border-color: #1a202c;
            box-shadow: 0 0 0 2px white, 0 0 0 4px #1a202c;
        }

        /* Selector de Iconos */
        .icon-picker-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .icon-option {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid #e5e7eb;
            background: white;
            font-size: 20px;
            color: #4b5563;
            transition: all 0.2s;
        }

        .icon-option:hover {
            border-color: #4b5563;
            background: #f3f4f6;
            transform: scale(1.1);
        }

        .icon-option.selected {
            border-color: #4b5563;
            background: #4b5563;
            color: white;
        }

        /* Botones Modernos */
        .btn-modern {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary-modern {
            background: #e5e7eb;
            color: #4b5563;
        }

        .btn-secondary-modern:hover {
            background: #d1d5db;
        }

        .modal-footer-modern {
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Sección de Tasas */
        .tasas-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .tasas-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .tasa-item-modern {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .tasa-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .tasa-info {
            flex: 1;
        }

        .tasa-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .tasa-value {
            font-size: 20px;
            font-weight: 700;
            color: #1a202c;
        }

        .alert-modern {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 80px 15px 20px 15px;
            }

            .subscriptions-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
            }

            .page-header-modern {
                padding: 25px 15px;
                border-radius: 15px;
            }

            .page-header-modern h1 {
                font-size: 24px;
            }

            .page-header-modern p {
                font-size: 13px;
            }

            .header-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin-top: 20px;
            }

            .header-stat {
                padding: 12px 15px;
            }

            .header-stat-value {
                font-size: 20px;
            }

            .header-stat-label {
                font-size: 11px;
            }

            .subscription-card-modern {
                padding: 20px;
            }

            .subscription-icon-modern {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }

            .subscription-name-modern {
                font-size: 15px;
            }

            .fab-button {
                width: 55px;
                height: 55px;
                bottom: 20px;
                right: 20px;
                font-size: 22px;
            }

            .modal-content-modern {
                max-width: 95%;
                margin: 10px;
                max-height: 90vh;
                overflow-y: auto;
            }

            .color-picker, .icon-picker {
                gap: 8px;
            }

            .color-option, .icon-option {
                width: 40px;
                height: 40px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 70px 10px 15px 10px;
            }

            .subscriptions-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .page-header-modern {
                padding: 20px 12px;
                border-radius: 12px;
            }

            .page-header-modern h1 {
                font-size: 20px;
            }

            .page-header-modern p {
                font-size: 12px;
            }

            .header-stats {
                grid-template-columns: 1fr;
                gap: 8px;
                margin-top: 15px;
            }

            .header-stat {
                padding: 10px 12px;
            }

            .header-stat-value {
                font-size: 18px;
            }

            .header-stat-label {
                font-size: 10px;
            }

            .subscription-card-modern {
                padding: 15px;
            }

            .btn-modern {
                padding: 10px 16px;
                font-size: 13px;
            }
        }
    </style>
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
                <a href="suscripciones_new.php" class="menu-item active">
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
            <!-- Header Moderno -->
            <div class="page-header-modern">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                    <div>
                        <h1><i class="fas fa-sync-alt"></i> Mis Suscripciones</h1>
                        <p>Gestiona todas tus suscripciones en un solo lugar con conversión automática</p>
                    </div>
                    <button onclick="mostrarModalTasas()" class="btn-modern btn-secondary-modern" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 10px 20px; font-size: 14px;">
                        <i class="fas fa-sync-alt"></i> Actualizar Tasas
                    </button>
                </div>
                
                <div class="header-stats" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
                    <div class="header-stat">
                        <div class="header-stat-value"><?php echo count($suscripciones); ?></div>
                        <div class="header-stat-label">Suscripciones Activas</div>
                    </div>
                    <div class="header-stat">
                        <div class="header-stat-value"><?php echo formatearMoneda($totalSuscripciones); ?></div>
                        <div class="header-stat-label">Total Mensual</div>
                    </div>
                    <div class="header-stat">
                        <div class="header-stat-value">
                            <i class="fas fa-dollar-sign" style="font-size: 0.7em; opacity: 0.8;"></i>
                            ₲<?php echo number_format($tasas['USD_PYG'] ?? 0, 0); ?>
                        </div>
                        <div class="header-stat-label">Tasa USD hoy</div>
                    </div>
                    <div class="header-stat">
                        <div class="header-stat-value">
                            <i class="fas fa-euro-sign" style="font-size: 0.7em; opacity: 0.8;"></i>
                            ₲<?php echo number_format($tasas['EUR_PYG'] ?? 0, 0); ?>
                        </div>
                        <div class="header-stat-label">Tasa EUR hoy</div>
                    </div>
                </div>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert-modern alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Grid de Suscripciones -->
            <?php if (empty($suscripciones)): ?>
                <div style="text-align: center; padding: 80px 20px; background: white; border-radius: 20px;">
                    <i class="fas fa-inbox" style="font-size: 64px; color: #d1d5db; margin-bottom: 20px;"></i>
                    <h3 style="color: #4b5563; margin-bottom: 10px;">No tienes suscripciones</h3>
                    <p style="color: #9ca3af; margin-bottom: 30px;">Agrega tu primera suscripción para empezar</p>
                    <button onclick="mostrarModalAgregar()" class="btn btn-primary btn-modern">
                        <i class="fas fa-plus"></i> Agregar Suscripción
                    </button>
                </div>
            <?php else: ?>
                <div class="subscriptions-grid">
                    <?php foreach ($suscripciones as $sub): ?>
                        <div class="subscription-card-modern" onclick="verDetalle(<?php echo htmlspecialchars(json_encode($sub), ENT_QUOTES); ?>)">
                            <div class="subscription-actions">
                                <button class="action-btn edit" onclick="event.stopPropagation(); editarSuscripcion(<?php echo htmlspecialchars(json_encode($sub), ENT_QUOTES); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="event.stopPropagation(); eliminarSuscripcion(<?php echo $sub['id']; ?>, '<?php echo htmlspecialchars($sub['nombre'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div class="subscription-icon-modern" style="background: <?php echo htmlspecialchars($sub['color']); ?>">
                                <i class="fas <?php echo htmlspecialchars($sub['icono']); ?>"></i>
                            </div>
                            
                            <div class="subscription-name-modern">
                                <?php echo htmlspecialchars($sub['nombre']); ?>
                            </div>
                            
                            <span class="subscription-badge badge-<?php echo strtolower($sub['moneda']); ?>">
                                <?php echo $sub['moneda']; ?>
                            </span>
                            
                            <div class="subscription-price-modern">
                                <?php if ($sub['moneda'] !== 'PYG'): ?>
                                    <div class="subscription-original-price">
                                        <?php echo $sub['moneda']; ?> <?php echo number_format($sub['monto'], 2); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="subscription-pyg-price">
                                    ₲<?php echo number_format($sub['monto_pyg'], 0); ?>
                                </div>
                            </div>
                            
                            <div class="subscription-day">
                                <i class="fas fa-calendar"></i> Día <?php echo $sub['dia_cobro']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Sección de Tasas (oculta, ahora en modal) -->
            <div class="tasas-card" style="display: none;">
                <div class="tasas-header">
                    <h3 style="font-size: 20px; font-weight: 700; color: #1a202c;">
                        <i class="fas fa-chart-line"></i> Tasas de Cambio
                    </h3>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="actualizar_tasas_api">
                        <button type="submit" class="btn-primary-modern btn-modern">
                            <i class="fas fa-sync-alt"></i> Actualizar desde API
                        </button>
                    </form>
                </div>

                <div class="tasa-item-modern">
                    <div class="tasa-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="tasa-info">
                        <div class="tasa-label">1 Dólar Estadounidense (USD)</div>
                        <div class="tasa-value">₲ <?php echo number_format($tasas['USD_PYG'] ?? 0, 2); ?></div>
                    </div>
                </div>

                <div class="tasa-item-modern">
                    <div class="tasa-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="tasa-info">
                        <div class="tasa-label">1 Euro (EUR)</div>
                        <div class="tasa-value">₲ <?php echo number_format($tasas['EUR_PYG'] ?? 0, 2); ?></div>
                    </div>
                </div>

                <div style="margin-top: 15px; padding: 15px; background: #fef3c7; border-radius: 12px; font-size: 13px; color: #92400e;">
                    <i class="fas fa-info-circle"></i>
                    Las tasas se actualizan automáticamente al agregar/editar suscripciones en USD o EUR, o puedes actualizarlas manualmente.
                </div>
            </div>
        </main>
    </div>

    <!-- Botón Flotante Agregar -->
    <?php if (!empty($suscripciones)): ?>
    <button class="fab-button" onclick="mostrarModalAgregar()" title="Agregar Suscripción">
        <i class="fas fa-plus"></i>
    </button>
    <?php endif; ?>

    <!-- Modal Agregar/Editar -->
    <div id="modalForm" class="modal-modern">
        <div class="modal-content-modern">
            <div class="modal-header-modern">
                <h3 id="modalTitle">Agregar Suscripción</h3>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="accion" id="form_accion" value="agregar">
                <input type="hidden" name="id" id="form_id">
                
                <div class="modal-body-modern">
                    <div class="form-group-modern">
                        <label for="nombre">Nombre de la Suscripción *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control-modern" 
                               placeholder="Ej: Netflix, Spotify, Gym" required>
                    </div>

                    <div class="form-row-modern">
                        <div class="form-group-modern">
                            <label for="monto">Monto *</label>
                            <input type="number" id="monto" name="monto" class="form-control-modern" 
                                   placeholder="0.00" min="0" step="0.01" required>
                        </div>

                        <div class="form-group-modern">
                            <label for="moneda">Moneda *</label>
                            <select id="moneda" name="moneda" class="form-control-modern" required>
                                <option value="PYG">PYG - Guaraníes</option>
                                <option value="USD">USD - Dólares</option>
                                <option value="EUR">EUR - Euros</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label for="dia_cobro">Día de Cobro (1-31)</label>
                        <input type="number" id="dia_cobro" name="dia_cobro" class="form-control-modern" 
                               value="1" min="1" max="31" required>
                    </div>

                    <div class="form-group-modern">
                        <label>Color *</label>
                        <input type="hidden" id="color" name="color" value="#4b5563" required>
                        <div class="color-picker-grid">
                            <?php foreach ($coloresPopulares as $color): ?>
                                <div class="color-option <?php echo $color === '#4b5563' ? 'selected' : ''; ?>" 
                                     style="background: <?php echo $color; ?>"
                                     onclick="seleccionarColor('<?php echo $color; ?>')"></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label>Icono *</label>
                        <input type="hidden" id="icono" name="icono" value="fa-star" required>
                        <div class="icon-picker-grid">
                            <?php foreach ($iconosPopulares as $icono): ?>
                                <div class="icon-option <?php echo $icono === 'fa-star' ? 'selected' : ''; ?>" 
                                     onclick="seleccionarIcono('<?php echo $icono; ?>')">
                                    <i class="fas <?php echo $icono; ?>"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label for="descripcion">Descripción (Opcional)</label>
                        <input type="text" id="descripcion" name="descripcion" class="form-control-modern" 
                               placeholder="Ej: Plan Premium, Membresía Anual">
                    </div>
                </div>

                <div class="modal-footer-modern">
                    <button type="button" onclick="cerrarModal()" class="btn-secondary-modern btn-modern">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-modern">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Formulario Eliminar -->
    <form id="formEliminar" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <!-- Modal Tasas de Cambio -->
    <div id="modalTasas" class="modal-modern">
        <div class="modal-content-modern" style="max-width: 600px;">
            <div class="modal-header-modern">
                <h3><i class="fas fa-chart-line"></i> Tasas de Cambio</h3>
            </div>
            
            <div style="padding: 30px;">
                <div class="tasa-item-modern">
                    <div class="tasa-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="tasa-info">
                        <div class="tasa-label">1 Dólar Estadounidense (USD)</div>
                        <div class="tasa-value">₲ <?php echo number_format($tasas['USD_PYG'] ?? 0, 2); ?></div>
                    </div>
                </div>

                <div class="tasa-item-modern">
                    <div class="tasa-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="tasa-info">
                        <div class="tasa-label">1 Euro (EUR)</div>
                        <div class="tasa-value">₲ <?php echo number_format($tasas['EUR_PYG'] ?? 0, 2); ?></div>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 12px; font-size: 13px; color: #92400e;">
                    <i class="fas fa-info-circle"></i>
                    Las tasas se actualizan automáticamente al agregar/editar suscripciones en USD o EUR, o puedes actualizarlas manualmente con el botón de abajo.
                </div>

                <form method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="accion" value="actualizar_tasas_api">
                    <button type="submit" class="btn btn-primary btn-modern" style="width: 100%;">
                        <i class="fas fa-sync-alt"></i> Actualizar Tasas desde API
                    </button>
                </form>
            </div>

            <div class="modal-footer-modern">
                <button type="button" onclick="cerrarModalTasas()" class="btn-secondary-modern btn-modern">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>

    <script>
        function mostrarModalAgregar() {
            document.getElementById('modalTitle').textContent = 'Agregar Suscripción';
            document.getElementById('form_accion').value = 'agregar';
            document.getElementById('form_id').value = '';
            document.getElementById('nombre').value = '';
            document.getElementById('monto').value = '';
            document.getElementById('moneda').value = 'PYG';
            document.getElementById('dia_cobro').value = '1';
            document.getElementById('color').value = '#4b5563';
            document.getElementById('icono').value = 'fa-star';
            document.getElementById('descripcion').value = '';
            
            // Reset selections
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            const defaultColor = document.querySelector('.color-option[style*="#4b5563"]');
            if (defaultColor) defaultColor.classList.add('selected');
            
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            const defaultIcon = document.querySelector('.icon-option .fa-star');
            if (defaultIcon && defaultIcon.parentElement) {
                defaultIcon.parentElement.classList.add('selected');
            }
            
            document.getElementById('modalForm').classList.add('show');
        }

        function editarSuscripcion(sub) {
            document.getElementById('modalTitle').textContent = 'Editar Suscripción';
            document.getElementById('form_accion').value = 'editar';
            document.getElementById('form_id').value = sub.id;
            document.getElementById('nombre').value = sub.nombre;
            document.getElementById('monto').value = sub.monto;
            document.getElementById('moneda').value = sub.moneda;
            document.getElementById('dia_cobro').value = sub.dia_cobro;
            document.getElementById('color').value = sub.color;
            document.getElementById('icono').value = sub.icono;
            document.getElementById('descripcion').value = sub.descripcion || '';
            
            // Update selections
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.color-option[style*="${sub.color}"]`)?.classList.add('selected');
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.icon-option .${sub.icono}`)?.parentElement.classList.add('selected');
            
            document.getElementById('modalForm').classList.add('show');
        }

        function cerrarModal() {
            document.getElementById('modalForm').classList.remove('show');
        }

        function mostrarModalTasas() {
            document.getElementById('modalTasas').classList.add('show');
        }

        function cerrarModalTasas() {
            document.getElementById('modalTasas').classList.remove('show');
        }

        function seleccionarColor(color) {
            document.getElementById('color').value = color;
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
        }

        function seleccionarIcono(icono) {
            document.getElementById('icono').value = icono;
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
        }

        function eliminarSuscripcion(id, nombre) {
            if (confirm('¿Estás seguro de eliminar "' + nombre + '"?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formEliminar').submit();
            }
        }

        function verDetalle(sub) {
            // Opcional: mostrar modal con detalles
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalForm').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
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
