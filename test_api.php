<?php
/**
 * Script de prueba para verificar la conexión con la API de tasas de cambio
 * Ejecutar este archivo directamente en el navegador para diagnosticar problemas
 */

echo "<h1>🔍 Test de Conexión con API de Tasas de Cambio</h1>";
echo "<hr>";

// 1. Verificar cURL
echo "<h2>1. Verificación de cURL</h2>";
if (function_exists('curl_init')) {
    echo "✅ <strong>cURL está habilitado</strong><br>";
    
    // Mostrar versión de cURL
    $version = curl_version();
    echo "Versión de cURL: " . $version['version'] . "<br>";
    echo "Versión de SSL: " . $version['ssl_version'] . "<br>";
} else {
    echo "❌ <strong>ERROR: cURL NO está habilitado en tu servidor PHP</strong><br>";
    echo "Solución: Habilita la extensión php_curl en tu php.ini<br>";
    exit;
}

echo "<hr>";

// 2. Test de conectividad básica
echo "<h2>2. Test de Conectividad con la API</h2>";
$url = "https://open.er-api.com/v6/latest/USD";
echo "URL de la API: <a href='$url' target='_blank'>$url</a><br><br>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);
$duration = round(($endTime - $startTime) * 1000, 2);

$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

curl_close($ch);

if ($response === false) {
    echo "❌ <strong>ERROR DE CONEXIÓN</strong><br>";
    echo "Mensaje de error: <span style='color: red;'>$error</span><br>";
    echo "<br>Posibles causas:<br>";
    echo "- No tienes acceso a internet<br>";
    echo "- Tu servidor tiene firewall bloqueando conexiones HTTPS salientes<br>";
    echo "- La API está caída temporalmente<br>";
    exit;
} else {
    echo "✅ <strong>Conexión establecida exitosamente</strong><br>";
    echo "Tiempo de respuesta: {$duration}ms<br>";
    echo "Código HTTP: $httpCode<br>";
    
    if ($httpCode !== 200) {
        echo "❌ <strong>ERROR: Código HTTP no es 200</strong><br>";
        echo "La API retornó un error<br>";
        exit;
    }
}

echo "<hr>";

// 3. Validar respuesta JSON
echo "<h2>3. Validación de Respuesta JSON</h2>";
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ <strong>ERROR AL DECODIFICAR JSON</strong><br>";
    echo "Error: " . json_last_error_msg() . "<br>";
    echo "Respuesta recibida: <pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
    exit;
} else {
    echo "✅ <strong>JSON válido recibido</strong><br>";
}

if (!isset($data['rates'])) {
    echo "❌ <strong>ERROR: La respuesta no contiene el campo 'rates'</strong><br>";
    echo "Estructura recibida: <pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
    exit;
} else {
    echo "✅ <strong>Campo 'rates' encontrado</strong><br>";
    echo "Cantidad de monedas disponibles: " . count($data['rates']) . "<br>";
}

echo "<hr>";

// 4. Verificar monedas específicas
echo "<h2>4. Verificación de Monedas Necesarias</h2>";

$monedasRequeridas = ['PYG', 'EUR'];
$todasPresentes = true;

foreach ($monedasRequeridas as $moneda) {
    if (isset($data['rates'][$moneda])) {
        echo "✅ <strong>$moneda encontrado:</strong> " . $data['rates'][$moneda] . "<br>";
    } else {
        echo "❌ <strong>$moneda NO encontrado</strong><br>";
        $todasPresentes = false;
    }
}

if (!$todasPresentes) {
    echo "<br>❌ <strong>ERROR: Faltan monedas necesarias</strong><br>";
    exit;
}

echo "<hr>";

// 5. Cálculo de tasas
echo "<h2>5. Cálculo de Tasas PYG</h2>";

try {
    $usdToPyg = $data['rates']['PYG'];
    echo "✅ <strong>USD → PYG:</strong> ₲" . number_format($usdToPyg, 2) . "<br>";
    
    $eurToUsd = 1 / $data['rates']['EUR'];
    $eurToPyg = $eurToUsd * $usdToPyg;
    echo "✅ <strong>EUR → PYG:</strong> ₲" . number_format($eurToPyg, 2) . "<br>";
    
    echo "<br><div style='background: #d1fae5; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981;'>";
    echo "<strong>🎉 TODO FUNCIONA CORRECTAMENTE</strong><br><br>";
    echo "Tasas calculadas:<br>";
    echo "• USD 1.00 = ₲" . number_format($usdToPyg, 2) . "<br>";
    echo "• EUR 1.00 = ₲" . number_format($eurToPyg, 2) . "<br>";
    echo "<br>Ejemplos de conversión:<br>";
    echo "• Netflix USD 15.99 = ₲" . number_format(15.99 * $usdToPyg, 0) . "<br>";
    echo "• Spotify USD 9.99 = ₲" . number_format(9.99 * $usdToPyg, 0) . "<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ <strong>ERROR EN CÁLCULO:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 6. Información de la API
echo "<h2>6. Información de la API</h2>";
echo "Provider: " . ($data['provider'] ?? 'No especificado') . "<br>";
echo "Última actualización: " . ($data['time_last_update_utc'] ?? 'No especificado') . "<br>";
echo "Próxima actualización: " . ($data['time_next_update_utc'] ?? 'No especificado') . "<br>";

echo "<hr>";
echo "<h2>📋 Resumen Final</h2>";
echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px;'>";
echo "<strong>✅ Todos los tests pasaron exitosamente</strong><br><br>";
echo "Tu configuración es correcta y la API funciona perfectamente.<br>";
echo "Si aún tienes errores en la aplicación, verifica:<br>";
echo "1. Que las tablas de la base de datos existan (ejecuta schema.sql)<br>";
echo "2. Que las funciones estén correctamente incluidas en functions.php<br>";
echo "3. Que tengas sesión iniciada al usar la aplicación<br>";
echo "</div>";

echo "<br><br>";
echo "<a href='dashboard/suscripciones_new.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block;'>← Volver a Suscripciones</a>";
?>
