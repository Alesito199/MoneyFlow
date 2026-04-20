# 👥 Gestión de Usuarios - MoneyFlow

Guía para crear y administrar usuarios en el sistema MoneyFlow.

---

## 🎯 Opciones Disponibles

MoneyFlow incluye **dos formas** de crear nuevos usuarios:

### 1️⃣ Script CLI (Línea de Comandos)
**Archivo:** `crear_usuario.php`  
**Para:** Administradores del servidor, creación rápida desde terminal

### 2️⃣ Interfaz Web (Admin Panel)
**URL:** `http://localhost/moneyflow/admin/crear_usuario.php`  
**Para:** Administradores con sesión iniciada, interfaz gráfica

---

## 🖥️ Opción 1: Script CLI (Recomendado)

### Uso Interactivo

**Ejecuta el script:**
```bash
php crear_usuario.php
```

**El script te pedirá:**
1. Usuario (sin espacios)
2. Nombre completo
3. Email (opcional)
4. Contraseña (mínimo 8 caracteres, oculta en pantalla)
5. Confirmación de contraseña
6. Rol (admin/usuario)
7. Confirmación final

**Ejemplo:**
```
╔════════════════════════════════════════════╗
║   MoneyFlow - Creador de Usuarios CLI    ║
╚════════════════════════════════════════════╝

Ingresa los datos del nuevo usuario:

Usuario (sin espacios): maria
Nombre completo: María González
Email (opcional, Enter para omitir): maria@email.com
Contraseña (mínimo 8 caracteres): ********
Confirmar contraseña: ********
Rol (admin/usuario) [usuario]: usuario

──────────────────────────────────────────────
Resumen del usuario a crear:
──────────────────────────────────────────────
Usuario:  maria
Nombre:   María González
Email:    maria@email.com
Rol:      usuario
──────────────────────────────────────────────

¿Crear este usuario? (s/n): s

Creando usuario...

✓ ¡Usuario creado exitosamente!
  ID: 2
  Usuario: maria
  Rol: usuario

El usuario puede iniciar sesión en: http://localhost/moneyflow
Credenciales: maria / [contraseña ingresada]
```

### Uso con Parámetros (No Interactivo)

**Sintaxis:**
```bash
php crear_usuario.php <username> "Nombre" <email> <password> [rol]
```

**Ejemplos:**
```bash
# Usuario normal con email
php crear_usuario.php juan "Juan Perez" juan@email.com mipass123 usuario

# Usuario admin sin email
php crear_usuario.php admin2 "Admin Secundario" null adminpass456 admin

# Usuario normal (rol por defecto)
php crear_usuario.php pedro "Pedro Lopez" pedro@mail.com pedro2026
```

**Parámetros:**
- `<username>` - Nombre de usuario único (obligatorio)
- `"Nombre"` - Nombre completo entre comillas (obligatorio)
- `<email>` - Email o "null" para omitir (obligatorio poner algo)
- `<password>` - Contraseña mínimo 8 caracteres (obligatorio)
- `[rol]` - "admin" o "usuario" (opcional, default: usuario)

### Características del Script CLI

✅ **Validaciones automáticas:**
- Usuario único (no duplicados)
- Contraseña mínimo 8 caracteres
- Email válido (si se proporciona)
- Rol válido (admin/usuario)
- Confirmación de contraseña en modo interactivo

✅ **Seguridad:**
- Contraseñas hasheadas con bcrypt
- Input oculto para contraseñas
- Solo ejecución desde CLI (no desde navegador)
- Confirmación antes de crear

✅ **Automatización:**
- Crea configuración inicial automáticamente
- Establece periodo mensual actual
- Usuario activo por defecto
- Colores en terminal para mejor visualización

---

## 🌐 Opción 2: Interfaz Web (Admin Panel)

### Acceso

1. **Inicia sesión** como administrador
2. **Accede a:** `http://localhost/moneyflow/admin/crear_usuario.php`

### Características

✅ **Formulario visual** con validación
✅ **Lista de usuarios** existentes
✅ **Filtros y búsqueda** (próximamente)
✅ **Solo accesible** para rol admin

### Uso

1. **Completa el formulario:**
   - Usuario (único, sin espacios)
   - Nombre completo
   - Email (opcional)
   - Rol (usuario/admin)
   - Contraseña (mínimo 8 caracteres)
   - Confirmación de contraseña

2. **Click "Crear Usuario"**

3. **Verás confirmación** y el nuevo usuario aparecerá en la tabla

### Seguridad Web

🔒 **Protecciones implementadas:**
- Solo usuarios con rol "admin" pueden acceder
- Redirección automática a login si no está autenticado
- Validación de formulario en servidor
- Protección contra SQL injection (PDO prepared statements)
- Hash bcrypt para contraseñas

---

## 👤 Tipos de Usuarios

### Usuario Normal
- **Rol:** `usuario`
- **Permisos:**
  - Ver su propio dashboard
  - Gestionar sus gastos
  - Configurar su información financiera
  - Ver sus reportes

### Administrador
- **Rol:** `admin`
- **Permisos adicionales:**
  - Crear nuevos usuarios
  - Ver panel de administración
  - Acceso a herramientas admin
  - Gestión del sistema

---

## 🔑 Credenciales por Defecto

El sistema viene con un usuario administrador:

```
Usuario: admin
Contraseña: admin123
Rol: admin
```

⚠️ **IMPORTANTE:** Cambia esta contraseña inmediatamente después de la instalación.

---

## 📊 Estructura de Usuarios

