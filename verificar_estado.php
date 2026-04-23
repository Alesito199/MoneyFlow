<?php
/**
 * Script de verificación del estado financiero
 * Ejecutar para diagnosticar por qué los KPIs están en 0
 */
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo "<h1>❌ No hay sesión activa</h1>";
    echo "<p>Por favor <a href='login.php'>inicia sesión</a> primero.</p>";
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

echo "<h1>🔍 Diagnóstico Financiero - MoneyFlow</h1>";
echo "<p>Usuario actual: <strong>$username</strong> (ID: $userId)</p>";
echo "<hr>";

// 1. Verificar configuración
echo "<h2>1. Configuración del Usuario</h2>";
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE user_id = ?");
    $stmt->execute([$userId]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "✅ <strong>Configuración encontrada</strong><br><br>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>Ingreso Mensual</td><td>₲" . number_format($config['ingreso_mensual'], 0) . "</td></tr>";
        echo "<tr><td>Monto Ahorro</td><td>₲" . number_format($config['monto_ahorro'], 0) . "</td></tr>";
        echo "<tr><td>Monto Gourmet</td><td>₲" . number_format($config['monto_gourmet'], 0) . "</td></tr>";
        echo "<tr><td>Saldo Inicial</td><td>₲" . number_format($config['saldo_inicial'], 0) . "</td></tr>";
        echo "<tr><td>Gourmet Inicial</td><td>₲" . number_format($config['gourmet_inicial'], 0) . "</td></tr>";
        echo "<tr><td>Objetivo Ahorro</td><td>₲" . number_format($config['objetivo_ahorro'], 0) . "</td></tr>";
        echo "<tr><td>Fecha Inicio</td><td>{$config['fecha_inicio']}</td></tr>";
        echo "<tr><td>Fecha Fin</td><td>{$config['fecha_fin']}</td></tr>";
        echo "</table>";
    } else {
        echo "❌ <strong>NO hay configuración para este usuario</strong><br>";
        echo "<p style='background: #fee2e2; padding: 15px; border-left: 4px solid #dc2626;'>";
        echo "⚠️ <strong>PROBLEMA DETECTADO:</strong> No tienes configuración financiera.<br>";
        echo "Ve a <a href='dashboard/configuracion.php'>Configuración</a> para establecer tu presupuesto.";
        echo "</p>";
    }
} catch (PDOException $e) {
    echo "❌ <strong>Error de BD:</strong> " . $e->getMessage();
}

echo "<hr>";

// 2. Verificar gastos fijos
echo "<h2>2. Gastos Fijos</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM gastos_fijos WHERE user_id = ? AND activo = 1");
    $stmt->execute([$userId]);
    $gastosFijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($gastosFijos)) {
        echo "✅ <strong>Gastos fijos encontrados: " . count($gastosFijos) . "</strong><br><br>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Nombre</th><th>Monto</th></tr>";
        $totalFijos = 0;
        foreach ($gastosFijos as $gf) {
            echo "<tr><td>{$gf['nombre']}</td><td>₲" . number_format($gf['monto'], 0) . "</td></tr>";
            $totalFijos += $gf['monto'];
        }
        echo "<tr style='font-weight: bold; background: #f3f4f6;'><td>TOTAL</td><td>₲" . number_format($totalFijos, 0) . "</td></tr>";
        echo "</table>";
    } else {
        echo "⚠️ <strong>No tienes gastos fijos registrados</strong>";
    }
} catch (PDOException $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
}

echo "<hr>";

// 3. Verificar suscripciones
echo "<h2>3. Suscripciones Activas</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM suscripciones WHERE user_id = ? AND activo = 1");
    $stmt->execute([$userId]);
    $suscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($suscripciones)) {
        echo "✅ <strong>Suscripciones encontradas: " . count($suscripciones) . "</strong><br><br>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Nombre</th><th>Moneda</th><th>Monto Original</th><th>Monto en PYG</th><th>Día Cobro</th></tr>";
        $totalSubs = 0;
        foreach ($suscripciones as $sub) {
            echo "<tr>";
            echo "<td><i class='fas {$sub['icono']}'></i> {$sub['nombre']}</td>";
            echo "<td>{$sub['moneda']}</td>";
            echo "<td>{$sub['moneda']} " . number_format($sub['monto'], 2) . "</td>";
            echo "<td>₲" . number_format($sub['monto_pyg'], 0) . "</td>";
            echo "<td>Día {$sub['dia_cobro']}</td>";
            echo "</tr>";
            $totalSubs += $sub['monto_pyg'];
        }
        echo "<tr style='font-weight: bold; background: #f3f4f6;'><td colspan='3'>TOTAL</td><td>₲" . number_format($totalSubs, 0) . "</td><td></td></tr>";
        echo "</table>";
    } else {
        echo "⚠️ <strong>No tienes suscripciones registradas</strong>";
    }
} catch (PDOException $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
}

echo "<hr>";

// 4. Calcular total de gastos fijos (gastos_fijos + suscripciones)
echo "<h2>4. Total de Gastos Fijos (Gastos Fijos + Suscripciones)</h2>";
$totalGastosFijos = calcularTotalGastosFijos($userId);
echo "<div style='background: #dbeafe; padding: 20px; border-radius: 8px; border-left: 4px solid #2563eb;'>";
echo "<h3 style='margin-top: 0;'>₲" . number_format($totalGastosFijos, 0) . "</h3>";
echo "<p>Este es el valor que aparecerá en el dashboard bajo 'Gastos Fijos'</p>";
echo "</div>";

