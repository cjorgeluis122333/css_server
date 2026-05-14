# Recuperación de Contraseña — Flujo Directo (sin servicio externo)

> Este flujo **no requiere correo electrónico ni código OTP**. La identidad del socio se verifica presentando tres datos que solo él conoce: número de acción, cédula y correo registrado.

---

## Descripción General

El flujo consta de **dos pasos**:

| Paso | Endpoint | Acción |
|------|----------|--------|
| 1 | `POST /forgot-password/direct/validate` | Verificar identidad → recibir token temporal |
| 2 | `POST /forgot-password/direct/reset` | Usar el token para establecer la nueva contraseña |

Ambos endpoints son **públicos** (no requieren `Authorization` header ni autenticación Sanctum).

El token generado en el Paso 1 tiene una vigencia de **10 minutos**. Si expira, el usuario debe iniciar el proceso desde el Paso 1 nuevamente.

---

## Paso 1 — Verificar Identidad

### Request

```
POST /api/forgot-password/direct/validate
Content-Type: application/json
```

**Body:**

```json
{
    "acc": 850,
    "cedula": 12345678,
    "correo": "socio@correo.com"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `acc` | `integer` | Sí | Número de acción del socio |
| `cedula` | `integer` | Sí | Número de cédula del socio |
| `correo` | `string (email)` | Sí | Correo electrónico registrado en el sistema |

> Los tres campos deben coincidir **exactamente** con el registro del usuario. Si alguno no coincide, la validación falla con `404`.

### Respuesta exitosa — `200 OK`

```json
{
    "status": "success",
    "message": "Identidad verificada. Usa el token para establecer tu nueva contraseña.",
    "data": {
        "token": "a3f8c2d1e4b7..."
    }
}
```

El campo `token` debe almacenarse en el estado local del frontend (memoria, no localStorage) y enviarse en el Paso 2.

### Errores posibles

| HTTP | Causa |
|------|-------|
| `422` | Campos faltantes o con formato incorrecto (ver `errors` en la respuesta) |
| `404` | No existe ninguna cuenta que coincida con los tres datos proporcionados |
| `500` | Error interno del servidor |

**Ejemplo de error 422:**

```json
{
    "status": "error",
    "message": "Errores de validación",
    "errors": {
        "correo": ["El correo electrónico no tiene un formato válido."]
    }
}
```

**Ejemplo de error 404:**

```json
{
    "status": "error",
    "message": "No existe una cuenta registrada para los datos proporcionados.",
    "code": 404
}
```

---

## Paso 2 — Establecer Nueva Contraseña

### Request

```
POST /api/forgot-password/direct/reset
Content-Type: application/json
```

**Body:**

```json
{
    "acc": 850,
    "token": "a3f8c2d1e4b7...",
    "password": "nuevaPassword123",
    "password_confirmation": "nuevaPassword123"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `acc` | `integer` | Sí | Número de acción del socio (mismo del Paso 1) |
| `token` | `string` | Sí | Token recibido en la respuesta del Paso 1 |
| `password` | `string` | Sí | Nueva contraseña (mínimo 6 caracteres) |
| `password_confirmation` | `string` | Sí | Debe ser idéntico a `password` |

### Respuesta exitosa — `200 OK`

```json
{
    "status": "success",
    "message": "Contraseña actualizada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.",
    "data": null
}
```

Redirigir al usuario a la pantalla de login.

### Errores posibles

| HTTP | Causa |
|------|-------|
| `422` | Campos inválidos, contraseñas no coinciden, o token expirado/inválido |
| `404` | El usuario ya no existe en el sistema |
| `500` | Error interno del servidor |

**Ejemplo de error por token inválido o expirado:**

```json
{
    "status": "error",
    "message": "El token es inválido o ha expirado. Inicia el proceso nuevamente.",
    "code": 422
}
```

**Ejemplo de error por contraseñas que no coinciden:**

```json
{
    "status": "error",
    "message": "Errores de validación",
    "errors": {
        "password": ["La confirmación de la contraseña no coincide."]
    }
}
```

---

## Flujo Completo — Ejemplo con Axios (React)

```js
// services/auth.js

const BASE_URL = '/api/forgot-password/direct';

/**
 * Paso 1: Verificar identidad del socio.
 * @returns {Promise<string>} token temporal
 */
export async function directValidate(acc, cedula, correo) {
    const { data } = await axios.post(`${BASE_URL}/validate`, {
        acc,
        cedula,
        correo,
    });
    return data.data.token;
}

/**
 * Paso 2: Establecer nueva contraseña.
 */
export async function directReset(acc, token, password, passwordConfirmation) {
    await axios.post(`${BASE_URL}/reset`, {
        acc,
        token,
        password,
        password_confirmation: passwordConfirmation,
    });
}
```

```jsx
// components/ForgotPasswordDirect.jsx

import { useState } from 'react';
import { directValidate, directReset } from '../services/auth';

export default function ForgotPasswordDirect() {
    const [step, setStep]   = useState(1);
    const [token, setToken] = useState('');
    const [acc, setAcc]     = useState('');
    const [error, setError] = useState(null);

    async function handleValidate(e) {
        e.preventDefault();
        setError(null);
        const { cedula, correo } = Object.fromEntries(new FormData(e.target));
        try {
            const t = await directValidate(Number(acc), Number(cedula), correo);
            setToken(t);
            setStep(2);
        } catch (err) {
            setError(err.response?.data?.message ?? 'Error al verificar identidad.');
        }
    }

    async function handleReset(e) {
        e.preventDefault();
        setError(null);
        const { password, password_confirmation } = Object.fromEntries(new FormData(e.target));
        try {
            await directReset(Number(acc), token, password, password_confirmation);
            // Redirigir al login
        } catch (err) {
            setError(err.response?.data?.message ?? 'Error al cambiar la contraseña.');
        }
    }

    if (step === 1) {
        return (
            <form onSubmit={handleValidate}>
                <input name="acc" type="number" placeholder="Número de acción"
                    onChange={e => setAcc(e.target.value)} required />
                <input name="cedula" type="number" placeholder="Cédula" required />
                <input name="correo" type="email" placeholder="Correo registrado" required />
                {error && <p className="error">{error}</p>}
                <button type="submit">Verificar identidad</button>
            </form>
        );
    }

    return (
        <form onSubmit={handleReset}>
            <input name="password" type="password"
                placeholder="Nueva contraseña (mín. 6 caracteres)" required minLength={6} />
            <input name="password_confirmation" type="password"
                placeholder="Confirmar contraseña" required />
            {error && <p className="error">{error}</p>}
            <button type="submit">Cambiar contraseña</button>
        </form>
    );
}
```

---

## Notas de Seguridad

- El `token` **no debe persistirse** en `localStorage` ni `sessionStorage`. Guardarlo solo en el estado de React (memoria) para que se descarte al cerrar la pestaña.
- El token expira en **10 minutos**. Si el usuario tarda más, debe reiniciar el flujo desde el Paso 1.
- El servidor valida el token usando `hash_equals` para prevenir ataques de timing.
- Una vez usado el token en el Paso 2 (exitosamente o no por token inválido), queda invalidado.
