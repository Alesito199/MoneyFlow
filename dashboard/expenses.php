<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        if (eliminarGasto($id)) {
            $mensaje = 'Gasto eliminado exitosamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el gasto';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener configuración
$config = obtenerConfiguracion();
$gastos = obtenerGastos($config['fecha_inicio'], $config['fecha_fin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Gastos - MoneyFlow</title>
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
                <a href="expenses.php" class="menu-item active">
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
                        <div class="user-role">Administrador</div>
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
                <h1>Gastos Variables</h1>
                <p>Todos tus gastos variables del periodo</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-ul"></i> Todos los Gastos
                    </h3>
                    <a href="../forms/add_expense.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Gasto
                    </a>
                </div>

                <?php if (empty($gastos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No hay gastos registrados</h3>
                        <p>Comienza registrando tu primer gasto</p>
                        <a href="../forms/add_expense.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Registrar Gasto
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Resumen -->
                    <div class="stats-row" style="padding: 20px; border-bottom: 1px solid var(--border);">
                        <div class="stat-item">
                            <div class="stat-label">Total Gastos</div>
                            <div class="stat-value"><?php echo count($gastos); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Efectivo</div>
                            <div class="stat-value">
                                <?php 
                                $totalEfectivo = array_sum(array_column(
                                    array_filter($gastos, fn($g) => $g['metodo'] === 'efectivo'), 
                                    'monto'
                                ));
                                echo formatearMoneda($totalEfectivo);
                                ?>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Gourmet</div>
                            <div class="stat-value">
                                <?php 
                                $totalGourmet = array_sum(array_column(
                                    array_filter($gastos, fn($g) => $g['metodo'] === 'gourmet'), 
                                    'monto'
                                ));
                                echo formatearMoneda($totalGourmet);
                                ?>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Total General</div>
                            <div class="stat-value">
                                <?php echo formatearMoneda($totalEfectivo + $totalGourmet); ?>
                            </div>
                        </div>
                    </div>

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
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastos as $index => $gasto): ?>
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
                                        <td class="text-right"><strong><?php echo formatearMoneda($gasto['monto']); ?></strong></td>
                                        <td>
                                            <button onclick="eliminarGasto(<?php echo $gasto['id']; ?>, '<?php echo htmlspecialchars($gasto['descripcion'], ENT_QUOTES); ?>')" 
                                                    class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Formulario oculto para eliminar -->
    <form id="formEliminar" method="POST" action="" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <script>
        function eliminarGasto(id, descripcion) {
            if (confirm('¿Estás seguro de eliminar el gasto "' + descripcion + '"?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formEliminar').submit();
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