echo "<hr>";

// 5. Verificar gastos del periodo
echo "<h2>5. Gastos del Periodo Actual</h2>";
if ($config) {
    $gastosEfectivo = calcularGastosEfectivo($config['fecha_inicio'], $config['fecha_fin'], $userId);
    $gastosGourmet = calcularGastosGourmet($config['fecha_inicio'], $config['fecha_fin'], $userId);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Tipo</th><th>Monto</th></tr>";
    echo "<tr><td>Gastos en Efectivo (Variables)</td><td>₲" . number_format($gastosEfectivo, 0) . "</td></tr>";
    echo "<tr><td>Gastos Gourmet</td><td>₲" . number_format($gastosGourmet, 0) . "</td></tr>";
    echo "<tr style='font-weight: bold; background: #f3f4f6;'><td>TOTAL GASTADO</td><td>₲" . number_format($gastosEfectivo + $gastosGourmet, 0) . "</td></tr>";
    echo "</table>";
    
    echo "<p>Periodo: del {$config['fecha_inicio']} al {$config['fecha_fin']}</p>";
} else {
    echo "❌ No hay configuración de periodo";
}

echo "<hr>";

// 6. Estado financiero completo
echo "<h2>6. Estado Financiero Completo (KPIs del Dashboard)</h2>";
$estado = calcularEstadoFinanciero($userId);

if ($estado) {
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<h3 style='margin-top: 0;'>✅ Cálculo exitoso</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th style='text-align: left;'>KPI</th><th style='text-align: right;'>Valor</th></tr>";
    echo "<tr><td><strong>Ingreso Mensual</strong></td><td style='text-align: right;'>₲" . number_format($estado['ingreso_mensual'], 0) . "</td></tr>";
    echo "<tr><td><strong>Ahorro</strong></td><td style='text-align: right;'>₲" . number_format($estado['monto_ahorro'], 0) . "</td></tr>";
    echo "<tr style='background: #fef3c7;'><td><strong>Gastos Fijos (incluye suscripciones)</strong></td><td style='text-align: right;'>₲" . number_format($estado['gastos_fijos'], 0) . "</td></tr>";
    echo "<tr><td><strong>Gastos Variables</strong></td><td style='text-align: right;'>₲" . number_format($estado['gastos_variables'], 0) . "</td></tr>";
    echo "<tr><td><strong>Disponible (Ingreso - Ahorro)</strong></td><td style='text-align: right;'>₲" . number_format($estado['disponible'], 0) . "</td></tr>";
    echo "<tr style='background: #d1fae5;'><td><strong>Disponible Real (Disponible - Fijos - Variables)</strong></td><td style='text-align: right;'>₲" . number_format($estado['disponible_real'], 0) . "</td></tr>";
    echo "</table>";
    echo "</div>";
} else {
    echo "<div style='background: #fee2e2; padding: 20px; border-radius: 8px; border-left: 4px solid #dc2626;'>";
    echo "<h3 style='margin-top: 0;'>❌ No se pudo calcular el estado financiero</h3>";
    echo "<p>Esto explica por qué ves '0 Gs' en todos los KPIs.</p>";
    echo "</div>";
}

echo "<hr>";

// 7. Solución
echo "<h2>7. 💡 Solución</h2>";
echo "<div style='background: #dbeafe; padding: 20px; border-radius: 8px;'>";

if (!$config) {
    echo "<h3>⚠️ ACCIÓN REQUERIDA</h3>";
    echo "<ol>";
    echo "<li>Ve a <a href='dashboard/configuracion.php' style='font-weight: bold;'>Configuración</a></li>";
    echo "<li>Llena el formulario con tus datos financieros:</li>";
    echo "<ul>";
    echo "<li>Ingreso mensual</li>";
    echo "<li>Meta de ahorro</li>";
    echo "<li>Saldo inicial</li>";
    echo "<li>Fecha de inicio y fin del periodo</li>";
    echo "</ul>";
    echo "<li>Guarda la configuración</li>";
    echo "<li>Vuelve al dashboard - los KPIs se actualizarán</li>";
    echo "</ol>";
} else {
    echo "<h3>✅ Tu configuración está correcta</h3>";
    echo "<p>Los gastos fijos ahora incluyen las suscripciones.</p>";
    echo "<p><strong>Total de Gastos Fijos = Gastos Fijos Tradicionales + Suscripciones</strong></p>";
    echo "<p>₲" . number_format($totalGastosFijos, 0) . " = Gastos Fijos + Suscripciones</p>";
}

echo "</div>";

echo "<br><br>";
echo "<a href='dashboard/index.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block;'>← Volver al Dashboard</a>";
echo " ";
echo "<a href='dashboard/suscripciones_new.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block;'>Ver Suscripciones</a>";
?>

<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
        background: #f9fafb;
    }
    h1 { color: #1f2937; }
    h2 { 
        color: #374151; 
        background: #e5e7eb; 
        padding: 10px 15px; 
        border-radius: 8px;
        margin-top: 30px;
    }
    table {
        width: 100%;
        background: white;
        margin: 15px 0;
    }
    th {
        background: #f3f4f6;
        text-align: left;
        padding: 12px;
    }
    td {
        padding: 10px;
    }
</style>
