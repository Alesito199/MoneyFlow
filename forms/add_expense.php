<?php
/**
 * MoneyFlow - Formulario de Registro de Gastos
 */

require_once __DIR__ . '/../includes/functions.php';

$mensaje = '';
$tipoMensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'fecha' => sanitizar($_POST['fecha']),
        'tipo' => sanitizar($_POST['tipo']),
        'categoria' => sanitizar($_POST['categoria']),
        'descripcion' => sanitizar($_POST['descripcion']),
        'monto' => floatval($_POST['monto']),
        'metodo' => sanitizar($_POST['metodo'])
    ];
    
    // Validaciones del lado del servidor
    if (empty($datos['fecha']) || empty($datos['tipo']) || empty($datos['categoria']) || 
        empty($datos['descripcion']) || $datos['monto'] <= 0 || empty($datos['metodo'])) {
        $mensaje = 'Todos los campos son obligatorios y el monto debe ser mayor a 0';
        $tipoMensaje = 'error';
    } else {
        $resultado = registrarGasto($datos);
        
        if ($resultado) {
            $mensaje = 'Gasto registrado exitosamente';
            $tipoMensaje = 'success';
            // Limpiar formulario
            $_POST = [];
        } else {
            $mensaje = 'Error al registrar el gasto. Intenta nuevamente.';
            $tipoMensaje = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Gasto - MoneyFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <h1>💰 MoneyFlow</h1>
            <div class="nav-links">
                <a href="../dashboard/">Dashboard</a>
                <a href="add_expense.php" class="active">Nuevo Gasto</a>
            </div>
        </nav>

        <div class="content">
            <div class="card">
                <h2>📝 Registrar Nuevo Gasto</h2>
                
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipoMensaje; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="formGasto" class="form-gasto">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha">Fecha *</label>
                            <input type="date" 
                                   id="fecha" 
                                   name="fecha" 
                                   value="<?php echo date('Y-m-d'); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="monto">Monto (Gs) *</label>
                            <input type="number" 
                                   id="monto" 
                                   name="monto" 
                                   placeholder="Ej: 50000" 
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo">Tipo de Gasto *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Selecciona...</option>
                                <?php foreach (TIPOS_GASTO as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="categoria">Categoría *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Selecciona...</option>
                                <?php foreach (CATEGORIAS as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="metodo">Método de Pago *</label>
                            <div class="radio-group">
                                <?php foreach (METODOS_PAGO as $key => $value): ?>
                                    <label class="radio-label">
                                        <input type="radio" 
                                               name="metodo" 
                                               value="<?php echo $key; ?>" 
                                               required>
                                        <span><?php echo $value; ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción *</label>
                        <textarea id="descripcion" 
                                  name="descripcion" 
                                  rows="3" 
                                  placeholder="Ej: Compra en el supermercado"
                                  required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">Limpiar</button>
                        <button type="submit" class="btn btn-primary">💾 Guardar Gasto</button>
                    </div>
                </form>
            </div>

            <div class="card tips">
                <h3>💡 Consejos</h3>
                <ul>
                    <li><strong>Gastos Fijos:</strong> Aquellos que se repiten mensualmente (electricidad, internet, etc.)</li>
                    <li><strong>Gastos Variables:</strong> Compras ocasionales o que varían mes a mes</li>
                    <li><strong>Gourmet:</strong> Solo para compras en supermercado con la tarjeta</li>
                    <li><strong>Efectivo:</strong> Para todos los demás gastos</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Validación adicional del formulario
        document.getElementById('formGasto').addEventListener('submit', function(e) {
            const monto = parseFloat(document.getElementById('monto').value);
            
            if (monto <= 0 || isNaN(monto)) {
                e.preventDefault();
                alert('El monto debe ser mayor a 0');
                return false;
            }
        });

        // Mensaje de éxito auto-ocultar
        <?php if ($tipoMensaje === 'success'): ?>
        setTimeout(function() {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
