# 💰 MoneyFlow - Sistema de Control Financiero Personal

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/Chart.js-4.4.0-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" alt="Chart.js">
</p>

<p align="center">
  <strong>Sistema web completo para control financiero personal</strong><br>
  Desarrollado con PHP, MySQL, HTML, CSS y JavaScript vanilla (sin frameworks pesados)
</p>

---

## 🎯 ¿Qué es MoneyFlow?

MoneyFlow es una aplicación web diseñada para ayudarte a **controlar tus gastos diarios** con precisión. Te dice **cuánto puedes gastar por día** según tu ingreso, ahorro y gastos fijos, con reinicio automático cada 25 de mes.

### ✨ Características Destacadas

- 💰 **Presupuesto Diario Calculado**: `(Ingreso - Ahorro - Gastos Fijos) ÷ Días`
- 📊 **Dashboard con KPIs**: Ingreso, ahorro, gastos fijos, variables, disponible real
- 📈 **Gráficos Interactivos**: Visualiza gastos por categoría con Chart.js
- 🔄 **Reinicio Automático**: Alerta y configuración automática al finalizar cada periodo
- 📅 **Resumen Semanal**: Análisis de últimos 7 días de gastos
- 🛒 **CRUD Completo**: Gestión de gastos fijos y variables
- 📱 **Responsive**: Funciona en móviles, tablets y desktop
- 🔐 **Seguro**: Multiusuario con bcrypt y PDO prepared statements

---

## 🚀 Inicio Rápido

### 1️⃣ Requisitos Previos

- PHP 7.4+
- MySQL 5.7+ o MariaDB 10.3+
- Servidor web (Apache/Nginx) o XAMPP/WAMP/LAMP

### 2️⃣ Instalación en 3 Pasos

```bash
# 1. Clonar el repositorio
git clone <URL_REPOSITORIO> moneyflow
cd moneyflow

# 2. Crear e importar base de datos
mysql -u root -p -e "CREATE DATABASE moneyflaw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p moneyflaw < sql/schema.sql

# 3. Configurar conexión (editar config/database.php)
```

**Edita `config/database.php`** con tus credenciales:
```php
private $host = 'localhost';
private $db_name = 'moneyflaw';
private $username = 'root';
private $password = 'tu_password';
```

### 3️⃣ Acceder

```
http://localhost/moneyflow
```

**Credenciales por defecto:**
- Usuario: `admin`
- Password: `admin123`

⚠️ **Cambia la contraseña después del primer acceso**

---

## 📖 Documentación

| Documento | Descripción |
|-----------|-------------|
| **[GUIA_USO.md](GUIA_USO.md)** | 📱 Guía completa paso a paso para usuarios finales |
| **[INSTALL.md](INSTALL.md)** | 🔧 Instalación técnica detallada (XAMPP, WAMP, LAMP) |
| **[DEPLOY.md](DEPLOY.md)** | 🚀 Despliegue a servidor de producción con hosting |
| **[USUARIOS.md](USUARIOS.md)** | 👥 Crear y gestionar usuarios (CLI y Web) |

---

## 💡 Uso Rápido

### Configuración Inicial  
**Configuración** → Ingresa sueldo, meta ahorro, fechas periodo (ej: 01/04 al 30/04)  
**Gastos Fijos** → Agrega arriendo, servicios, suscripciones  
**Dashboard** → Ve tu presupuesto: **"Puedes gastar hoy: 91,666 Gs"**

### Uso Diario  
**Mañana**: Ver cuánto puedes gastar hoy  
**Durante el día**: Registrar cada gasto inmediatamente  
**Dashboard**: Actualiza en tiempo real "Ya gastaste", "Te queda"

### Reinicio Mensual  
Sistema alerta automáticamente al finalizar periodo → **Configuración** → **"Configurar Automáticamente"** → ¡Listo!

📘 **[Ver guía completa de uso](GUIA_USO.md)**

---


## 🏗️ Estructura del Proyecto

