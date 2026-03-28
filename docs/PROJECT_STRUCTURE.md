# 📦 ESTRUCTURA COMPLETA DEL PROYECTO

## ✅ ARCHIVOS CREADOS

### 📁 Raíz del Proyecto
```
MoneyFlow/
├── index.php                    # Redirige al dashboard
├── install.php                  # Instalador web con verificación
├── .htaccess                    # Configuración Apache
├── .gitignore                   # Archivos a ignorar en Git
└── README.md                    # Documentación principal
```

### 📁 config/ - Configuración
```
config/
├── database.php                 # Conexión a MySQL (crear desde .example)
├── database.example.php         # Plantilla de configuración
└── constants.php                # Constantes del sistema
```

**Funcionalidad:**
- Gestión de conexión PDO a MySQL
- Constantes globales (alertas, categorías, formatos)
- Zona horaria y configuración de errores

### 📁 sql/ - Base de Datos
```
sql/
└── schema.sql                   # Estructura completa de BD
```

**Incluye:**
- ✅ Tabla `configuracion` (saldo inicial, objetivo, fechas)
- ✅ Tabla `gastos` (registro de gastos con índices)
- ✅ Vista `resumen_gastos` (agregaciones automáticas)
- ✅ Procedimiento `obtener_estado_financiero`
- ✅ Datos iniciales pre-cargados

### 📁 includes/ - Lógica de Negocio
```
includes/
└── functions.php                # Funciones principales del sistema
```

**Funciones Clave:**
- `calcularEstadoFinanciero()` - Estado completo de finanzas
- `calcularGastosEfectivo()` / `calcularGastosGourmet()` - Totales por método
- `analizarRitmoGasto()` - Control inteligente de gasto
- `registrarGasto()` - Guardar nuevo gasto
- `obtenerGastosPorCategoria()` - Datos para gráficos
- `obtenerEvolucionDiaria()` - Timeline de gastos
- `formatearMoneda()` - Formato paraguayo (Gs)
- `enviarJSON()` - Respuestas API
- `sanitizar()` - Seguridad de inputs

### 📁 dashboard/ - Interfaz Principal
```
dashboard/
└── index.php                    # Dashboard con KPIs y gráficos
```

**Características:**
- 📊 4 KPIs principales (Saldo, Gourmet, Gastos, Ahorro)
- 📈 Barra de progreso de ahorro
- 🎯 Análisis de ritmo de gasto
- 📉 Gráficos Chart.js (categorías, evolución diaria)
- 📋 Tabla de últimos 10 gastos
- 🚨 Alertas visuales automáticas

### 📁 forms/ - Formularios
```
forms/
└── add_expense.php              # Registro de nuevos gastos
```

**Validaciones:**
- ✅ Campos obligatorios (client + server side)
- ✅ Monto > 0
- ✅ Fecha válida
- ✅ Categorías y métodos limitados
- ✅ Prevención SQL injection
- ✅ Mensajes de éxito/error

### 📁 api/ - REST API
```
api/
├── status.php                   # GET - Estado financiero
└── add_expense.php              # POST - Registrar gasto
```

**Características:**
- ✅ Headers CORS configurados
- ✅ Respuestas JSON estandarizadas
- ✅ Manejo de errores robusto
- ✅ Validaciones exhaustivas
- ✅ Mensajes personalizados según estado

### 📁 assets/ - Archivos Estáticos
```
assets/
├── css/
│   └── style.css                # Estilos completos del sistema
└── js/
    └── main.js                  # JavaScript + validaciones
```

**CSS Incluye:**
- 🎨 Variables CSS (colores, tamaños)
- 📱 Diseño responsive (mobile-first)
- 🌈 Gradientes modernos
- ⚡ Animaciones y transiciones
- 🎯 Sistema de grid flexible
- 📊 Estilos para gráficos

**JavaScript Incluye:**
- 🔍 Validación de formularios
- ⏱️ Auto-ocultar alertas
- 🎬 Animaciones de valores
- 🛠️ Funciones de utilidad

### 📁 docs/ - Documentación
```
docs/
├── N8N_INTEGRATION.md           # Guía completa de integración n8n
└── QUICKSTART.md                # Guía de inicio rápido
```

---

## 🔑 CARACTERÍSTICAS IMPLEMENTADAS

### 1. ✅ BASE DE DATOS OPTIMIZADA
- Tablas con tipos de datos correctos (DECIMAL para montos)
- Índices en columnas frecuentemente consultadas
- Vista SQL para agregaciones rápidas
- Procedimiento almacenado para consultas complejas
- Charset UTF-8 para soporte completo de caracteres

### 2. ✅ LÓGICA DE NEGOCIO ROBUSTA
- **Separación estricta:** Gourmet NO afecta saldo de efectivo
- **Cálculo de ahorro:** Saldo actual - Objetivo
- **Sistema de alertas:** Multinivel (OK, ALERTA, ALERTA_AVANZADA)
- **Análisis inteligente:** Compara gasto real vs esperado según el día
- **Validaciones:** Server-side + client-side

### 3. ✅ INTERFAZ PROFESIONAL
- Dashboard moderno y limpio
- Gráficos interactivos (Chart.js)
- Responsive design (funciona en móviles)
- Animaciones suaves
- Mensajes de feedback claros
- Diseño intuitivo

### 4. ✅ API REST COMPLETA
- **GET /api/status.php:** Estado financiero completo
- **POST /api/add_expense.php:** Registrar gastos
- Respuestas JSON consistentes
- Manejo de errores HTTP correcto
- CORS habilitado para integraciones

### 5. ✅ INTEGRACIÓN N8N
- 4 workflows documentados
- Alertas automáticas por WhatsApp/Telegram
- Registro desde emails bancarios
- Reporte diario automático
- Bot interactivo de Telegram
- JSON completo para importar en n8n

