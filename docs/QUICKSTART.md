# 🚀 GUÍA DE INICIO RÁPIDO - MoneyFlow

Esta guía te ayudará a tener MoneyFlow funcionando en **5 minutos**.

---

## ⚡ INSTALACIÓN EXPRESS

### 1️⃣ Requisitos Mínimos

```bash
✅ PHP 7.4+
✅ MySQL 5.7+
✅ Apache/Nginx
✅ 5 minutos de tu tiempo
```

### 2️⃣ Clonar e Instalar

```bash
# Clonar repositorio
git clone https://github.com/tuusuario/MoneyFlow.git
cd MoneyFlow

# Crear base de datos
mysql -u root -p -e "CREATE DATABASE moneyflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar estructura
mysql -u root -p moneyflow < sql/schema.sql
```

### 3️⃣ Configurar Conexión

**Copiar archivo de ejemplo:**
```bash
cp config/database.example.php config/database.php
```

**Editar `config/database.php`:**
```php
private $username = 'tu_usuario';
private $password = 'tu_password';
```

### 4️⃣ Configurar Tus Datos

**Editar en MySQL o en `sql/schema.sql` antes de importar:**

```sql
UPDATE configuracion SET 
    saldo_inicial = 4287264.00,      -- Tu saldo
    gourmet_inicial = 561290.00,     -- Tu saldo gourmet
    objetivo_ahorro = 1200000.00,    -- Tu objetivo
    fecha_inicio = '2026-04-01',     -- Fecha inicio
    fecha_fin = '2026-04-25'         -- Fecha fin
WHERE id = 1;
```

### 5️⃣ ¡Listo! Acceder al Sistema

```
http://localhost/MoneyFlow/dashboard/
```

O si usas el instalador web:
```
http://localhost/MoneyFlow/install.php
```

---

## 📱 PRIMEROS PASOS

### Registrar tu Primer Gasto

1. Ve a **"Nuevo Gasto"** en el menú
2. Completa el formulario:
   - **Fecha:** Hoy
   - **Monto:** Ejemplo: 50000
   - **Tipo:** Variable
   - **Categoría:** Supermercado
   - **Método:** Efectivo
   - **Descripción:** Compra semanal
3. **Guardar**

### Ver tu Dashboard

- **Saldo Actual:** Efectivo disponible
- **Gourmet Disponible:** Saldo de tarjeta
- **Total Gastado:** Suma de todos los gastos
- **Ahorro Proyectado:** Lo que te sobrará

---

## 🔗 INTEGRAR CON N8N (Opcional)

### Obtener Estado Financiero

```bash
curl http://localhost/MoneyFlow/api/status.php
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "saldo_actual": 4287264,
    "estado": "OK",
    "mensaje": "Tu situación financiera es saludable"
  }
}
```

### Registrar Gasto desde API

```bash
curl -X POST http://localhost/MoneyFlow/api/add_expense.php \
  -H "Content-Type: application/json" \
  -d '{
    "fecha": "2026-03-28",
    "tipo": "variable",
    "categoria": "supermercado",
    "descripcion": "Compra",
    "monto": 50000,
    "metodo": "efectivo"
  }'
```

**Ver guía completa:** [docs/N8N_INTEGRATION.md](N8N_INTEGRATION.md)

---

## 🎯 CONFIGURACIONES COMUNES

### Cambiar Límite de Alerta

**Archivo:** `config/constants.php`
```php
define('ALERTA_SALDO_MINIMO', 2000000); // 2 millones Gs
```

### Agregar Nueva Categoría

1. **Modificar enum en BD:**
```sql
ALTER TABLE gastos 
MODIFY COLUMN categoria ENUM(
    'electricidad', 'transporte', 'supermercado', 
    'servicios', 'otros', 'salud'  -- Nueva
);
```

2. **Actualizar constantes:**
```php
// config/constants.php
define('CATEGORIAS', [
    'electricidad' => 'Electricidad',
    'transporte' => 'Transporte',
    'supermercado' => 'Supermercado',
    'servicios' => 'Servicios',
    'salud' => 'Salud',  // Nueva
    'otros' => 'Otros'
]);
```

### Cambiar Periodo de Control

```sql
UPDATE configuracion SET 
    fecha_inicio = '2026-05-01',
    fecha_fin = '2026-05-31'
WHERE id = 1;
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### ❌ "No se puede conectar a la base de datos"

**Solución:**
```bash
# Verificar que MySQL está corriendo
sudo systemctl status mysql

# Verificar credenciales en config/database.php
# Probar conexión manual
mysql -u root -p moneyflow
```

### ❌ "Error 500" en el dashboard

**Solución:**
```bash
# Activar modo debug
# Editar: config/constants.php
define('DEBUG_MODE', true);

# Ver errores en el navegador o logs
tail -f /var/log/apache2/error.log
```

### ❌ Los gráficos no se muestran

**Solución:**
- Verificar conexión a internet (Chart.js se carga desde CDN)
- Abrir consola del navegador (F12) y buscar errores
- Verificar que hay gastos registrados

### ❌ Permisos denegados

**Solución (Linux/Mac):**
```bash
sudo chown -R www-data:www-data /var/www/MoneyFlow
sudo chmod -R 755 /var/www/MoneyFlow
```

**Solución (Windows/XAMPP):**
- Ejecutar XAMPP como Administrador

---

## 📚 RECURSOS ADICIONALES

- **README completo:** [README.md](../README.md)
- **Integración n8n:** [docs/N8N_INTEGRATION.md](N8N_INTEGRATION.md)
- **Estructura SQL:** [sql/schema.sql](../sql/schema.sql)
- **Funciones PHP:** [includes/functions.php](../includes/functions.php)

---

## 🎓 PRÓXIMOS PASOS

1. ✅ Registra tus gastos diariamente
2. 🔔 Configura alertas automáticas con n8n
3. 📊 Revisa tu dashboard cada noche
4. 🎯 Ajusta tu objetivo de ahorro según tus metas
5. 📱 Integra con WhatsApp/Telegram para mayor comodidad

---

## 💡 TIPS PRO

### Crear Respaldo Automático

```bash
# Agregar a crontab: crontab -e
0 2 * * * mysqldump -u root -p'password' moneyflow > /backups/moneyflow_$(date +\%Y\%m\%d).sql
```

### Habilitar HTTPS

```bash
# Instalar certbot (Let's Encrypt)
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d tudominio.com
```

### Optimizar Performance

```php
// Agregar cache en includes/functions.php
// Implementar Redis o Memcached para datos frecuentes
```

---

## 🚀 ¡Comienza Ahora!

```bash
# Un solo comando para iniciar
php -S localhost:8000
```

Luego accede a: **http://localhost:8000/dashboard/**

---

**¿Necesitas ayuda? Abre un issue en GitHub o consulta la documentación completa.**

**MoneyFlow - Tu control financiero simplificado 💰**
