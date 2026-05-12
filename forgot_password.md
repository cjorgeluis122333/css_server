# Flujo de Recuperación de Contraseña — Guía para el Frontend

---

## Visión general del flujo

```
[Frontend]                          [Backend]
    │                                   │
    │── POST /forgot-password/request ──▶│ Valida acc+cédula, genera OTP,
    │◀── 200: correo ofuscado ──────────│ guarda en caché 2 min, envía email
    │                                   │
    │  [Usuario revisa su email]         │
    │                                   │
    │── POST /forgot-password/verify ───▶│ Compara OTP con caché,
    │◀── 200: código verificado ────────│ marca verificado por 10 min
    │                                   │
    │── POST /forgot-password/reset ────▶│ Comprueba flag de verificación,
    │◀── 200: contraseña actualizada ───│ actualiza password en BD
```

El flujo tiene **3 pantallas** (o pasos dentro de una sola pantalla multi-step). Todas las peticiones son públicas — no requieren token de autenticación.

---

## Pantalla 1 — Solicitar código OTP

### Qué muestra
- Campo: **Número de acción** (`acc`) — input numérico
- Campo: **Cédula** (`cedula`) — input numérico
- Botón: "Enviar código"

### Petición al backend

```
POST /api/forgot-password/request
Content-Type: application/json
```

```json
{
    "acc": 150,
    "cedula": 12345678
}
```

### Respuesta exitosa `200`

```json
{
    "status": "success",
    "message": "Se ha enviado un código de verificación al correo registrado. Válido por 2 minutos.",
    "data": {
        "correo": "ju***@gmail.com"
    }
}
```

**Acción del frontend:** guardar el `acc` en el estado local (lo necesita en el paso 2 y 3), mostrar `data.correo` al usuario y navegar a la Pantalla 2.

### Errores a manejar

| HTTP | `message` del backend | Qué mostrar al usuario |
|---|---|---|
| `422` | Errores de validación | Mostrar los errores de campo (`errors`) |
| `404` | No existe una cuenta registrada para los datos proporcionados. | "Los datos ingresados no corresponden a ninguna cuenta. ¿Todavía no tienes cuenta? [Regístrate]" |

> **Nota UX:** Si llega un 404, redirigir al usuario al registro (`/register`), no reintentar este flujo.

---

## Pantalla 2 — Ingresar código OTP

### Qué muestra
- Texto informativo: "Ingresa el código de 6 dígitos que enviamos a `{correo}`"
- Campo: **Código** — input numérico de 6 caracteres (o 6 inputs individuales tipo OTP)
- Botón: "Verificar código"
- Enlace: "No recibí el código — reenviar" (llama nuevamente a la Pantalla 1 / endpoint request)
- Contador visual de los **2 minutos** de vigencia (opcional pero recomendado)

### Petición al backend

```
POST /api/forgot-password/verify
Content-Type: application/json
```

```json
{
    "acc": 150,
    "code": "483921"
}
```

El `acc` viene del estado guardado en el paso anterior.

### Respuesta exitosa `200`

```json
{
    "status": "success",
    "message": "Código verificado correctamente. Ahora puedes establecer tu nueva contraseña.",
    "data": null
}
```

**Acción del frontend:** navegar a la Pantalla 3. El `acc` sigue guardado en el estado.

### Errores a manejar

| HTTP | `message` del backend | Qué mostrar al usuario |
|---|---|---|
| `422` (campo) | Errores de validación | "El código debe tener exactamente 6 dígitos" |
| `422` (expirado) | El código ha expirado. Por favor solicita uno nuevo. | "Tu código venció. Pulsa 'reenviar' para obtener uno nuevo." → volver a Pantalla 1 |
| `422` (incorrecto) | El código ingresado no es válido. | "Código incorrecto. Verifica el email e inténtalo de nuevo." |

> **Nota:** El OTP es de **un solo uso**. Una vez verificado correctamente queda destruido; un segundo intento con el mismo código devolverá "código expirado".

---

## Pantalla 3 — Nueva contraseña

### Qué muestra
- Campo: **Nueva contraseña** (`password`) — input tipo password, mínimo 6 caracteres
- Campo: **Confirmar contraseña** (`password_confirmation`) — input tipo password
- Botón: "Guardar nueva contraseña"

### Petición al backend

```
POST /api/forgot-password/reset
Content-Type: application/json
```

```json
{
    "acc": 150,
    "password": "miNuevaContra123",
    "password_confirmation": "miNuevaContra123"
}
```

### Respuesta exitosa `200`

```json
{
    "status": "success",
    "message": "Contraseña actualizada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.",
    "data": null
}
```

**Acción del frontend:** limpiar el estado del flujo (acc, correo) y redirigir al usuario al login (`/login`) con un mensaje de éxito.

### Errores a manejar

| HTTP | `message` del backend | Qué mostrar al usuario |
|---|---|---|
| `422` (campo) | Errores de validación | "Las contraseñas no coinciden" / "Mínimo 6 caracteres" |
| `422` (verificación expirada) | La verificación del código ha expirado o no se completó... | "Tu sesión de recuperación venció. Debes iniciar el proceso nuevamente." → volver a Pantalla 1 |

> **Nota:** El frontend tiene una ventana de **10 minutos** desde que se verificó el código en el Paso 2 para completar este paso. Si el usuario tarda más, el backend rechazará la petición.

---

## Estado del frontend a mantener durante el flujo

```js
// Estado mínimo necesario entre los 3 pasos
{
  acc: null,       // int — se guarda en Paso 1 y se reutiliza en Pasos 2 y 3
  correo: null,    // string ofuscado — se guarda en Paso 1 para mostrar en Paso 2
  step: 1,         // 1 | 2 | 3 — controla qué pantalla/componente mostrar
}
```

---

## Reglas de negocio clave

1. El usuario **debe tener una cuenta registrada** (haber hecho el registro previamente). Si no la tiene, el Paso 1 devuelve 404 y hay que redirigir al registro.
2. El código OTP es válido **2 minutos** y de **un solo uso**.
3. El estado "verificado" (permiso para hacer el Paso 3) dura **10 minutos**.
4. Si cualquier paso falla por expiración, el usuario debe **reiniciar desde el Paso 1**.
5. El `acc` es el identificador que une los 3 pasos — nunca debe perderse del estado mientras el flujo está activo.

---

## Configuración de correo en producción

Durante desarrollo, el email no se envía físicamente — el código aparece en `storage/logs/laravel.log` del servidor. Para producción se deben configurar las siguientes variables en el `.env` del backend:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.tuproveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu@correo.com
MAIL_PASSWORD=tupassword
MAIL_FROM_ADDRESS=noreply@ccv.com
MAIL_FROM_NAME="Club Campestre de Villavicencio"
```