### 6. ✅ SEGURIDAD
- PDO con Prepared Statements
- Sanitización de inputs (htmlspecialchars)
- Validaciones exhaustivas
- .htaccess con protección de archivos
- Headers de seguridad (X-Frame-Options, etc.)

### 7. ✅ DOCUMENTACIÓN COMPLETA
- README.md detallado
- Guía de inicio rápido
- Integración n8n paso a paso
- Comentarios en código
- Ejemplos de uso

---

## 🚀 CÓMO USAR EL SISTEMA

### PASO 1: Instalar

```bash
# 1. Subir archivos al servidor
# 2. Crear base de datos
mysql -u root -p -e "CREATE DATABASE moneyflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Importar estructura
mysql -u root -p moneyflow < sql/schema.sql

# 4. Copiar config
cp config/database.example.php config/database.php

# 5. Editar credenciales en config/database.php
```

### PASO 2: Configurar Tus Datos

```sql
UPDATE configuracion SET 
    saldo_inicial = 4287264.00,
    gourmet_inicial = 561290.00,
    objetivo_ahorro = 1200000.00,
    fecha_inicio = '2026-04-01',
    fecha_fin = '2026-04-25'
WHERE id = 1;
```

### PASO 3: Acceder

```
http://tudominio.com/MoneyFlow/dashboard/
```

O usar el instalador web:
```
http://tudominio.com/MoneyFlow/install.php
```

### PASO 4: Usar

1. **Registrar gastos** → `/forms/add_expense.php`
2. **Ver dashboard** → `/dashboard/index.php`
3. **API para n8n** → `/api/status.php`

---

## 🔗 ENDPOINTS DE LA API

### 1. Obtener Estado Financiero

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

### 2. Registrar Gasto

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

## 📊 LÓGICA DEL SISTEMA

### Estados Posibles

| Estado | Condición | Acción |
|--------|-----------|--------|
| `OK` | Saldo > 2.000.000 y gasto normal | Todo bien ✅ |
| `ALERTA` | Saldo < 2.000.000 | Notificación ⚠️ |
| `ALERTA_AVANZADA` | Gasto > Esperado | Alerta fuerte 🚨 |

### Cálculos Principales

```php
// Saldo actual (Gourmet NO afecta!)
$saldoActual = $saldoInicial - $gastosEfectivo;

// Ahorro actual
$ahorroActual = $saldoActual - $objetivoAhorro;

// Porcentaje de ahorro
$porcentajeAhorro = ($ahorroActual / $objetivoAhorro) * 100;

// Gasto esperado según día del periodo
$porcentajePeriodo = ($diaActual / $diasTotales) * 100;
$gastoEsperado = ($presupuesto * $porcentajePeriodo) / 100;

// Alerta si gasto real > esperado
if ($gastoReal > $gastoEsperado) {
    estado = "ALERTA_AVANZADA";
}
```

---

## 🎯 PRÓXIMOS PASOS SUGERIDOS

### Personalización Básica
1. ✅ Ajustar valores iniciales en la BD
2. ✅ Cambiar límite de alerta (`constants.php`)
3. ✅ Agregar más categorías de gastos
4. ✅ Personalizar colores en `style.css`

### Integración n8n
1. ✅ Configurar alertas automáticas
2. ✅ Bot de Telegram para registro rápido
3. ✅ Reporte diario por WhatsApp
4. ✅ Registro automático desde emails

### Mejoras Avanzadas
1. 🔲 Sistema multi-usuario
2. 🔲 Autenticación con JWT
3. 🔲 App móvil (PWA o nativa)
4. 🔲 Exportar a CSV/PDF
5. 🔲 Gráficos adicionales
6. 🔲 Presupuesto por categoría
7. 🔲 Integración con bancos

---

## 🛡️ SEGURIDAD ADICIONAL

### Proteger API con API Key

Agregar al inicio de `api/status.php` y `api/add_expense.php`:

```php
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== 'TU_CLAVE_SECRETA_AQUI') {
    enviarJSON(['error' => 'No autorizado'], 401);
}
```

En n8n, agregar header:
```json
{
  "headers": {
    "X-API-KEY": "TU_CLAVE_SECRETA_AQUI"
  }
}
```

### Habilitar HTTPS

```bash
# Con Let's Encrypt
sudo certbot --apache -d tudominio.com
```

---

## 📝 NOTAS IMPORTANTES

1. **Archivo de configuración:** `config/database.php` NO se incluye en Git (ver `.gitignore`)
2. **Instalador:** Elimina `install.php` después de completar la instalación
3. **Permisos:** Asegúrate de que Apache tenga permisos de lectura
4. **Chart.js:** Requiere conexión a internet (CDN)
5. **Zona horaria:** Configurada para Paraguay (America/Asuncion)

---

## 🎓 RECURSOS

- **README principal:** [README.md](../README.md)
- **Guía rápida:** [docs/QUICKSTART.md](QUICKSTART.md)
- **Integración n8n:** [docs/N8N_INTEGRATION.md](N8N_INTEGRATION.md)

---

## ✅ CHECKLIST DE INSTALACIÓN

```
☑️ Base de datos creada
☑️ Schema SQL importado
☑️ config/database.php configurado
☑️ Valores iniciales ajustados
☑️ Apache/PHP funcionando
☑️ Dashboard accesible
☑️ Primer gasto registrado
☑️ API probada (curl o Postman)
☑️ install.php eliminado
```

---

**🎉 ¡Tu sistema MoneyFlow está 100% listo para usar!**

**Controla tus gastos, alcanza tus metas 💰**
