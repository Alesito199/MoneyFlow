# 📖 Guía de Uso - MoneyFlow

Guía completa para usar el sistema de control financiero personal MoneyFlow.

---

## 🚀 Acceso Inicial

### Primera Vez

1. **Accede al sistema**: `http://localhost/moneyflow` (o tu dominio)
2. **Credenciales por defecto**:
   - Usuario: `admin`
   - Contraseña: `admin123`
3. **⚠️ IMPORTANTE**: Cambia la contraseña inmediatamente después del primer acceso

---

## 📋 Configuración Inicial (Hazlo una sola vez)

### Paso 1: Configurar tus Datos Financieros

**Dashboard → Menú lateral → Configuración**

Completa la información básica:

```
💰 Ingreso Mensual:      5,000,000 Gs    (Tu sueldo)
🐷 Monto de Ahorro:      1,200,000 Gs    (Cuánto quieres ahorrar)
🍽️  Monto Gourmet:         300,000 Gs    (Presupuesto tarjeta, opcional)
📅 Fecha Inicio:          01/04/2026      (Inicio del periodo mensual)
📅 Fecha Fin:             30/04/2026      (Fin del periodo mensual)
```

**Click en "Actualizar Configuración"**

✅ El sistema calculará automáticamente tu presupuesto diario:

```
Presupuesto Diario = (Ingreso - Ahorro - Gastos Fijos) ÷ Días del Periodo
```

### Paso 2: Agregar Gastos Fijos

**Dashboard → Menú lateral → Gastos Fijos**

Agrega todos tus gastos mensuales recurrentes:

| Nombre          | Monto       | Ejemplo                    |
|-----------------|-------------|----------------------------|
| Arriendo        | 800,000 Gs  | Alquiler de vivienda       |
| Internet        | 120,000 Gs  | Servicio de internet       |
| Luz             | 80,000 Gs   | Factura eléctrica          |
| Agua            | 45,000 Gs   | Factura de agua            |
| Netflix         | 35,000 Gs   | Suscripción streaming      |
| Gimnasio        | 150,000 Gs  | Membresía mensual          |

**Click en "Agregar Gasto Fijo" para cada uno**

✅ El sistema suma automáticamente todos los gastos fijos y actualiza tu presupuesto diario.

### Paso 3: Verificar Dashboard

**Dashboard → Verás 6 indicadores (KPIs)**

```
┌─────────────────────┬──────────────────┐
│ Ingreso Mensual     │    5,000,000 Gs  │
│ Ahorro              │    1,200,000 Gs  │
│ Gastos Fijos        │    1,230,000 Gs  │
│ Gastos Variables    │            0 Gs  │
│ Disponible          │    3,800,000 Gs  │
│ Disponible Real     │    2,570,000 Gs  │
└─────────────────────┴──────────────────┘
```

**Card Principal (Morada Grande) - Presupuesto Diario**

```
💰 Puedes gastar hoy:    91,666 Gs
📊 Ya gastaste:               0 Gs
✅ Te queda por hoy:     91,666 Gs
📅 Días restantes:          30 días
```

✅ **¡Listo! Ya tienes tu presupuesto diario calculado.**

---

## 📱 Uso Diario

### Cada Mañana
1. Abre MoneyFlow
2. Ve el Dashboard
3. Mira: **"Puedes gastar hoy: XX,XXX Gs"**
4. Ten esa cifra en mente durante el día

### Después de Cada Gasto

**Dashboard → Botón "+ Agregar Gasto"**

Completa el formulario:

```
📅 Fecha:            19/04/2026 (hoy)
📁 Categoría:        Comida
🏷️  Tipo:            Necesario
💵 Método de Pago:   Efectivo
💰 Monto:            25,000 Gs
📝 Descripción:      Almuerzo restaurante
```

**Click "Guardar"**

✅ El Dashboard se actualiza instantáneamente:
- "Ya gastaste: 25,000 Gs"
- "Te queda por hoy: 66,666 Gs"
- Barra de progreso verde/amarilla

### Revisar Tu Estado

**Dashboard → Sección "Resumen Semanal"**

```
📊 Presupuesto Semanal:     641,662 Gs
💸 Gastado en 7 días:       580,000 Gs
✅ Disponible:               61,662 Gs
📈 Porcentaje usado:            87%
✅ Vas bien
```

**Dashboard → Gráfico de Gastos por Categoría**

Visualiza en qué categorías gastas más:
- 🍴 Comida: 45%
- 🚗 Transporte: 25%
- 🎮 Entretenimiento: 15%
- 🏥 Salud: 10%
- 📦 Otros: 5%

---

## 🔄 Reinicio de Periodo Mensual

### ¿Cómo funciona el periodo mensual?

El sistema trabaja con **periodos mensuales personalizables**:
- **Día de inicio**: Configura el día que prefieras (ej: día 1, 15, 25 del mes)
- **Día de fin**: Automáticamente se calcula según tu periodo
- **Flexible**: Ajústalo según tu día de cobro o preferencia personal

### ¿Qué pasa al finalizar un periodo?

**1. Alerta Automática en Dashboard**

