# 🔗 INTEGRACIÓN CON N8N - MoneyFlow

Esta guía te muestra cómo integrar MoneyFlow con n8n para automatizar notificaciones y registro de gastos.

---

## 📋 TABLA DE CONTENIDO

1. [Configuración Inicial](#configuración-inicial)
2. [Workflow 1: Alertas Automáticas](#workflow-1-alertas-automáticas)
3. [Workflow 2: Registro desde Email](#workflow-2-registro-desde-email)
4. [Workflow 3: Reporte Diario](#workflow-3-reporte-diario)

---

## 🛠️ CONFIGURACIÓN INICIAL

### Paso 1: Instalar n8n

```bash
# Opción 1: Docker (Recomendado)
docker run -it --rm \
  --name n8n \
  -p 5678:5678 \
  -v ~/.n8n:/home/node/.n8n \
  n8nio/n8n

# Opción 2: npm
npm install n8n -g
n8n start
```

### Paso 2: Configurar Credentials

En n8n, configura las siguientes credenciales:

1. **WhatsApp (via Twilio o WhatsApp Business API)**
2. **Telegram Bot** (obtén token de @BotFather)
3. **Gmail API** (para leer correos bancarios)

---

## 🚨 WORKFLOW 1: ALERTAS AUTOMÁTICAS

Este workflow verifica cada hora si hay alertas financieras y envía notificaciones.

### Nodos del Workflow

```
[Cron] → [HTTP Request] → [IF] → [WhatsApp/Telegram]
```

### Configuración Detallada

#### 1. Nodo Cron (Schedule Trigger)
```json
{
  "mode": "everyHour",
  "hour": "*",
  "minute": "0"
}
```

#### 2. Nodo HTTP Request
```json
{
  "url": "https://tudominio.com/api/status.php",
  "method": "GET",
  "responseFormat": "json"
}
```

#### 3. Nodo IF (Conditional)
```json
{
  "conditions": {
    "boolean": [
      {
        "value1": "={{$json.data.alerta}}",
        "operation": "equal",
        "value2": true
      }
    ]
  }
}
```

#### 4A. Nodo WhatsApp (Rama TRUE)
```json
{
  "operation": "send",
  "recipient": "+595981234567",
  "message": "={{$json.data.mensaje}}\n\nSaldo: {{$json.data.saldo_actual}} Gs\nEstado: {{$json.data.estado}}"
}
```

#### 4B. Nodo Telegram (Alternativa)
```json
{
  "operation": "sendMessage",
  "chatId": "TU_CHAT_ID",
  "text": "🚨 *MoneyFlow - Alerta Financiera*\n\n{{$json.data.mensaje}}\n\n💰 *Saldo Actual:* {{$json.data.saldo_actual}} Gs\n📊 *Estado:* {{$json.data.estado}}\n📈 *Ahorro:* {{$json.data.ahorro_actual}} Gs",
  "parseMode": "Markdown"
}
```

### JSON Completo del Workflow (Importar en n8n)

```json
{
  "name": "MoneyFlow - Alertas Automáticas",
  "nodes": [
    {
      "parameters": {
        "rule": {
          "interval": [
            {
              "field": "hours",
              "hoursInterval": 1
            }
          ]
        }
      },
      "name": "Verificar cada hora",
      "type": "n8n-nodes-base.scheduleTrigger",
      "position": [250, 300],
      "typeVersion": 1
    },
    {
      "parameters": {
        "url": "https://tudominio.com/api/status.php",
        "options": {}
      },
      "name": "Obtener Estado Financiero",
      "type": "n8n-nodes-base.httpRequest",
      "position": [450, 300],
      "typeVersion": 3
    },
    {
      "parameters": {
        "conditions": {
          "boolean": [
            {
              "value1": "={{$json.data.alerta}}",
              "value2": true
            }
          ]
        }
      },
      "name": "¿Hay Alerta?",
      "type": "n8n-nodes-base.if",
      "position": [650, 300],
      "typeVersion": 1
    },
    {
      "parameters": {
        "chatId": "TU_CHAT_ID",
        "text": "=🚨 *MoneyFlow - Alerta Financiera*\n\n{{$json.data.mensaje}}\n\n💰 *Saldo:* {{$json.data.saldo_actual}} Gs\n📊 *Estado:* {{$json.data.estado}}",
        "additionalFields": {
          "parseMode": "Markdown"
        }
      },
      "name": "Enviar a Telegram",
      "type": "n8n-nodes-base.telegram",
      "position": [850, 200],
      "typeVersion": 1,
      "credentials": {
        "telegramApi": "Telegram"
      }
    }
  ],
  "connections": {
    "Verificar cada hora": {
      "main": [
        [
          {
            "node": "Obtener Estado Financiero",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Obtener Estado Financiero": {
      "main": [
        [
          {
            "node": "¿Hay Alerta?",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "¿Hay Alerta?": {
      "main": [
        [
          {
            "node": "Enviar a Telegram",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  }
}
```

---

## 📧 WORKFLOW 2: REGISTRO DESDE EMAIL

Registra automáticamente gastos cuando recibes emails de tu banco.

### Nodos del Workflow

```
[Gmail Trigger] → [Extraer Datos] → [HTTP Request POST] → [Notificación]
```

### Configuración

#### 1. Nodo Gmail Trigger
```json
{
  "pollTimes": {
    "item": [
      {
        "mode": "everyMinute",
        "minute": 5
      }
    ]
  },
  "filters": {
    "from": "notificaciones@banco.com.py",
    "subject": "Compra realizada"
  }
}
```

#### 2. Nodo Function (Extraer Monto)
```javascript
// Extraer monto del email
const emailBody = $input.item.json.textPlain;

// Buscar patrón: "monto: 150.000 Gs"
const montoMatch = emailBody.match(/monto:?\s*([\d.,]+)\s*Gs/i);

if (!montoMatch) {
  // Si no encuentra monto, preguntar al usuario
  return [{
    json: {
      necesita_confirmacion: true,
      email: emailBody
    }
  }];
}

const monto = parseFloat(montoMatch[1].replace(/\./g, '').replace(',', '.'));

// Determinar categoría según palabras clave
let categoria = 'otros';
if (emailBody.toLowerCase().includes('supermercado')) categoria = 'supermercado';
if (emailBody.toLowerCase().includes('combustible')) categoria = 'transporte';
if (emailBody.toLowerCase().includes('ande')) categoria = 'electricidad';

return [{
  json: {
    fecha: new Date().toISOString().split('T')[0],
    tipo: 'variable',
    categoria: categoria,
    descripcion: 'Gasto registrado desde email bancario',
    monto: monto,
    metodo: 'efectivo',
    necesita_confirmacion: false
  }
}];
```

#### 3. Nodo IF (¿Necesita Confirmación?)
```json
{
  "conditions": {
    "boolean": [
      {
        "value1": "={{$json.necesita_confirmacion}}",
        "operation": "equal",
        "value2": false
      }
    ]
  }
}
```

#### 4A. Nodo HTTP Request (Rama FALSE - Auto-registrar)
```json
{
  "url": "https://tudominio.com/api/add_expense.php",
  "method": "POST",
  "bodyParametersJson": "={{JSON.stringify($json)}}",
  "options": {
    "headers": {
      "Content-Type": "application/json"
    }
  }
}
```

#### 4B. Nodo Telegram (Rama TRUE - Pedir confirmación)
```json
{
  "operation": "sendMessage",
  "chatId": "TU_CHAT_ID",
  "text": "📧 Recibí un email bancario pero no pude extraer el monto.\n\n¿Cuál es el monto del gasto?\n\nResponde con: /gasto MONTO",
  "parseMode": "Markdown"
}
```

---

## 📊 WORKFLOW 3: REPORTE DIARIO

Envía un resumen diario de tus finanzas.

### Configuración

#### 1. Nodo Cron (Trigger)
```json
{
  "triggerTimes": {
    "item": [
      {
        "mode": "everyDay",
        "hour": 20,
        "minute": 0
      }
    ]
  }
}
```

#### 2. Nodo HTTP Request
```json
{
  "url": "https://tudominio.com/api/status.php",
  "method": "GET"
}
```

#### 3. Nodo Telegram (Reporte)
```json
{
  "operation": "sendMessage",
  "chatId": "TU_CHAT_ID",
  "text": "=📊 *Reporte Diario - MoneyFlow*\n\n💰 *Saldo Actual:* {{$json.data.saldo_actual}} Gs\n💳 *Gourmet Disponible:* {{$json.data.gourmet_disponible}} Gs\n\n📉 *Gastos del Periodo:*\n• Efectivo: {{$json.data.gastos_efectivo}} Gs\n• Gourmet: {{$json.data.gastos_gourmet}} Gs\n• Total: {{$json.data.gastos_totales}} Gs\n\n🎯 *Ahorro:* {{$json.data.ahorro_actual}} Gs ({{$json.data.porcentaje_ahorro}}%)\n\n{{$json.data.analisis_ritmo.mensaje}}",
  "additionalFields": {
    "parseMode": "Markdown"
  }
}
```

---

## 🤖 WORKFLOW 4: BOT DE TELEGRAM INTERACTIVO

Registra gastos conversando con un bot.

### Configuración del Bot

#### 1. Nodo Telegram Trigger
```json
{
  "updates": ["message"]
}
```

#### 2. Nodo Function (Parsear Comando)
```javascript
const message = $input.item.json.message.text;
const chatId = $input.item.json.message.chat.id;

// Comando: /gasto 50000 supermercado Compra semanal
const comandoMatch = message.match(/^\/gasto\s+(\d+)\s+(\w+)\s+(.+)$/i);

if (!comandoMatch) {
  return [{
    json: {
      chatId: chatId,
      respuesta: "❌ Formato incorrecto.\n\nUsa: /gasto MONTO CATEGORIA DESCRIPCION\n\nEjemplo: /gasto 50000 supermercado Compra semanal"
    }
  }];
}

const [_, monto, categoria, descripcion] = comandoMatch;

// Validar categoría
const categoriasValidas = ['electricidad', 'transporte', 'supermercado', 'servicios', 'otros'];
if (!categoriasValidas.includes(categoria.toLowerCase())) {
  return [{
    json: {
      chatId: chatId,
      respuesta: `❌ Categoría inválida.\n\nCategorías válidas: ${categoriasValidas.join(', ')}`
    }
  }];
}

return [{
  json: {
    chatId: chatId,
    gasto: {
      fecha: new Date().toISOString().split('T')[0],
      tipo: 'variable',
      categoria: categoria.toLowerCase(),
      descripcion: descripcion,
      monto: parseFloat(monto),
      metodo: 'efectivo'
    }
  }
}];
```

#### 3. Nodo HTTP Request (Registrar)
```json
{
  "url": "https://tudominio.com/api/add_expense.php",
  "method": "POST",
  "bodyParametersJson": "={{JSON.stringify($json.gasto)}}",
  "options": {
    "headers": {
      "Content-Type": "application/json"
    }
  }
}
```

#### 4. Nodo Telegram (Confirmación)
```json
{
  "operation": "sendMessage",
  "chatId": "={{$json.chatId}}",
  "text": "✅ Gasto registrado exitosamente!\n\n💰 Monto: {{$json.gasto.monto}} Gs\n📁 Categoría: {{$json.gasto.categoria}}\n📝 Descripción: {{$json.gasto.descripcion}}",
  "parseMode": "Markdown"
}
```

---

## 🔐 SEGURIDAD

### Proteger tu API

Agrega autenticación básica a tus endpoints:

```php
// Agregar al inicio de api/status.php y api/add_expense.php

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($apiKey !== 'TU_CLAVE_SECRETA_AQUI') {
    enviarJSON([
        'success' => false,
        'error' => 'API Key inválida'
    ], 401);
}
```

En n8n, agrega header:
```json
{
  "headers": {
    "X-API-KEY": "TU_CLAVE_SECRETA_AQUI"
  }
}
```

---

## 📱 COMANDOS DEL BOT DE TELEGRAM

- `/gasto MONTO CATEGORIA DESCRIPCION` - Registrar gasto
- `/saldo` - Ver saldo actual
- `/reporte` - Ver reporte completo
- `/ayuda` - Ver todos los comandos

---

## 🎯 PRÓXIMOS PASOS

1. Personaliza los horarios de notificaciones
2. Agrega más reglas de detección en emails
3. Crea webhooks para integrar con otras apps
4. Implementa recordatorios de facturas pendientes

---

## 📞 SOPORTE

Si tienes problemas con la integración:
1. Verifica que la API responda correctamente: `curl https://tudominio.com/api/status.php`
2. Revisa los logs de n8n
3. Testea cada nodo individualmente

---

**¡Tu MoneyFlow ahora está completamente automatizado! 🚀**
