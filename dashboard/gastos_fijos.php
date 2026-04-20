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
        
        if (agregarGastoFijo($nombre, $monto)) {
            $mensaje = 'Gasto fijo agregado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al agregar el gasto fijo';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $monto = floatval($_POST['monto'] ?? 0);
        
        if (actualizarGastoFijo($id, $nombre, $monto)) {
            $mensaje = 'Gasto fijo actualizado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al actualizar el gasto fijo';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        
        if (eliminarGastoFijo($id)) {
            $mensaje = 'Gasto fijo eliminado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el gasto fijo';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener todos los gastos fijos
$gastosFijos = obtenerGastosFijos();
$totalGastosFijos = calcularTotalGastosFijos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos Fijos - MoneyFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <a href="gastos_fijos.php" class="menu-item active">
                    <i class="fas fa-receipt"></i>
                    <span>Gastos Fijos</span>
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
                <h1>Gastos Fijos</h1>
                <p>Administra tus gastos mensuales fijos (arriendo, servicios, suscripciones, etc.)</p>
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
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="kpi-content">
                        <h3>Total Gastos Fijos</h3>
                        <div class="kpi-value"><?php echo formatearMoneda($totalGastosFijos); ?></div>
                        <div class="kpi-label"><?php echo count($gastosFijos); ?> gastos activos</div>
                    </div>
                </div>
            </div>

            <!-- Formulario para agregar gasto fijo -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i> Agregar Nuevo Gasto Fijo
                    </h3>
                </div>

                <form method="POST" action="" class="form">
                    <input type="hidden" name="accion" value="agregar">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Gasto</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" 
                                   placeholder="Ej: Arriendo, Internet, Netflix" required>
                        </div>

                        <div class="form-group">
                            <label for="monto">Monto Mensual</label>
                            <input type="number" id="monto" name="monto" class="form-control" 
                                   placeholder="0" min="0" step="0.01" required>
                        </div>

                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Lista de gastos fijos -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Mis Gastos Fijos
                    </h3>
                </div>

                <?php if (empty($gastosFijos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No hay gastos fijos registrados</h3>
                        <p>Agrega tus gastos mensuales fijos usando el formulario de arriba</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Monto Mensual</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastosFijos as $gasto): ?>
                                    <tr id="row-<?php echo $gasto['id']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($gasto['nombre']); ?></strong>
                                        </td>
                                        <td>
                                            <strong><?php echo formatearMoneda($gasto['monto']); ?></strong>
                                        </td>
                                        <td>
                                            <button onclick="editarGasto(<?php echo $gasto['id']; ?>, '<?php echo htmlspecialchars($gasto['nombre'], ENT_QUOTES); ?>', <?php echo $gasto['monto']; ?>)" 
                                                    class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button onclick="eliminarGasto(<?php echo $gasto['id']; ?>, '<?php echo htmlspecialchars($gasto['nombre'], ENT_QUOTES); ?>')" 
                                                    class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: var(--light); font-weight: bold;">
                                    <td>TOTAL</td>
                                    <td><strong><?php echo formatearMoneda($totalGastosFijos); ?></strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal para editar gasto -->
    <div id="modalEditar" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Gasto Fijo</h3>
                <button onclick="cerrarModal()" class="modal-close">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nombre">Nombre del Gasto</label>
                        <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_monto">Monto Mensual</label>
                        <input type="number" id="edit_monto" name="monto" class="form-control" 
                               min="0" step="0.01" required>
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

    <!-- Formulario oculto para eliminar -->
    <form id="formEliminar" method="POST" action="" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <script>
        function editarGasto(id, nombre, monto) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_monto').value = monto;
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        function eliminarGasto(id, nombre) {
            if (confirm('¿Estás seguro de eliminar el gasto fijo "' + nombre + '"?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formEliminar').submit();
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalEditar');
            if (event.target === modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>