```
moneyflow/
├── assets/              # Recursos estáticos
│   ├── css/            # Hojas de estilo responsive
│   └── js/             # Scripts JavaScript
├── config/             # Configuración del sistema
│   ├── database.php    # Conexión a BD (configurar aquí)
│   └── constants.php   # Constantes y configuraciones
├── dashboard/          # Páginas del dashboard
│   ├── index.php       # Dashboard principal con KPIs y gráficos
│   ├── expenses.php    # Lista de gastos variables
│   ├── gastos_fijos.php # CRUD de gastos fijos mensuales
│   └── configuracion.php # Configuración del usuario
├── forms/              # Formularios
│   └── add_expense.php # Agregar nuevo gasto
├── includes/           # Lógica del backend
│   ├── auth.php        # Autenticación y sesiones
│   └── functions.php   # Funciones de negocio
├── sql/                # Base de datos
│   └── schema.sql      # Esquema completo con datos de ejemplo
├── index.php           # Página inicial (redirección)
├── login.php           # Página de login
└── logout.php          # Cerrar sesión
```

---

## 💻 Tecnologías

| Tecnología | Versión | Uso |
|------------|---------|-----|
| **PHP** | 7.4+ | Backend, lógica de negocios |
| **MySQL/MariaDB** | 5.7+ / 10.3+ | Base de datos relacional |
| **HTML5** | - | Estructura semántica |
| **CSS3** | - | Diseño responsive con variables CSS |
| **JavaScript** | ES6+ | Interactividad del cliente |
| **Chart.js** | 4.4.0 | Gráficos de gastos por categoría |
| **Font Awesome** | 6.4.0 | Iconografía moderna |
| **PDO** | - | Capa de abstracción de base de datos |

---

## 📊 Base de Datos

### Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| **usuarios** | Sistema multiusuario con roles (admin, usuario) |
| **configuracion** | Ingresos, ahorro, gourmet, periodos por usuario |
| **gastos_fijos** | Gastos mensuales recurrentes (arriendo, servicios) |
| **gastos** | Registro diario de gastos variables con categorías |

### Fórmulas de Cálculo

```
Presupuesto Diario = (Ingreso - Ahorro - Gastos Fijos) ÷ Días del Periodo

Disponible = Ingreso Mensual - Monto de Ahorro

Disponible Real = Ingreso - Ahorro - Gastos Fijos - Gastos Variables

Ritmo de Gasto = Gasto Real vs Gasto Esperado (con alertas +10%)
```

---

## 🔧 Configuraciones Avanzadas

### Cambiar Moneda

Editar `config/constants.php`:
```php
define('MONEDA', 'Gs.');  // Cambiar a: $, €, USD, etc.
```

### Agregar Categorías de Gastos

Editar array de categorías en `config/constants.php` o tablas.

### Ajustar Periodo de Reinicio

Totalmente personalizable. Cambiar en **Configuración** las fechas de inicio/fin según tu preferencia (ej: día 1, 15, 25 del mes o el día que prefieras).

---

## 🐛 Solución de Problemas Comunes

| Problema | Solución |
|----------|----------|
| **Error de conexión a BD** | Verificar credenciales en `config/database.php` y que MySQL esté corriendo |
| **Gráfico no aparece** | Verificar conexión a internet (Chart.js usa CDN) y que existan gastos registrados |
| **Errores de sesión** | Verificar permisos de escritura en directorio de sesiones PHP |
| **Presupuesto diario incorrecto** | Ir a Configuración → Actualizar (sin cambios) para recalcular |

📖 **[Ver soluciones detalladas en INSTALL.md](INSTALL.md)**

---

## 🚀 Roadmap - Mejoras Futuras

- [ ] Exportar reportes a PDF y Excel
- [ ] Gráficos de tendencias históricas mensuales
- [ ] Notificaciones push y por email
- [ ] Modo oscuro (dark mode)
- [ ] PWA para instalación como app móvil
- [ ] Presupuesto individualizado por categoría
- [ ] Recordatorios automáticos de gastos fijos
- [ ] Integración con bancos (API)
- [ ] Calculadora de metas financieras

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Para cambios importantes:

1. **Fork** el proyecto
2. Crear rama de feature: `git checkout -b feature/NuevaCaracteristica`
3. Commit cambios: `git commit -m 'Agregar nueva característica'`
4. Push a la rama: `git push origin feature/NuevaCaracteristica`
5. Abrir **Pull Request**

---

## 🔐 Seguridad

### Características Implementadas

✅ Contraseñas hasheadas con **bcrypt**  
✅ Consultas preparadas con **PDO** (anti SQL injection)  
✅ Validación de entrada en servidor  
✅ Control de sesiones PHP  
✅ Separación de credenciales en `.gitignore`  

### Recomendaciones Post-Instalación

⚠️ Cambiar contraseña del usuario admin  
⚠️ Configurar HTTPS con certificado SSL  
⚠️ Mantener PHP y MySQL actualizados  
⚠️ Hacer respaldos periódicos de la base de datos  

---

## 📄 Licencia

Este proyecto es código abierto y está disponible bajo la **Licencia MIT**.

---

## 👨‍💻 Autor

Desarrollado con ❤️ y ☕

---

## 🙏 Agradecimientos

- **Chart.js** - Por los gráficos interactivos y modernos
- **Font Awesome** - Por la iconografía completa
- **Comunidad PHP** - Por las mejores prácticas de desarrollo

---

<p align="center">
  <strong>💰 MoneyFlow - Control total de tus finanzas personales 📊</strong>
</p>

<p align="center">
  <a href="GUIA_USO.md">📖 Guía de Uso</a> •
  <a href="INSTALL.md">🔧 Instalación</a> •
  <a href="DEPLOY.md">🚀 Despliegue</a>
</p>

<p align="center">
  ¿Te gusta el proyecto? <strong>Dale una ⭐ en GitHub!</strong>
</p>

5. **Protege directorios sensibles** (opcional, archivo `.htaccess` en `/config/`):

```apache
Deny from all
```

## 👥 Usuarios por Defecto

El sistema incluye dos usuarios de ejemplo:

| Usuario | Contraseña | Rol   |
|---------|------------|-------|
| admin   | admin123   | Admin |
| maria   | maria123   | Usuario |

## 🗂️ Estructura del Proyecto

```
MoneyFlow/
├── assets/
│   ├── css/style.css          # Estilos profesionales
│   └── js/main.js             # JavaScript
├── config/
│   ├── database.php.example   # Ejemplo de configuración
│   └── constants.php          # Constantes del sistema
├── dashboard/
│   ├── index.php              # Dashboard principal
│   ├── expenses.php           # Registro de gastos
│   └── configuracion.php      # Configuración de periodo
├── forms/
│   └── add_expense.php        # Formulario de gastos
├── includes/
│   ├── auth.php               # Autenticación
│   └── functions.php          # Funciones del negocio
├── sql/
│   └── schema.sql             # Estructura de BD
├── index.php                  # Página inicial
├── login.php                  # Login
└── logout.php                 # Logout
```

## 💰 Uso del Sistema

1. **Login:** Ingresa con tus credenciales
2. **Dashboard:** Visualiza tu estado financiero actual
3. **Ingresar Gasto:** Registra nuevos gastos
4. **Registro de Gastos:** Consulta histórico completo
5. **Configuración:** Ajusta fechas del periodo y montos iniciales

## 🛠️ Soporte

Para problemas o dudas:
1. Revisa que la base de datos esté correctamente importada
2. Verifica las credenciales en `config/database.php`
3. Comprueba los permisos de archivos
4. Revisa los logs de PHP de tu servidor

## 📄 Licencia

Uso personal y educativo. - Control de Gastos Personal

Sistema web simple y profesional para control de gastos personales con soporte multiusuario.

## Características