```
🎉 Nuevo periodo disponible
El periodo actual ha finalizado. Configura el nuevo periodo.
   [Ir a Configuración]
```

**2. Configuración Automática**

- Click en "Ir a Configuración"
- Verás un botón grande: **"Configurar Automáticamente"**
- Click en el botón
- El sistema sugiere las fechas del siguiente periodo mensual
- Confirmas y ¡listo!

**3. Nuevo Periodo Iniciado**

✅ Gastos variables se reinician
✅ Presupuesto diario se recalcula
✅ Dashboard muestra nuevo periodo
✅ Gastos fijos se mantienen (son mensuales)

### Configuración Manual (Si prefieres)

**Dashboard → Configuración**

Cambia las fechas manualmente según tu preferencia:
- Fecha Inicio: Ejemplo: 01/05/2026 (o el día que prefieras)
- Fecha Fin: Ejemplo: 31/05/2026 (o según tu periodo)

**Click "Actualizar Configuración"**

---

## 📊 Gestión de Gastos

### Ver Todos los Gastos

**Dashboard → Menú lateral → Gastos**

Lista completa de gastos del periodo actual con:
- Fecha
- Categoría
- Tipo
- Método de pago
- Monto
- Descripción
- Acciones (eliminar)

### Eliminar un Gasto (Si te equivocaste)

1. Ve a **Gastos**
2. Localiza el gasto erróneo
3. Click en botón rojo "Eliminar"
4. Confirma la eliminación
5. ✅ Dashboard se actualiza automáticamente

### Editar Gastos Fijos

**Dashboard → Gastos Fijos**

- **Editar**: Click en botón azul "Editar"
- **Eliminar**: Click en botón rojo "Eliminar" (con confirmación)
- **Activar/Desactivar**: Solo gastos activos suman al total

---

## 💡 Consejos de Uso

### Para Control Diario Efectivo

✅ **DO (Hacer)**
- Registra gastos inmediatamente después de hacerlos
- Revisa el Dashboard cada mañana
- Respeta tu presupuesto diario
- Si te pasas un día, compensa al día siguiente
- Usa las alertas como guía

❌ **DON'T (No hacer)**
- No esperes al final del día para registrar todo
- No ignores las alertas rojas
- No cambies el periodo sin razón
- No elimines gastos reales para "verse mejor"

### Interpretando las Alertas

**🟢 Verde - "Vas bien"**
```
Gastaste menos del presupuesto diario → Excelente control
```

**🟡 Amarilla - "Te pasaste"**
```
Gastaste más del presupuesto diario → Ajusta próximos días
```

**🔴 Roja - "Muy por encima"**
```
Gastaste mucho más del presupuesto → Cuidado con el resto del mes
```

### Optimizando tu Presupuesto

**Si siempre te queda dinero al final del mes:**
- Aumenta tu meta de ahorro
- Considera invertir el excedente

**Si siempre te falta dinero al final del mes:**
- Revisa gastos fijos (¿puedes reducir alguno?)
- Analiza el gráfico de categorías (¿dónde gastas más?)
- Reduce gastos "opcionales"
- Ajusta tu ingreso si cambió
- Reduce la meta de ahorro temporalmente (último recurso)

---

## 📊 Categorías de Gastos

### Comida 🍴
- Almuerzo, cena, desayuno
- Supermercado
- Restaurantes, delivery

### Transporte 🚗
- Taxi, Uber, Bolt
- Combustible
- Estacionamiento
- Mantenimiento vehículo

### Salud 🏥
- Medicamentos
- Consultas médicas
- Odontólogo
- Seguro de salud

### Entretenimiento 🎮
- Cine, teatro
- Salidas nocturnas
- Hobbies
- Eventos, conciertos

### Servicios 📦
- Reparaciones
- Peluquería, barbería
- Limpieza, lavandería
- Envíos, courier

### Otros 📌
- Regalos
- Donaciones
- Emergencias
- Gastos varios

---

## 🔐 Seguridad y Datos

### Cambiar Contraseña

1. Dashboard → Menú superior → Tu nombre
2. Click "Cambiar Contraseña"
3. Ingresa contraseña actual
4. Ingresa nueva contraseña (mínimo 8 caracteres)
5. Confirma nueva contraseña
6. Click "Guardar"

### Cerrar Sesión

**Dashboard → Menú superior → "Cerrar Sesión"**

⚠️ Siempre cierra sesión si usas una computadora compartida.

### Respaldo de Datos

Tu información está en la base de datos MySQL. Para respaldo:

```bash
# Exportar datos (desde terminal)
mysqldump -u root -p moneyflaw > backup_moneyflow_$(date +%Y%m%d).sql

# O usa phpMyAdmin → Exportar
```

---

## 📱 Uso Móvil

### Diseño Responsive

✅ MoneyFlow funciona perfectamente en:
- 📱 iPhone, Android
- 💻 Tablets (iPad, etc.)
- 🖥️ Desktop (PC, Mac)

### Acceso Móvil

1. Abre tu navegador móvil (Chrome, Safari)
2. Ingresa: `http://tu-dominio.com/moneyflow`
3. Inicia sesión
4. Las cards se reorganizan automáticamente para pantallas pequeñas

