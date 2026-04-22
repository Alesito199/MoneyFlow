# 🚀 Guía de Instalación - MoneyFlow

Sistema web de control financiero personal desarrollado en PHP, MySQL, HTML, CSS y JavaScript.

## 📋 Requisitos Previos

- **PHP** 7.4 o superior
- **MySQL** 5.7 o superior (o MariaDB 10.3+)
- **Servidor web** (Apache, Nginx, o servidor PHP integrado)
- Navegador web moderno

## 🔧 Instalación en Localhost

### 1. Instalar XAMPP / WAMP / LAMP

**Opción A: XAMPP (Recomendado para Windows/Mac/Linux)**
- Descargar desde: https://www.apachefriends.org/
- Instalar siguiendo el asistente
- Iniciar Apache y MySQL desde el panel de control

**Opción B: WAMP (Windows)**
- Descargar desde: https://www.wampserver.com/
- Instalar y ejecutar

**Opción C: LAMP (Linux)**
```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql
```

### 2. Clonar o Copiar el Proyecto

**Opción A: Con Git**
```bash
cd C:/xampp/htdocs  # En Windows
cd /var/www/html    # En Linux

git clone <URL_DEL_REPOSITORIO> moneyflow
```

**Opción B: Manual**
1. Descargar el proyecto como ZIP
2. Extraer en:
   - Windows (XAMPP): `C:\xampp\htdocs\moneyflow`
   - Mac (MAMP): `/Applications/MAMP/htdocs/moneyflow`
   - Linux (LAMP): `/var/www/html/moneyflow`

### 3. Configurar la Base de Datos

#### Paso 3.1: Crear la base de datos

**Opción A: Por phpMyAdmin**
1. Abrir http://localhost/phpmyadmin
2. Crear nueva base de datos llamada: **moneyflaw**
3. Seleccionar cotejamiento: `utf8mb4_unicode_ci`