- ✅ Sistema de login con base de datos
- ✅ Multiusuario (cada persona maneja sus propios gastos)
- ✅ Dashboard con resumen financiero personal
- ✅ Registro de gastos (efectivo y tarjeta gourmet)
- ✅ Listado completo de gastos por usuario
- ✅ Diseño profesional gris y blanco
- ✅ Seguimiento de meta de ahorro
- ✅ Análisis de ritmo de gasto

## Requisitos

- PHP 7.4+
- MySQL 5.7+
- Servidor web (Apache/Nginx)
- Extensión PDO de PHP

## Instalación

### 1. Crear la base de datos

```bash
# Crear la base de datos
mysql -u root -p -e "CREATE DATABASE moneyflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar el schema (incluye tabla de usuarios y datos de ejemplo)
mysql -u root -p moneyflow < sql/schema.sql
```

### 2. Configurar conexión

Editar `config/database.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'moneyflow');
define('DB_USER', 'root');
define('DB_PASS', 'tu_password');
```

### 3. Configurar servidor web

**Apache:** El sistema incluye `.htaccess` configurado.

**Nginx:** Agregar a tu configuración:

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

### 4. Acceder al sistema

```
http://localhost/moneyflow
```

## Usuarios por Defecto

El sistema viene con 2 usuarios de ejemplo:

| Usuario | Contraseña | Rol | Descripción |
|---------|-----------|-----|-------------|
| **admin** | admin123 | Administrador | Usuario con todos los permisos |
| **maria** | maria123 | Usuario | Usuario normal |

**IMPORTANTE:** Cambia estas contraseñas en producción.

## Agregar Nuevos Usuarios

### Opción 1: Directamente en MySQL

```sql
-- Usar el generador de contraseñas incluido
-- php generar_password.php

INSERT INTO usuarios (username, password, nombre, email, rol) VALUES
('nuevo_usuario', '$2y$10$tu_hash_generado', 'Nombre Usuario', 'email@example.com', 'usuario');

-- Crear su configuración inicial
INSERT INTO configuracion (user_id, saldo_inicial, gourmet_inicial, objetivo_ahorro, fecha_inicio, fecha_fin) VALUES
(LAST_INSERT_ID(), 2000000.00, 300000.00, 500000.00, '2026-04-01', '2026-04-25');
```

### Opción 2: Usar el generador de passwords

```bash
php generar_password.php
# Ingresar la contraseña deseada
# Copiar el hash generado y usarlo en el INSERT anterior
```

## Estructura del Sistema

```
moneyflow/
├── config/              # Configuración de base de datos
│   ├── database.php     # Conexión PDO
│   └── constants.php    # Constantes del sistema
├── includes/            # Funciones y autenticación
│   ├── auth.php         # Control de sesión
│   └── functions.php    # Lógica de negocio por usuario
├── dashboard/           # Panel principal
│   ├── index.php        # Dashboard personal
│   └── expenses.php     # Lista de gastos
├── forms/               # Formularios
│   └── add_expense.php  # Registrar nuevo gasto
├── assets/              # Recursos estáticos
│   └── css/style.css    # Estilos profesionales
├── sql/                 # Base de datos
│   └── schema.sql       # Schema con multiusuario
├── login.php            # Página de login
├── logout.php           # Cerrar sesión
├── index.php            # Punto de entrada
└── generar_password.php # Generador de contraseñas
```

## Uso del Sistema

### 1. Login
- Ingresar con usuario y contraseña
- Cada usuario solo ve sus propios gastos

### 2. Dashboard
- Ver resumen de saldo actual (efectivo y gourmet)
- Revisar total gastado en el periodo
- Verificar progreso de ahorro
- Análisis de ritmo de gasto (alerta si gastas muy rápido)
- Ver últimos 10 gastos

### 3. Ingresar Gasto
- Seleccionar tipo (necesario, opcional, emergencia)
- Elegir categoría (comida, transporte, salud, etc.)
- Indicar método de pago (efectivo o gourmet)
- Ingresar monto y descripción
- El gasto se guarda vinculado a tu usuario

