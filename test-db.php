<?php
echo "<h1>Test de Conexión a Base de Datos</h1>";

// Cargar configuración
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Conexión exitosa a la base de datos</p>";
        
        // Probar consulta
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Usuarios en la base de datos: " . $result['total'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ No se pudo conectar</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
