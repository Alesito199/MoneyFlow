# 💰 MoneyFlow - Sistema de Control de Gastos Personales

Sistema web profesional para control de gastos personales con integración n8n, alertas inteligentes y dashboard en tiempo real.

![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 🎯 CARACTERÍSTICAS PRINCIPALES

✅ **Registro Rápido de Gastos** - Formulario intuitivo con validaciones  
✅ **Dashboard Interactivo** - Visualización en tiempo real con gráficos  
✅ **Separación Efectivo/Gourmet** - Control independiente de métodos de pago  
✅ **Alertas Inteligentes** - Análisis de ritmo de gasto  
✅ **API REST** - Integración con n8n, Telegram, WhatsApp  
✅ **Control por Periodo** - Sistema adaptable a cualquier rango de fechas  
✅ **Objetivo de Ahorro** - Seguimiento de metas financieras  
✅ **100% Responsive** - Funciona en desktop y móvil  

---

## 🚀 INSTALACIÓN RÁPIDA

### Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Composer (opcional)

### Paso 1: Clonar el repositorio

```bash
git clone https://github.com/tuusuario/MoneyFlow.git
cd MoneyFlow
```

### Paso 2: Configurar la base de datos

```bash
# Crear la base de datos
mysql -u root -p -e "CREATE DATABASE moneyflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar el schema
mysql -u root -p moneyflow < sql/schema.sql
```

### Paso 3: Configurar conexión

Editar `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'moneyflow';
private $username = 'root';        // Tu usuario MySQL
private $password = 'tu_password';  // Tu contraseña MySQL
```

### Paso 4: Configurar valores iniciales

Editar los valores en `sql/schema.sql` antes de importar, o actualizar directamente:

```sql
UPDATE configuracion SET 
    saldo_inicial = 4287264.00,
    gourmet_inicial = 561290.00,
    objetivo_ahorro = 1200000.00,
    fecha_inicio = '2026-04-01',
    fecha_fin = '2026-04-25'
WHERE id = 1;
```

### Paso 5: Acceder al sistema

```
http://localhost/MoneyFlow/dashboard/
```

---

## 📁 ESTRUCTURA DEL PROYECTO

```
MoneyFlow/
│
├── 📁 config/
│   ├── database.php          # Conexión a BD
│   └── constants.php         # Constantes del sistema
│
├── 📁 includes/
│   └── functions.php         # Lógica de negocio
│
├── 📁 dashboard/
│   └── index.php            # Dashboard principal
│
├── 📁 forms/
│   └── add_expense.php      # Formulario de gastos
│
├── 📁 api/
│   ├── status.php           # Endpoint de estado (GET)
│   └── add_expense.php      # Endpoint para gastos (POST)
│
├── 📁 assets/
│   ├── css/
│   │   └── style.css        # Estilos principales
│   └── js/
│       └── main.js          # JavaScript
│
├── 📁 sql/
│   └── schema.sql           # Estructura de BD
│
├── 📁 docs/
│   └── N8N_INTEGRATION.md   # Guía de integración n8n
│
└── README.md
```

---

## 🎨 FUNCIONALIDADES DETALLADAS

### 1. Dashboard

**Ubicación:** `/dashboard/index.php`

- **KPIs principales:** Saldo, Gourmet, Gastos Totales, Ahorro
- **Análisis de ritmo:** Detecta si gastas más rápido de lo esperado
- **Gráfico de categorías:** Distribución de gastos (Chart.js)
- **Evolución diaria:** Línea temporal de gastos
- **Tabla de gastos:** Últimos 10 movimientos

### 2. Registro de Gastos

**Ubicación:** `/forms/add_expense.php`

**Campos:**
- Fecha (date picker)
- Tipo: Fijo/Variable
- Categoría: Electricidad, Transporte, Supermercado, Servicios, Otros
- Descripción (textarea)
- Monto (número, validación > 0)
- Método: Efectivo/Gourmet

**Validaciones:**
- ✅ Campos obligatorios
- ✅ Monto mayor a 0
- ✅ Formato de fecha válido
- ✅ Prevención SQL injection

### 3. API REST

#### Endpoint: Estado Financiero

```http
GET /api/status.php
```

**Respuesta:**
```json
{
  "success": true,
  "timestamp": "2026-03-28 10:30:00",
  "data": {
    "saldo_actual": 3500000,
    "gourmet_disponible": 400000,
    "ahorro_actual": 2300000,
    "porcentaje_ahorro": 191.67,
    "estado": "OK",
    "alerta": false,
    "mensaje": "Tu situación financiera es saludable",
    "analisis_ritmo": {
      "dia_actual": 10,
      "dias_totales": 25,
      "porcentaje_periodo": 40.00,
      "gasto_esperado": 1714905.60,
      "gasto_real": 787264.00,
      "alerta_avanzada": false
    }
  }
}
```

#### Endpoint: Registrar Gasto

```http
POST /api/add_expense.php
Content-Type: application/json

{
  "fecha": "2026-03-28",
  "tipo": "variable",
  "categoria": "supermercado",
  "descripcion": "Compra semanal",
  "monto": 150000,
  "metodo": "efectivo"
}
```

**Respuesta:**
```json
{
  "success": true,
  "timestamp": "2026-03-28 10:30:00",
  "data": {
    "id": 123,
    "mensaje": "Gasto registrado exitosamente"
  }
}
```

---

## 🔗 INTEGRACIÓN CON N8N

### Casos de Uso

1. **Alertas Automáticas por WhatsApp/Telegram**
   - Verifica cada hora si hay alertas
   - Envía notificación si el saldo es bajo

2. **Registro desde Email Bancario**
   - Lee emails de tu banco
   - Extrae monto y categoría
   - Registra automáticamente

3. **Reporte Diario**
   - Envía resumen cada noche
   - Incluye análisis de ritmo de gasto

4. **Bot de Telegram Interactivo**
   - Registra gastos desde chat
   - Consulta saldo en tiempo real

📖 **Ver guía completa:** [docs/N8N_INTEGRATION.md](docs/N8N_INTEGRATION.md)

---

## 🧠 LÓGICA DE NEGOCIO

### Cálculo de Saldo

```php
$saldoActual = $saldoInicial - $gastosEfectivo;
// ⚠️ Los gastos en gourmet NO afectan el saldo de efectivo
```

### Cálculo de Ahorro

```php
$ahorroActual = $saldoActual - $objetivoAhorro;
$porcentajeAhorro = ($ahorroActual / $objetivoAhorro) * 100;
```

### Sistema de Alertas

| Condición | Estado |
|-----------|--------|
| `saldo_actual < 2.000.000 Gs` | `ALERTA` |
| `gasto_real > gasto_esperado` | `ALERTA_AVANZADA` |
| Todo OK | `OK` |

### Análisis de Ritmo de Gasto

```php
$porcentajePeriodo = ($diaActual / $diasTotales) * 100;
$gastoEsperado = ($presupuesto * $porcentajePeriodo) / 100;

if ($gastoReal > $gastoEsperado) {
    // ⚠️ Estás gastando más rápido de lo esperado
}
```

---

## 🔧 CONFIGURACIÓN AVANZADA

### Cambiar Límite de Alerta

Editar `config/constants.php`:

```php
define('ALERTA_SALDO_MINIMO', 2000000); // Cambiar a tu valor
```

### Agregar Nuevas Categorías

```sql
ALTER TABLE gastos 
MODIFY COLUMN categoria ENUM(
    'electricidad', 
    'transporte', 
    'supermercado', 
    'servicios', 
    'otros',
    'salud',        -- Nueva categoría
    'educacion'     -- Nueva categoría
) NOT NULL;
```

Actualizar `config/constants.php`:

```php
define('CATEGORIAS', [
    'electricidad' => 'Electricidad',
    'transporte' => 'Transporte',
    'supermercado' => 'Supermercado',
    'servicios' => 'Servicios',
    'salud' => 'Salud',           // Nueva
    'educacion' => 'Educación',   // Nueva
    'otros' => 'Otros'
]);
```

---

## 🔐 SEGURIDAD

### Proteger la API

Agregar autenticación con API Key:

```php
// En api/status.php y api/add_expense.php
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== 'TU_CLAVE_SECRETA') {
    enviarJSON(['error' => 'No autorizado'], 401);
}
```

### Mejores Prácticas Implementadas

✅ Prepared Statements (PDO) - Prevención SQL Injection  
✅ Sanitización de inputs - htmlspecialchars()  
✅ Validación server-side  
✅ Headers CORS configurables  
✅ Manejo de errores con try-catch  
✅ Logs de errores  

---

## 📊 BASE DE DATOS

### Tablas Principales

#### `configuracion`
- Almacena configuración global del sistema
- Solo debería tener 1 registro (id=1)

#### `gastos`
- Registro de todos los gastos
- Índices en: fecha, metodo, categoria, tipo

### Procedimiento Almacenado

```sql
CALL obtener_estado_financiero('2026-04-01', '2026-04-25');
```

---

## 🚀 ESCALABILIDAD

### Convertir a Multi-Usuario

1. Agregar tabla `usuarios`:
```sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

2. Agregar `usuario_id` a tablas:
```sql
ALTER TABLE gastos ADD COLUMN usuario_id INT;
ALTER TABLE configuracion ADD COLUMN usuario_id INT;
```

3. Implementar sistema de login (sesiones PHP)

---

## 🐛 TROUBLESHOOTING

### Error: "No se pudo conectar a la base de datos"

✅ Verifica usuario y contraseña en `config/database.php`  
✅ Asegúrate de que MySQL esté corriendo  
✅ Verifica que la BD `moneyflow` exista  

### Los gráficos no se muestran

✅ Verifica que Chart.js cargue (conexión a internet)  
✅ Revisa la consola del navegador (F12)  

### La API devuelve error 500

✅ Activa `DEBUG_MODE` en `config/constants.php`  
✅ Revisa logs de PHP  
✅ Verifica permisos de archivos  

---

## 📄 LICENCIA

Este proyecto está bajo la Licencia MIT.

---

## 👨‍💻 AUTOR

Creado con ❤️ para control financiero personal

**¿Te gusta el proyecto? ⭐ Dale una estrella en GitHub**

---

**MoneyFlow - Controla tus finanzas, alcanza tus metas 💰**
