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