**Opción B: Por línea de comandos**
```bash
mysql -u root -p
```
```sql
CREATE DATABASE moneyflaw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Paso 3.2: Importar el esquema

**Opción A: Por phpMyAdmin**
1. Seleccionar la base de datos `moneyflaw`
2. Ir a la pestaña "Importar"
3. Seleccionar el archivo `sql/schema.sql`
4. Click en "Continuar"

**Opción B: Por línea de comandos**
```bash
mysql -u root -p moneyflaw < sql/schema.sql
```

### 4. Configurar la Conexión

Editar el archivo `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'moneyflaw';
private $username = 'root';      // Cambiar según tu configuración
private $password = '';          // Cambiar según tu configuración
```

**Nota:** Si tu MySQL tiene contraseña, ingresarla en el campo `$password`.

### 5. Acceder al Sistema

Abrir en el navegador:
```
http://localhost/moneyflow
```

**Credenciales de prueba:**
- Usuario: `admin`
- Contraseña: `admin123`

## ✅ Verificar la Instalación

Si todo está correcto, deberías ver:
1. ✅ Página de login
2. ✅ Después de iniciar sesión, el Dashboard
3. ✅ KPIs con información financiera
4. ✅ Gráfico de gastos por categoría
5. ✅ Menú lateral con todas las opciones

## 🛠️ Solución de Problemas Comunes

### Error: "Error de conexión a la base de datos"

**Solución:**
1. Verificar que MySQL está corriendo
2. Verificar credenciales en `config/database.php`
3. Verificar que la base de datos `moneyflaw` existe

### Error: "No se encontró configuración para tu usuario"

**Solución:**
```sql
-- Ejecutar en phpMyAdmin o consola MySQL
INSERT INTO configuracion (
    user_id, ingreso_mensual, monto_ahorro, monto_gourmet,
    saldo_inicial, gourmet_inicial, objetivo_ahorro,
    fecha_inicio, fecha_fin
) VALUES (
    1, 5000000, 1200000, 300000, 
    4000000, 500000, 1200000,
    '2026-04-01', '2026-04-30'
);
```

### Error: "Class 'Database' not found"

**Solución:**
Verificar que `config/constants.php` existe y contiene:
```php
<?php
define('MONEDA', 'Gs.');
// ... más constantes
```

### El gráfico no aparece

**Solución:**
- Verificar conexión a internet (Chart.js se carga desde CDN)
- O descargar Chart.js y servir localmente

## 📱 Estructura del Sistema

```
moneyflow/
├── assets/
│   ├── css/
│   │   └── style.css          # Estilos del sistema
│   └── js/
│       └── main.js             # JavaScript personalizado
├── config/
│   ├── database.php            # Configuración de BD
│   └── constants.php           # Constantes del sistema
├── dashboard/
│   ├── index.php               # Dashboard principal
│   ├── expenses.php            # Gastos variables
│   ├── gastos_fijos.php        # Gastos fijos
│   └── configuracion.php       # Configuración
├── forms/
│   └── add_expense.php         # Formulario de gastos
├── includes/
│   ├── auth.php                # Autenticación
│   └── functions.php           # Funciones del sistema
├── sql/
│   └── schema.sql              # Esquema de base de datos
├── index.php                   # Punto de entrada
├── login.php                   # Página de login
└── logout.php                  # Cerrar sesión
```

## 🎨 Características del Sistema

### Dashboard Principal
- ✅ Ingreso mensual
- ✅ Ahorro programado
- ✅ Total de gastos fijos
- ✅ Gastos variables del periodo
- ✅ Dinero disponible
- ✅ Dinero disponible real (después de gastos)
- ✅ Gráfico de gastos por categoría (Chart.js)
- ✅ Análisis de ritmo de gasto
- ✅ Últimos gastos registrados

### Gastos Fijos
- ✅ Agregar gastos fijos (arriendo, servicios, etc.)
- ✅ Editar gastos fijos
- ✅ Eliminar gastos fijos
- ✅ Cálculo automático del total mensual

### Gastos Variables
- ✅ Registrar gastos diarios
- ✅ Categorizar gastos (comida, transporte, salud, etc.)
- ✅ Método de pago (efectivo, tarjeta gourmet)
- ✅ Ver historial completo
- ✅ Eliminar gastos
- ✅ Filtrado por periodo

### Configuración
- ✅ Configurar ingreso mensual
- ✅ Establecer meta de ahorro
- ✅ Configurar monto gourmet
- ✅ Definir periodo de control
- ✅ Ver resumen de cálculos

### Cálculos Automáticos
```
Disponible = Ingreso Mensual - Monto de Ahorro
Disponible Real = Disponible - Gastos Fijos - Gastos Variables
```

## 🔐 Seguridad

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Sesiones PHP para autenticación
- ✅ Consultas preparadas (PDO) para prevenir SQL Injection
- ✅ Validación de datos en servidor
- ✅ Control de acceso por usuario

## 📊 Base de Datos

### Tablas Principales

1. **usuarios**: Almacena usuarios del sistema
2. **configuracion**: Configuración financiera por usuario
3. **gastos_fijos**: Gastos mensuales fijos
4. **gastos**: Gastos variables diarios

## 🎯 Uso del Sistema

### 1. Primer Uso
1. Iniciar sesión con usuario `admin` / `admin123`
2. Ir a **Configuración**
3. Configurar:
   - Ingreso mensual
   - Meta de ahorro
   - Periodo de control

### 2. Configurar Gastos Fijos
1. Ir a **Gastos Fijos**
2. Agregar gastos mensuales (arriendo, servicios, etc.)

### 3. Registrar Gastos Diarios
1. Click en **Agregar Gasto**
2. Llenar el formulario
3. Seleccionar categoría y método de pago
4. Guardar

### 4. Ver Dashboard
El dashboard mostrará automáticamente:
- Tus KPIs financieros
- Gráfico de gastos por categoría
- Análisis de ritmo de gasto
- Últimos gastos

## 🌐 Despliegue en Servidor

Para desplegar en un servidor web:

1. Subir archivos por FTP/SFTP
2. Crear base de datos en el hosting
3. Importar `sql/schema.sql`
4. Editar `config/database.php` con credenciales del hosting
5. Asegurarse de que los permisos de archivos sean correctos

## 💻 Tecnologías Utilizadas

- **Backend**: PHP 7.4+ (PDO para base de datos)
- **Base de Datos**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3 (Variables CSS)
- **JavaScript**: Vanilla JS + Chart.js 4.4.0
- **Iconos**: Font Awesome 6.4.0
- **Diseño**: Responsive, Mobile-First

## 📝 Crear Nuevos Usuarios

```sql
-- Ejecutar en phpMyAdmin o consola MySQL
INSERT INTO usuarios (username, password, nombre, email, rol) 
VALUES (
    'nuevo_usuario', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- admin123
    'Nombre Completo',
    'email@example.com',
    'usuario'
);

-- Luego agregar configuración inicial para ese usuario
INSERT INTO configuracion (
    user_id, ingreso_mensual, monto_ahorro, monto_gourmet,
    saldo_inicial, gourmet_inicial, objetivo_ahorro,
    fecha_inicio, fecha_fin
) VALUES (
    (SELECT id FROM usuarios WHERE username = 'nuevo_usuario'),
    3000000, 500000, 200000,
    2000000, 300000, 500000,
    CURDATE(), LAST_DAY(CURDATE())
);
```

**Para generar una nueva contraseña hasheada:**
```php
<?php
echo password_hash('tu_contraseña', PASSWORD_DEFAULT);
?>
```

## 🆘 Soporte

Si encuentras algún problema:
1. Revisar la sección "Solución de Problemas"
2. Verificar logs de PHP: `error_log` o `php_error.log`
3. Verificar logs de MySQL
4. Asegurarse de que todos los archivos fueron copiados correctamente

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

---

**¡Disfruta de MoneyFlow! 💰📊**