Cada usuario creado incluye:

### Tabla `usuarios`
- **username** - Usuario único
- **password** - Hash bcrypt de la contraseña
- **nombre** - Nombre completo
- **email** - Email (opcional)
- **rol** - admin o usuario
- **activo** - 1 (activo) o 0 (inactivo)
- **created_at** - Fecha de creación
- **updated_at** - Última actualización

### Tabla `configuracion` (Auto-generada)
- **user_id** - Relación con usuario
- **ingreso_mensual** - 0 (el usuario lo configura)
- **monto_ahorro** - 0
- **monto_gourmet** - 0
- **fecha_inicio** - Primer día del mes actual
- **fecha_fin** - Último día del mes actual

---

## ❓ Preguntas Frecuentes

### ¿Puedo crear usuarios desde phpMyAdmin?

**Sí**, pero no es recomendado porque:
- Debes hashear la contraseña manualmente
- Debes crear la configuración asociada
- Es más propenso a errores

**Mejor usa:** Los scripts proporcionados.

### ¿Cómo cambiar la contraseña de un usuario?

**Opción 1:** Desde el dashboard del usuario (próximamente)

**Opción 2:** Script manual:
```php
<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

$username = 'usuario';
$nueva_password = 'nueva_password_123';

$hash = password_hash($nueva_password, PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
$stmt->execute([$hash, $username]);

echo "Contraseña actualizada\n";
```

### ¿Cómo eliminar un usuario?

**Desde MySQL:**
```sql
DELETE FROM usuarios WHERE username = 'usuario_a_eliminar';
```

**Nota:** Esto eliminará en cascada:
- Configuración del usuario
- Gastos fijos del usuario
- Gastos variables del usuario

### ¿Puedo desactivar un usuario sin eliminarlo?

**Sí:**
```sql
UPDATE usuarios SET activo = 0 WHERE username = 'usuario';
```

Para reactivar:
```sql
UPDATE usuarios SET activo = 1 WHERE username = 'usuario';
```

### ¿Puedo cambiar el rol de un usuario?

**Sí:**
```sql
-- Hacer admin
UPDATE usuarios SET rol = 'admin' WHERE username = 'usuario';

-- Hacer usuario normal
UPDATE usuarios SET rol = 'usuario' WHERE username = 'admin2';
```

---

## 🛠️ Solución de Problemas

### Error: "El usuario ya existe"

**Causa:** Username duplicado

**Solución:** Usa un nombre de usuario diferente

### Error: "Error de conexión"

**Causa:** Credenciales incorrectas en `config/database.php`

**Solución:** 
```bash
# Verifica la configuración
nano config/database.php

# Verifica que MySQL esté corriendo
# Windows (XAMPP):
# Abre XAMPP Control Panel → Start MySQL

# Linux/Mac:
sudo systemctl status mysql
```

### El script CLI no muestra colores en Windows

**Causa:** Terminal de Windows no soporta códigos ANSI por defecto

**Solución:** 
- Usa Windows Terminal (recomendado)
- O usa Git Bash
- O ejecuta sin colores (funciona igual)

### No puedo acceder a admin/crear_usuario.php

**Causa:** No has iniciado sesión como admin

**Solución:**
1. Inicia sesión con usuario admin
2. Verifica que tu rol sea "admin" en la base de datos
3. Verifica la ruta: `http://localhost/moneyflow/admin/crear_usuario.php`

---

## 📝 Buenas Prácticas

✅ **DO (Hacer):**
- Usar contraseñas fuertes (mínimo 8 caracteres, mezcla de letras, números, símbolos)
- Cambiar contraseña del admin por defecto
- Crear usuarios individuales (no compartir credenciales)
- Usar emails reales para recuperación futura
- Desactivar usuarios en lugar de eliminarlos

❌ **DON'T (No hacer):**
- Usar contraseñas obvias (123456, password, admin123)
- Compartir credenciales entre usuarios
- Dar rol admin sin necesidad
- Crear usuarios directamente en la base de datos
- Almacenar contraseñas en texto plano

---

## 🔗 Ver También

- **[README.md](README.md)** - Información general del proyecto
- **[INSTALL.md](INSTALL.md)** - Instalación del sistema
- **[GUIA_USO.md](GUIA_USO.md)** - Guía de uso para usuarios finales

---

## 💡 Ejemplos de Casos de Uso

### Caso 1: Familia con Finanzas Compartidas

```bash
# Papa (admin)
php crear_usuario.php papa "José García" jose@email.com papapass123 admin

# Mama (usuaria)
php crear_usuario.php mama "María García" maria@email.com mamapass123 usuario

# Hijo mayor (usuario)
php crear_usuario.php carlos "Carlos García" carlos@email.com carlospass123 usuario
```

### Caso 2: Empresa con Contadores

```bash
# Contador principal (admin)
php crear_usuario.php contador "Juan Contador" contador@empresa.com pass123 admin

# Empleados
php crear_usuario.php emp001 "Pedro Sánchez" pedro@empresa.com pedropass usuario
php crear_usuario.php emp002 "Ana López" ana@empresa.com anapass usuario
```

### Caso 3: Uso Personal con Testing

```bash
# Usuario principal
php crear_usuario.php yo "Mi Nombre" mi@email.com mipass123 admin

# Usuario de prueba
php crear_usuario.php test "Usuario Test" null testpass123 usuario
```

---

<p align="center">
  <strong>👥 Gestión eficiente de usuarios en MoneyFlow 🚀</strong>
</p>
