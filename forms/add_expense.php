<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = registrarGasto(
        $_POST['tipo'],
        $_POST['categoria'],
        $_POST['metodo'],
        floatval($_POST['monto']),
        $_POST['descripcion'],
        $_POST['fecha']
    );
    
    if ($resultado) {
        $mensaje = 'Gasto registrado exitosamente';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'Error al registrar el gasto';
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Gasto - MoneyFlow</title>
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
                <a href="../dashboard/index.php" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="add_expense.php" class="menu-item active">
                    <i class="fas fa-plus-circle"></i>
                    <span>Agregar Gasto</span>
                </a>
                <a href="../dashboard/expenses.php" class="menu-item">
                    <i class="fas fa-list"></i>
                    <span>Gastos Variables</span>
                </a>
                <a href="../dashboard/gastos_fijos.php" class="menu-item">
                    <i class="fas fa-receipt"></i>
                    <span>Gastos Fijos</span>
                </a>
                <a href="../dashboard/suscripciones_new.php" class="menu-item">
                    <i class="fas fa-sync-alt"></i>
                    <span>Suscripciones</span>
                </a>
                <a href="../dashboard/configuracion.php" class="menu-item">
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
                <h1>Registrar Nuevo Gasto</h1>
                <p>Ingresa los detalles del gasto</p>
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
                        <i class="fas fa-receipt"></i> Detalles del Gasto
                    </h3>
                </div>

                <form method="POST" action="" class="form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo">
                                <i class="fas fa-tag"></i> Tipo de Gasto *
                            </label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach (TIPOS_GASTO as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="categoria">
                                <i class="fas fa-folder"></i> Categoría *
                            </label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach (CATEGORIAS as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="metodo">
                                <i class="fas fa-credit-card"></i> Método de Pago *
                            </label>
                            <select id="metodo" name="metodo" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach (METODOS_PAGO as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="monto">
                                <i class="fas fa-money-bill"></i> Monto (Gs) *
                            </label>
                            <input 
                                type="number" 
                                id="monto" 
                                name="monto" 
                                step="1" 
                                min="0" 
                                required 
                                placeholder="Ej: 50000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fecha">
                            <i class="fas fa-calendar"></i> Fecha *
                        </label>
                        <input 
                            type="date" 
                            id="fecha" 
                            name="fecha" 
                            value="<?php echo date('Y-m-d'); ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">
                            <i class="fas fa-file-alt"></i> Descripción *
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            rows="3" 
                            required 
                            placeholder="Ej: Almuerzo en restaurante"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Gasto
                        </button>
                        <a href="../dashboard/index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Quick Tips -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb"></i> Consejos Rápidos
                    </h3>
                </div>
                <ul style="padding: 20px 40px; margin: 0;">
                    <li><strong>Necesario:</strong> Gastos esenciales como comida principal, transporte</li>
                    <li><strong>Opcional:</strong> Gastos no urgentes como entretenimiento, salidas</li>
                    <li><strong>Emergencia:</strong> Gastos imprevistos que requieren atención inmediata</li>
                </ul>
            </div>
        </main>
    </div>

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
            document.body.style.overflow = 'hidden';
        }

        // Funci\u00f3n para cerrar men\u00fa
        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
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

        // Cerrar men\u00fa al cambiar tama\u00f1o de ventana
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
    </script>
</body>
</html>