### Agrega a Pantalla de Inicio (Opcional)

**iPhone/iPad:**
1. Safari → Botón compartir
2. "Agregar a pantalla de inicio"
3. Icono en tu home screen

**Android:**
1. Chrome → Menú (3 puntos)
2. "Agregar a pantalla de inicio"
3. Icono en tu home screen

---

## ❓ Preguntas Frecuentes (FAQ)

### ¿Por qué mi presupuesto diario es negativo?

**Respuesta:** Tus gastos fijos + ahorro superan tu ingreso mensual.

**Solución:**
- Reduce tu meta de ahorro
- Reduce o elimina gastos fijos innecesarios
- Aumenta tu ingreso mensual

### ¿Puedo personalizar el día de inicio del periodo?

**Respuesta:** Sí, totalmente flexible. Cambia las fechas en Configuración según tu preferencia (ej: del 1 al 30, del 15 al 14, etc.). Configúralo según tu día de cobro o como prefieras organizar tus finanzas.

### ¿Los gastos fijos se reinician cada mes?

**Respuesta:** No, los gastos fijos son permanentes. Solo se restan del cálculo del presupuesto diario. Los gastos variables sí se reinician cada periodo.

### ¿Puedo tener varios usuarios?

**Respuesta:** Sí, el sistema soporta múltiples usuarios. Cada usuario tiene sus propios datos aislados.

### ¿Qué pasa si olvido registrar un gasto?

**Respuesta:** Puedes agregarlo después con la fecha correcta. El sistema calculará correctamente el "gastado hoy" según la fecha que ingreses.

### ¿Puedo exportar mis datos a Excel?

**Respuesta:** Actualmente no hay exportación automática. Pero puedes exportar desde phpMyAdmin → tabla `gastos` → Exportar → CSV/Excel.

### ¿El sistema envía recordatorios?

**Respuesta:** No, actualmente no hay notificaciones automáticas. Debes ingresar manualmente para revisar tu presupuesto.

---

## 🆘 Solución de Problemas

### "No puedo iniciar sesión"

**Causas comunes:**
- Contraseña incorrecta → Usa: `admin123`
- Usuario incorrecto → Usa: `admin`
- Base de datos no importada → Ve a [INSTALL.md](INSTALL.md)

### "No veo gastos en el Dashboard"

**Causas comunes:**
- No has agregado gastos aún
- El periodo configurado no incluye hoy
- Revisa fechas en Configuración

### "El presupuesto diario no se actualiza"

**Solución:**
1. Ve a Configuración
2. Click "Actualizar Configuración" (sin cambiar nada)
3. Regresa al Dashboard
4. Refresca la página (F5)

### "Error de conexión a la base de datos"

**Solución:**
1. Verifica que MySQL esté corriendo
2. Revisa credenciales en `config/database.php`
3. Verifica que la base de datos `moneyflaw` existe
4. Ve [INSTALL.md](INSTALL.md) para reinstalar

---

## 🎯 Flujo de Trabajo Ideal

### Inicio de Periodo (Día de Cobro)

```
08:00 → Abrir MoneyFlow
08:05 → Ver alerta "Nuevo periodo disponible"
08:10 → Ir a Configuración → "Configurar Automáticamente"
08:15 → Dashboard muestra nuevo presupuesto diario: 91,666 Gs
08:20 → Listo para usar el periodo
```

### Días del Periodo (Uso Regular)

```
[Mañana]
08:00 → Abrir Dashboard
        "Puedes gastar hoy: 91,666 Gs" → Tener en mente

[Durante el Día]
12:30 → Almorzar (35,000 Gs) → Registrar inmediatamente
14:45 → Comprar algo (15,000 Gs) → Registrar inmediatamente
19:00 → Cena (25,000 Gs) → Registrar inmediatamente

[Noche]
21:00 → Revisar Dashboard
        "Ya gastaste: 75,000 Gs"
        "Te queda: 16,666 Gs"
        Barra: 82% (verde) ✅
        
21:05 → Planificar mañana según resultado
```

### Fin de Periodo (Reinicio Mensual)

```
[Repetir ciclo]
- Configurar nuevo periodo mensual
- Revisar periodo anterior (opcional: exportar datos)
- Ajustar estrategia si es necesario
```

---

## 📞 Soporte

### Documentación Adicional

- **[README.md](README.md)** - Información general del proyecto
- **[INSTALL.md](INSTALL.md)** - Guía de instalación técnica detallada
- **[DEPLOY.md](DEPLOY.md)** - Cómo subir a servidor de producción

### Reportar Problemas

Si encuentras errores o necesitas ayuda:
1. Revisa esta guía completa
2. Revisa la documentación técnica
3. Contacta al desarrollador o crea un issue en GitHub

---

## 🎉 ¡Listo para Usar!

Ahora que conoces todas las funciones de MoneyFlow, estás listo para:

✅ Controlar tus gastos diarios con precisión
✅ Alcanzar tus metas de ahorro
✅ Visualizar tus patrones de gasto
✅ Tomar decisiones financieras informadas

**💰 ¡Éxito con tu control financiero! 🚀**
