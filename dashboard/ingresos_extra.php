<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'agregar') {
        $fecha = trim($_POST['fecha'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $monto = floatval($_POST['monto'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? 'otro');
        $notas = trim($_POST['notas'] ?? '');
        
        if (registrarIngresoExtra($fecha, $descripcion, $monto, $categoria, $notas)) {
            $mensaje = '💰 Ingreso extra registrado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al registrar el ingreso extra';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $fecha = trim($_POST['fecha'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $monto = floatval($_POST['monto'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? 'otro');
        $notas = trim($_POST['notas'] ?? '');
        
        if (actualizarIngresoExtra($id, $fecha, $descripcion, $monto, $categoria, $notas)) {
            $mensaje = 'Ingreso extra actualizado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al actualizar el ingreso extra';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        
        if (eliminarIngresoExtra($id)) {
            $mensaje = 'Ingreso extra eliminado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el ingreso extra';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener todos los ingresos extra
$ingresosExtra = obtenerIngresosExtra();
$totalIngresosExtra = calcularTotalIngresosExtra();

// Obtener resumen por categoría
$ingresosPorCategoria = obtenerIngresosExtraPorCategoria();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresos Extra - MoneyFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .categoria-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .categoria-regalo { background: #fce7f3; color: #be185d; }
        .categoria-trabajo_extra { background: #dbeafe; color: #1e40af; }
        .categoria-venta { background: #d1fae5; color: #065f46; }
        .categoria-premio { background: #fef3c7; color: #92400e; }
        .categoria-reembolso { background: #e0e7ff; color: #3730a3; }
        .categoria-otro { background: #f3f4f6; color: #374151; }
        
        .ingreso-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .ingreso-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .ingreso-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .ingreso-monto {
            font-size: 1.25rem;
            font-weight: 700;
            color: #059669;
        }
        
        .ingreso-fecha {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .ingreso-descripcion {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .ingreso-notas {
            color: #6b7280;
            font-size: 0.875rem;
            font-style: italic;
        }
        
        .resumen-categorias {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .categoria-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .categoria-nombre {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            text-transform: capitalize;
        }
        
        .categoria-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: #059669;
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
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Gastos Fijos</span>
                </a>
                <a href="suscripciones.php" class="menu-item">
                    <i class="fas fa-sync-alt"></i>
                    <span>Suscripciones</span>
                </a>
                <a href="ingresos_extra.php" class="menu-item active">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Ingresos Extra</span>
                </a>
                <a href="configuracion.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </div>

            <div class="sidebar-footer">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-hand-holding-usd"></i> Ingresos Extra</h1>
                    <p class="subtitle">Registra ingresos adicionales que no forman parte de tu salario mensual</p>
                    <p class="info-text">
                        <i class="fas fa-info-circle"></i> 
                        Estos ingresos no afectan tu presupuesto mensual, solo se registran para tu control personal
                    </p>
                </div>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <!-- Resumen Total -->
            <div class="kpi-grid" style="margin-bottom: 2rem;">
                <div class="kpi-card" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                    <div class="kpi-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="kpi-content">
                        <div class="kpi-label">Total Ingresos Extra</div>
                        <div class="kpi-value"><?php echo formatearMoneda($totalIngresosExtra); ?></div>
                        <div class="kpi-sublabel">Acumulado total</div>
                    </div>
                </div>
            </div>

            <!-- Resumen por categoría -->
            <?php if (!empty($ingresosPorCategoria)): ?>
                <div class="section-title" style="margin-bottom: 1rem;">
                    <h3><i class="fas fa-chart-pie"></i> Resumen por Categoría</h3>
                </div>
                <div class="resumen-categorias">
                    <?php foreach ($ingresosPorCategoria as $cat): ?>
                        <div class="categoria-card">
                            <div class="categoria-nombre">
                                <?php 
                                $categorias = [
                                    'regalo' => 'Regalos',
                                    'trabajo_extra' => 'Trabajo Extra',
                                    'venta' => 'Ventas',
                                    'premio' => 'Premios',
                                    'reembolso' => 'Reembolsos',
                                    'otro' => 'Otros'
                                ];
                                echo $categorias[$cat['categoria']] ?? $cat['categoria'];
                                ?>
                                <small>(<?php echo $cat['cantidad']; ?>)</small>
                            </div>
                            <div class="categoria-total"><?php echo formatearMoneda($cat['total']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario para agregar ingreso -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3><i class="fas fa-plus"></i> Registrar Nuevo Ingreso Extra</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="accion" value="agregar">
                        
                        <div class="form-group">
                            <label for="fecha">Fecha *</label>
                            <input type="date" id="fecha" name="fecha" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="categoria">Categoría *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="regalo">🎁 Regalo</option>
                                <option value="trabajo_extra">💼 Trabajo Extra</option>
                                <option value="venta">🛒 Venta</option>
                                <option value="premio">🏆 Premio</option>
                                <option value="reembolso">💳 Reembolso</option>
                                <option value="otro">📌 Otro</option>
                            </select>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label for="descripcion">Descripción *</label>
                            <input type="text" id="descripcion" name="descripcion" required 
                                   placeholder="Ej: Venta de laptop, Regalo de cumpleaños, Freelance">
                        </div>

                        <div class="form-group">
                            <label for="monto">Monto (Gs) *</label>
                            <input type="number" id="monto" name="monto" required min="0" step="1000" 
                                   placeholder="0">
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label for="notas">Notas adicionales</label>
                            <textarea id="notas" name="notas" rows="2" 
                                      placeholder="Detalles adicionales (opcional)"></textarea>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Registrar Ingreso
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de ingresos -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Historial de Ingresos Extra</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($ingresosExtra)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No hay ingresos extra registrados</p>
                            <small>Los ingresos extra que registres aparecerán aquí</small>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ingresosExtra as $ingreso): ?>
                            <div class="ingreso-card">
                                <div class="ingreso-header">
                                    <div>
                                        <span class="categoria-badge categoria-<?php echo $ingreso['categoria']; ?>">
                                            <?php 
                                            $categorias = [
                                                'regalo' => '🎁 Regalo',
                                                'trabajo_extra' => '💼 Trabajo Extra',
                                                'venta' => '🛒 Venta',
                                                'premio' => '🏆 Premio',
                                                'reembolso' => '💳 Reembolso',
                                                'otro' => '📌 Otro'
                                            ];
                                            echo $categorias[$ingreso['categoria']] ?? $ingreso['categoria'];
                                            ?>
                                        </span>
                                        <span class="ingreso-fecha">
                                            <?php echo date('d/m/Y', strtotime($ingreso['fecha'])); ?>
                                        </span>
                                    </div>
                                    <div class="ingreso-monto">
                                        +<?php echo formatearMoneda($ingreso['monto']); ?>
                                    </div>
                                </div>
                                <div class="ingreso-descripcion">
                                    <?php echo htmlspecialchars($ingreso['descripcion']); ?>
                                </div>
                                <?php if (!empty($ingreso['notas'])): ?>
                                    <div class="ingreso-notas">
                                        <i class="fas fa-sticky-note"></i> 
                                        <?php echo htmlspecialchars($ingreso['notas']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="action-buttons" style="margin-top: 0.5rem;">
                                    <button class="btn-action btn-edit" 
                                            onclick="editarIngreso(<?php echo htmlspecialchars(json_encode($ingreso)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('¿Eliminar este ingreso extra?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo $ingreso['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para editar -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar Ingreso Extra</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <form method="POST" id="formEditar">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_fecha">Fecha *</label>
                    <input type="date" id="edit_fecha" name="fecha" required>
                </div>

                <div class="form-group">
                    <label for="edit_categoria">Categoría *</label>
                    <select id="edit_categoria" name="categoria" required>
                        <option value="regalo">🎁 Regalo</option>
                        <option value="trabajo_extra">💼 Trabajo Extra</option>
                        <option value="venta">🛒 Venta</option>
                        <option value="premio">🏆 Premio</option>
                        <option value="reembolso">💳 Reembolso</option>
                        <option value="otro">📌 Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_descripcion">Descripción *</label>
                    <input type="text" id="edit_descripcion" name="descripcion" required>
                </div>

                <div class="form-group">
                    <label for="edit_monto">Monto (Gs) *</label>
                    <input type="number" id="edit_monto" name="monto" required min="0" step="1000">
                </div>

                <div class="form-group">
                    <label for="edit_notas">Notas adicionales</label>
                    <textarea id="edit_notas" name="notas" rows="2"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        function editarIngreso(ingreso) {
            document.getElementById('edit_id').value = ingreso.id;
            document.getElementById('edit_fecha').value = ingreso.fecha;
            document.getElementById('edit_categoria').value = ingreso.categoria;
            document.getElementById('edit_descripcion').value = ingreso.descripcion;
            document.getElementById('edit_monto').value = ingreso.monto;
            document.getElementById('edit_notas').value = ingreso.notas || '';
            
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>
