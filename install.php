<?php
/**
 * MoneyFlow - Script de Instalación
 * Verifica requisitos y guía la instalación
 */

// Configuración
$requisitos = [
    'php_version' => '7.4.0',
    'extensiones' => ['pdo', 'pdo_mysql', 'json', 'mbstring'],
    'permisos' => ['config', 'api', 'dashboard', 'forms']
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - MoneyFlow</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            max-width: 800px;
            width: 100%;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { color: #667eea; margin-bottom: 1.5rem; }
        h2 { color: #333; margin: 2rem 0 1rem; font-size: 1.2rem; }
        .check-item {
            padding: 1rem;
            margin: 0.5rem 0;
            background: #f3f4f6;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 3px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status.ok { background: #d1fae5; color: #065f46; }
        .status.error { background: #fee2e2; color: #991b1b; }
        .btn {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 2rem;
            width: 100%;
        }
        .btn:hover { background: #5568d3; }
        .btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .info-box {
            background: #dbeafe;
            color: #1e40af;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        .code {
            background: #1f2937;
            color: #10b981;
            padding: 1rem;
            border-radius: 5px;
            font-family: monospace;
            margin: 1rem 0;
            overflow-x: auto;
        }
        .steps {
            counter-reset: step;
        }
        .step {
            counter-increment: step;
            padding-left: 2rem;
            position: relative;
            margin: 1.5rem 0;
        }
        .step::before {
            content: counter(step);
            position: absolute;
            left: 0;
            top: 0;
            background: #667eea;
            color: white;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💰 MoneyFlow - Instalación</h1>
        
        <h2>📋 Verificación de Requisitos</h2>
        
        <?php
        $errores = 0;
        
        // Verificar versión de PHP
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, $requisitos['php_version'], '>=');
        ?>
        
        <div class="check-item">
            <span>PHP <?php echo $requisitos['php_version']; ?>+ (Actual: <?php echo $phpVersion; ?>)</span>
            <span class="status <?php echo $phpOk ? 'ok' : 'error'; ?>">
                <?php echo $phpOk ? '✓ OK' : '✗ Error'; ?>
            </span>
        </div>
        
        <?php
        if (!$phpOk) $errores++;
        
        // Verificar extensiones
        foreach ($requisitos['extensiones'] as $ext) {
            $extOk = extension_loaded($ext);
            ?>
            <div class="check-item">
                <span>Extensión: <?php echo $ext; ?></span>
                <span class="status <?php echo $extOk ? 'ok' : 'error'; ?>">
                    <?php echo $extOk ? '✓ OK' : '✗ No instalada'; ?>
                </span>
            </div>
            <?php
            if (!$extOk) $errores++;
        }
        
        // Verificar permisos de escritura
        foreach ($requisitos['permisos'] as $dir) {
            $dirPath = __DIR__ . '/' . $dir;
            $writable = is_writable($dirPath);
            ?>
            <div class="check-item">
                <span>Permisos de escritura: /<?php echo $dir; ?></span>
                <span class="status <?php echo $writable ? 'ok' : 'error'; ?>">
                    <?php echo $writable ? '✓ OK' : '✗ Sin permisos'; ?>
                </span>
            </div>
            <?php
            if (!$writable) $errores++;
        }
        ?>
        
        <h2>🔧 Pasos de Instalación</h2>
        
        <div class="steps">
            <div class="step">
                <strong>Crear la base de datos</strong>
                <div class="code">mysql -u root -p -e "CREATE DATABASE moneyflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"</div>
            </div>
            
            <div class="step">
                <strong>Importar el schema SQL</strong>
                <div class="code">mysql -u root -p moneyflow &lt; sql/schema.sql</div>
            </div>
            
            <div class="step">
                <strong>Configurar la conexión a la base de datos</strong>
                <p>Editar el archivo: <code>config/database.php</code></p>
                <div class="info-box">
                    Cambiar los valores de:<br>
                    - <strong>$host</strong>: Host de MySQL (usualmente 'localhost')<br>
                    - <strong>$db_name</strong>: 'moneyflow'<br>
                    - <strong>$username</strong>: Tu usuario de MySQL<br>
                    - <strong>$password</strong>: Tu contraseña de MySQL
                </div>
            </div>
            
            <div class="step">
                <strong>Configurar valores iniciales</strong>
                <p>Editar en la base de datos o modificar antes de importar:</p>
                <div class="code">UPDATE configuracion SET 
    saldo_inicial = 4287264.00,
    gourmet_inicial = 561290.00,
    objetivo_ahorro = 1200000.00,
    fecha_inicio = '2026-04-01',
    fecha_fin = '2026-04-25'
WHERE id = 1;</div>
            </div>
            
            <div class="step">
                <strong>Eliminar este archivo de instalación</strong>
                <div class="info-box">
                    Por seguridad, elimina <code>install.php</code> después de completar la instalación.
                </div>
            </div>
        </div>
        
        <?php if ($errores > 0): ?>
            <div class="info-box" style="background: #fee2e2; color: #991b1b;">
                <strong>⚠️ Hay <?php echo $errores; ?> error(es) que debes solucionar antes de continuar.</strong>
            </div>
            <button class="btn" disabled>No se puede continuar</button>
        <?php else: ?>
            <div class="info-box">
                <strong>✅ Todos los requisitos están satisfechos!</strong><br>
                Sigue los pasos anteriores para completar la instalación.
            </div>
            <a href="dashboard/index.php" class="btn" style="text-decoration: none; text-align: center; display: block;">
                Continuar al Dashboard
            </a>
        <?php endif; ?>
        
        <div style="margin-top: 2rem; text-align: center; color: #6b7280; font-size: 0.875rem;">
            <p>¿Necesitas ayuda? Revisa el README.md o la documentación completa.</p>
        </div>
    </div>
</body>
</html>
