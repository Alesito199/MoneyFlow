# 🚀 Guía de Despliegue - MoneyFlow

Instrucciones paso a paso para subir MoneyFlow a tu servidor de producción.

## ✅ Pre-requisitos

Antes de comenzar, asegúrate de tener:

- [ ] Acceso a tu hosting (FTP, SSH o panel de control)
- [ ] Acceso a MySQL/MariaDB (phpMyAdmin o terminal)
- [ ] Nombre de dominio configurado (opcional)
- [ ] Cliente FTP (FileZilla, WinSCP) o acceso por panel

## 📤 Paso 1: Subir Archivos

### Opción A: Con FTP (FileZilla, WinSCP)

1. Conecta a tu servidor FTP
2. Navega a la carpeta `public_html/` o `www/`
3. Sube **todos** los archivos y carpetas del proyecto:
   ```
   assets/
   config/
   dashboard/
   forms/
   includes/
   sql/
   .gitignore
   index.php
   login.php
   logout.php
   README.md
   ```

### Opción B: Con cPanel File Manager

1. Accede a cPanel → File Manager
2. Navega a `public_html/`
3. Sube un archivo ZIP del proyecto
4. Click derecho → Extract

### Opción C: Con Git (si tu hosting lo soporta)

```bash
cd public_html/
git clone https://github.com/tu-usuario/MoneyFlow.git .
```

⚠️ **IMPORTANTE:** NO subas el archivo `config/database.php` local con tus credenciales de desarrollo.

## 🗄️ Paso 2: Configurar Base de Datos

### A. Crear Base de Datos

**En cPanel:**
1. MySQL Databases → Create New Database
2. Nombre: `moneyflaw` (o el que prefieras)
3. Crear usuario MySQL
4. Asignar usuario a la base de datos con TODOS los privilegios

**En phpMyAdmin:**
```sql
CREATE DATABASE moneyflaw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### B. Importar Estructura

**En phpMyAdmin:**
1. Selecciona la base de datos `moneyflaw`
2. Click en "Importar"
3. Selecciona el archivo `sql/schema.sql`
4. Click "Continuar"

**Por SSH:**
```bash
mysql -u tu_usuario -p moneyflaw < sql/schema.sql
```

### C. Verificar Importación

Deberías tener estas tablas:
- ✅ usuarios (2 registros: admin, maria)
- ✅ configuracion (2 registros)
- ✅ gastos (vacío o con registros de prueba)

## ⚙️ Paso 3: Configurar Conexión

1. En el servidor, copia el archivo de ejemplo:
   ```bash
   cp config/database.php.example config/database.php
   ```

2. Edita `config/database.php` (con File Manager o FTP):
   ```php
   private $host = 'localhost';           // O la IP de tu servidor MySQL
   private $db_name = 'moneyflaw';        // El nombre que creaste
   private $username = 'tu_usuario_mysql'; // Usuario de la BD
   private $password = 'tu_password_mysql';// Contraseña de la BD
   ```

3. Guarda los cambios

## 🔒 Paso 4: Seguridad

### A. Proteger Configuración

Asegúrate que `config/database.php` tenga permisos 644:
```bash
chmod 644 config/database.php
```

### B. Eliminar Archivos Sensibles (opcional)

Después de importar la BD, considera eliminar o mover:
```bash
rm sql/schema.sql  # O moverlo fuera del directorio web
```

### C. Cambiar Contraseñas

1. Accede al sistema: `https://tudominio.com/`
2. Login como admin (usuario: `admin`, contraseña: `admin123`)
3. Cambia inmediatamente la contraseña

### D. Habilitar HTTPS

Si tu hosting ofrece Let's Encrypt (cPanel → SSL/TLS):
1. Instala certificado SSL
2. Fuerza HTTPS (opcional, en `.htaccess`):
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

## 🧪 Paso 5: Pruebas

### Checklist de Verificación

- [ ] La página `https://tudominio.com/` redirige al login
- [ ] Puedes hacer login con `admin` / `admin123`
- [ ] El dashboard carga correctamente
- [ ] Se muestran las tarjetas de KPIs
- [ ] Puedes agregar un gasto de prueba
- [ ] El gasto aparece en "Registro de Gastos"
- [ ] Los cálculos son correctos
- [ ] Puedes cerrar sesión y volver a entrar

### Si algo falla:

1. **Error de conexión a BD:**
   - Verifica credenciales en `config/database.php`
   - Confirma que el usuario MySQL tiene permisos
   - Revisa el host (localhost, 127.0.0.1, IP externa)

2. **Página en blanco:**
   - Revisa logs de PHP (cPanel → Error Log)
   - Verifica permisos de archivos (755 carpetas, 644 archivos)
   - Confirma versión de PHP (mínimo 7.4)

3. **Error 500:**
   - Revisa permisos de `.htaccess`
   - Temporalmente renombra `.htaccess` para probar

## 📊 Paso 6: Configuración Inicial

1. **Ajustar Periodo:**
   - Dashboard → Configuración
   - Establece fecha de inicio y fin del periodo

2. **Configurar Montos:**
   - Saldo inicial
   - Gourmet inicial
   - Objetivo de ahorro

3. **Crear Usuarios Adicionales** (opcional):
   - Modifica directamente en phpMyAdmin
   - Tabla `usuarios`
   - Contraseñas: usa `password_hash()` en PHP

## 🎯 Optimizaciones Post-Despliegue

### Performance

1. **Habilitar caché de PHP** (en `.htaccess`):
   ```apache
   <IfModule mod_expires.c>
       ExpiresActive On
       ExpiresByType text/css "access plus 1 month"
       ExpiresByType text/javascript "access plus 1 month"
   </IfModule>
   ```

2. **Comprimir archivos** (en `.htaccess`):
   ```apache
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/html text/css text/javascript
   </IfModule>
   ```

### Monitoreo

1. **Logs de errores:** Revisa regularmente `error_log` en tu hosting
2. **Backups:** Configura backups automáticos de BD (cPanel → Backups)
3. **Actualizaciones:** Mantén PHP actualizado en tu hosting

## 📁 Estructura Final en Servidor

```
public_html/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   ├── constants.php
│   ├── database.php          ← Tu configuración (NO subir a Git)
│   └── database.php.example  ← Ejemplo de referencia
├── dashboard/
│   ├── configuracion.php
│   ├── expenses.php
│   └── index.php
├── forms/
│   └── add_expense.php
├── includes/
│   ├── auth.php
│   └── functions.php
├── sql/                      ← Considerar eliminar después de importar
│   └── schema.sql
├── .gitignore
├── index.php
├── login.php
├── logout.php
└── README.md
```

## 🆘 Soporte

Si encuentras problemas:

1. Revisa los logs de error de PHP
2. Verifica conexión a BD con un script simple
3. Confirma versión de PHP y extensiones
4. Revisa permisos de archivos y carpetas

---

**¡Listo!** Tu sistema MoneyFlow debería estar funcionando en producción 🎉