### 4. Registro de Gastos
- Ver todos tus gastos del periodo
- Filtrados automáticamente por tu usuario
- Resumen de totales por método de pago

## Configuración Inicial por Usuario

Cada usuario tiene su propia configuración que se crea al insertar el usuario:

- **Saldo inicial:** Dinero en efectivo disponible
- **Tarjeta Gourmet:** Saldo disponible en la tarjeta
- **Objetivo de ahorro:** Meta de ahorro para el periodo
- **Periodo:** Fechas de inicio y fin del ciclo de gastos

### Valores de Ejemplo (Usuario Admin):
- Saldo inicial: 4,287,264 Gs
- Tarjeta Gourmet: 561,290 Gs
- Objetivo de ahorro: 1,200,000 Gs
- Periodo: 1-25 de cada mes

### Valores de Ejemplo (Usuario Maria):
- Saldo inicial: 2,000,000 Gs
- Tarjeta Gourmet: 300,000 Gs
- Objetivo de ahorro: 500,000 Gs
- Periodo: 1-25 de cada mes

Puedes modificar estos valores directamente en la tabla `configuracion`.

## Categorías de Gastos

- **Comida:** Supermercado, restaurantes, delivery
- **Transporte:** Combustible, Uber, taxi, colectivo
- **Salud:** Medicamentos, consultas médicas
- **Entretenimiento:** Cine, salidas, hobbies
- **Servicios:** Internet, luz, agua, teléfono
- **Otros:** Gastos no clasificados

## Tipos de Gasto

- **Necesario:** Gastos esenciales (comida, transporte al trabajo)
- **Opcional:** Gastos no urgentes (salidas, entretenimiento)
- **Emergencia:** Gastos imprevistos (médicos, reparaciones)

## Métodos de Pago

- **Efectivo:** Descuenta del saldo inicial
- **Gourmet:** Descuenta de la tarjeta gourmet

## Seguridad

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Sesiones PHP para autenticación
- ✅ Validación de usuario activo
- ✅ Filtrado de gastos por usuario (no se ven gastos de otros)
- ✅ Prepared statements en todas las consultas SQL

## Cambiar Contraseña de un Usuario

```sql
-- Primero generar el hash
-- php generar_password.php

-- Actualizar en la base de datos
UPDATE usuarios 
SET password = '$2y$10$nuevo_hash_aqui' 
WHERE username = 'nombre_usuario';
```

## Base de Datos

### Tablas Principales

1. **usuarios**: Información de cada usuario del sistema
2. **configuracion**: Configuración financiera por usuario
3. **gastos**: Todos los gastos registrados (con user_id)

### Relaciones

- Cada usuario tiene UNA configuración
- Cada usuario puede tener MUCHOS gastos
- Los gastos y la configuración se eliminan al eliminar el usuario (CASCADE)

## Personalización

### Cambiar Colores

Editar `assets/css/style.css` y modificar las variables CSS:

```css
:root {
    --primary: #4a5568;    /* Color principal */
    --secondary: #718096;  /* Color secundario */
    --dark: #2d3748;       /* Color oscuro */
    --light: #f7fafc;      /* Color claro */
}
```

### Añadir Nuevas Categorías

Editar `config/constants.php` y agregar a:

```php
define('CATEGORIAS', [
    'comida' => 'Comida',
    'nueva_categoria' => 'Nueva Categoría'  // Agregar aquí
]);
```

Luego actualizar el schema de la tabla `gastos` para incluir el nuevo ENUM.

## Soporte

Para problemas técnicos:
1. Verificar que la base de datos esté importada correctamente
2. Revisar las credenciales en `config/database.php`
3. Asegurarse de que PHP tenga habilitada la extensión PDO
4. Verificar permisos de escritura si usas sesiones en archivos

---

**Desarrollado con PHP, MySQL y diseño profesional**

Sistema creado para control de gastos personal con separación completa por usuario.
