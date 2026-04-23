#!/bin/bash

# ==================================================
# MoneyFlow - Script de Diagnóstico y Configuración
# Para servidor Bitnami (AWS Lightsail)
# ==================================================

echo "=========================================="
echo "  MoneyFlow - Configuración Bitnami"
echo "=========================================="
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# ==================================================
# PASO 1: DIAGNÓSTICO - Ver logs de error
# ==================================================
echo ""
echo "PASO 1: Revisando logs de error de Apache..."
echo "=========================================="
sudo tail -50 /opt/bitnami/apache2/logs/error_log
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 2: Verificar estructura del proyecto
# ==================================================
echo ""
echo "PASO 2: Verificando archivos del proyecto..."
echo "=========================================="
ls -la /home/bitnami/htdocs/moneyflow/
echo ""
echo "Archivos en config/:"
ls -la /home/bitnami/htdocs/moneyflow/config/
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 3: Verificar si existe database.php
# ==================================================
echo ""
echo "PASO 3: Verificando database.php..."
echo "=========================================="
if [ -f "/home/bitnami/htdocs/moneyflow/config/database.php" ]; then
    echo -e "${GREEN}✓ database.php existe${NC}"
    echo ""
    echo "Contenido actual:"
    cat /home/bitnami/htdocs/moneyflow/config/database.php
else
    echo -e "${RED}✗ database.php NO existe${NC}"
    echo ""
    echo "Creando desde ejemplo..."
    if [ -f "/home/bitnami/htdocs/moneyflow/config/database.php.example" ]; then
        sudo cp /home/bitnami/htdocs/moneyflow/config/database.php.example /home/bitnami/htdocs/moneyflow/config/database.php
        echo -e "${YELLOW}⚠ Archivo creado. DEBES EDITARLO con tus credenciales${NC}"
        echo ""
        echo "Ejecuta: sudo nano /home/bitnami/htdocs/moneyflow/config/database.php"
    else
        echo -e "${RED}✗ Tampoco existe database.php.example${NC}"
    fi
fi
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 4: Verificar extensiones PHP
# ==================================================
echo ""
echo "PASO 4: Verificando extensiones PHP..."
echo "=========================================="
php -v
echo ""
echo "Extensiones necesarias:"
php -m | grep -E "pdo|mysql|mysqli|mbstring|json"
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 5: Verificar procesos PHP
# ==================================================
echo ""
echo "PASO 5: Verificando que PHP-FPM está corriendo..."
echo "=========================================="
ps aux | grep php-fpm | grep -v grep
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 6: Arreglar permisos
# ==================================================
echo ""
echo "PASO 6: Corrigiendo permisos del proyecto..."
echo "=========================================="
cd /home/bitnami/htdocs/moneyflow
sudo chown -R daemon:daemon .
sudo chmod -R 755 .
if [ -f "config/database.php" ]; then
    sudo chmod 644 config/database.php
fi
echo -e "${GREEN}✓ Permisos actualizados${NC}"
ls -la /home/bitnami/htdocs/moneyflow/
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 7: Crear archivo de prueba PHP
# ==================================================
echo ""
echo "PASO 7: Creando archivo de prueba..."
echo "=========================================="
echo "<?php phpinfo(); ?>" | sudo tee /home/bitnami/htdocs/moneyflow/test.php > /dev/null
echo -e "${GREEN}✓ Archivo test.php creado${NC}"
echo ""
echo "Abre en tu navegador: https://aquinossolution.com/moneyflow/test.php"
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 8: Crear archivo de prueba de BD
# ==================================================
echo ""
echo "PASO 8: Creando archivo de prueba de conexión a BD..."
echo "=========================================="
cat > /tmp/test-db.php << 'DBTEST'
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
DBTEST

sudo mv /tmp/test-db.php /home/bitnami/htdocs/moneyflow/test-db.php
sudo chown daemon:daemon /home/bitnami/htdocs/moneyflow/test-db.php
echo -e "${GREEN}✓ Archivo test-db.php creado${NC}"
echo ""
echo "Abre en tu navegador: https://aquinossolution.com/moneyflow/test-db.php"
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 9: Reiniciar Apache
# ==================================================
echo ""
echo "PASO 9: Reiniciando Apache..."
echo "=========================================="
sudo /opt/bitnami/ctlscript.sh restart apache
echo ""
echo -e "${GREEN}✓ Apache reiniciado${NC}"
echo ""
read -p "Presiona ENTER para continuar..."

# ==================================================
# PASO 10: Verificar logs después del reinicio
# ==================================================
echo ""
echo "PASO 10: Verificando logs después de reiniciar..."
echo "=========================================="
sudo tail -30 /opt/bitnami/apache2/logs/error_log
echo ""

# ==================================================
# RESUMEN FINAL
# ==================================================
echo ""
echo "=========================================="
echo "  RESUMEN"
echo "=========================================="
echo ""
echo "Archivos de prueba creados:"
echo "  1. test.php      - Prueba que PHP funciona"
echo "  2. test-db.php   - Prueba conexión a base de datos"
echo ""
echo "URLs para probar:"
echo "  https://aquinossolution.com/moneyflow/test.php"
echo "  https://aquinossolution.com/moneyflow/test-db.php"
echo "  https://aquinossolution.com/moneyflow/"
echo ""
echo "Si test.php funciona pero test-db.php NO:"
echo "  → Problema de configuración de base de datos"
echo "  → Edita: sudo nano /home/bitnami/htdocs/moneyflow/config/database.php"
echo ""
echo "Si ninguno funciona:"
echo "  → Revisa los logs arriba para ver el error específico"
echo "  → Revisa los logs de PHP: sudo tail -50 /opt/bitnami/php/logs/php-fpm.log"
echo ""
echo "Credenciales por defecto de MoneyFlow:"
echo "  Usuario: admin"
echo "  Password: admin123"
echo ""
echo "=========================================="
echo ""
