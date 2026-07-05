# 🤖 Project Context & Agent Rules

> **Última actualización:** 5 de julio de 2026
> Este archivo es la guía definitiva para cualquier agente IA que trabaje en este repositorio. Léelo **completo** antes de escribir una sola línea de código.

---

## 📌 Visión General

**CCV Backend** es la API REST de un sistema de gestión para un club social. Gestiona:

- **Socios** (titulares y familiares) con estructura jerárquica por número de acción (`acc`).
- **Cuotas mensuales** y cálculo de deuda/morosidad.
- **Historial de pagos** con procesamiento de abonos y cuotas adelantadas.
- **Invitados** con validación de límites (24 invitaciones/mes por socio, 4 visitas/mes por invitado).
- **Salones** (precios, reservas y control de ocupación).
- **Junta Directiva** con cargos anuales (presidente, vicepresidente, secretario, etc.).
- **Exportación a Excel** de reportes de deuda.
- **Administración de usuarios** con roles jerárquicos basados en número de acción.

El frontend es una aplicación **React** en un repositorio separado que consume esta API mediante **Laravel Sanctum**.

> ⚠️ **Este servidor es 100% API REST.** Nunca se retorna HTML ni vistas Blade como respuesta HTTP. Los controllers **siempre** retornan `JsonResponse`. Las vistas Blade solo existen en `resources/views/emails/` para el contenido HTML de correos electrónicos enviados vía Mailables — nunca para respuestas HTTP.

---

## 🛠 Stack Técnico

| Componente         | Tecnología                                        |
| ------------------ | ------------------------------------------------- |
| **Framework**      | Laravel 12.x                                      |
| **PHP**            | ^8.2                                              |
| **Base de Datos**  | MySQL 8.0.28 (Docker) / SQLite (dev local)        |
| **Autenticación**  | Laravel Sanctum 4.0 (tokens + SPA CSRF)           |
| **Frontend**       | React (repo separado), Vite 7 + Tailwind CSS 4    |
| **Testing**        | Pest 3.8 + Pest Plugin Laravel 3.2                |
| **Exports**        | Maatwebsite/Excel 3.1                             |
| **Code Style**     | Laravel Pint 1.24                                 |
| **DevOps**         | Docker (php:8.2-apache), Docker Compose            |
| **Dev Tools**      | Laravel Sail 1.41, Laravel Boost 2.0, Pail 1.2.2  |
| **HTTP Client**    | Axios 1.11 (frontend)                              |

### Comandos Clave

```bash
# Desarrollo local
composer run setup       # Instalación completa (deps, .env, migrations, npm)
composer run dev         # Servidor Laravel + Queue + Vite concurrentes
composer run test        # Ejecutar tests con Pest

# Docker
docker-compose up -d     # Levantar app (puerto 8080) + MySQL (puerto 3307)
```

---

## 🏗 Arquitectura y Patrones

### Patrón Principal: Service-Oriented MVC

```
Routes (api.php)
    ↓
Controllers (thin — solo orquestación)
    ↓
Services (lógica de negocio)
    ↓
Models (Eloquent directo — sin Repository)
```

**No se utilizan:** Repositories, DTOs, Actions, Events/Listeners, Jobs.

### Patrones Implementados

| Patrón                        | Implementación                                                                 |
| ----------------------------- | ------------------------------------------------------------------------------ |
| **Service Layer**             | `app/Service/` — 12 services (partner) + 22 services (activity: 11 pagos + 11 clientes) con lógica de negocio |
| **Mailables**                 | `app/Mail/` — `PasswordResetMail` para OTP de recuperación de contraseña       |
| **FormRequest Validation**    | `app/Http/Requests/` — validación desacoplada de controllers                   |
| **API Response Trait**        | `app/Traits/ApiResponse.php` — formato estándar JSON                           |
| **API Resources**             | `app/Http/Resources/` — transformación de modelos + display condicional RBAC; `activity/client/` para los 11 resources de clientes de actividades |
| **LEFT JOIN Enrichment**      | `*PagoService::paginated()` y `*PagoService::filterByMes()` incluyen LEFT JOIN con tabla de clientes para retornar campo `nombre` (11 servicios de actividades) |
| **Backed Enums (PHP 8.1)**    | `app/Enum/` — `PartnerCategory`, `UserRole`, `DebtMetricType`                 |
| **Eloquent Scopes**           | Filtros reutilizables en modelos (`scopeHolders()`, `scopeCurrentMonth()`)     |
| **Constructor DI**            | Controllers inyectan Services; Services pueden inyectar otros Services         |
| **DB Transactions**           | `DB::transaction()` en operaciones de escritura críticas                       |
| **Global Exception Handling** | `bootstrap/app.php` maneja excepciones para rutas `api/*`                      |
| **Excel Exports**             | `FromArray` + `WithHeadings` + `WithStyles` + `ShouldAutoSize`                |
| **Gates (Authorization)**     | `AppServiceProvider::boot()` — 14 gates para control de acceso por módulo     |
| **Policies (Ownership)**      | `app/Policies/` — 4 policies para validación de propiedad de datos            |
| **Audit Trail**               | `performed_by` en tablas de operaciones, visible solo para SUPER_ADMIN         |

---

## 📁 Estructura de Directorios

```
app/
├── Enum/                  # PHP 8.1 Backed Enums (PartnerCategory, UserRole, DebtMetricType)
├── Exports/               # Clases de exportación Excel (Maatwebsite)
├── Http/
│   ├── Controllers/       # 16 controllers (thin, delegan a Services)
│   ├── Middleware/         # Middleware personalizado
│   ├── Requests/          # FormRequest validation classes
│   └── Resources/         # API Resource transformations (con display condicional RBAC)
├── Mail/                  # Mailables (PasswordResetMail)
├── Models/                # 9 modelos Eloquent
├── Policies/              # 4 Policies: Partner, HallControl, Guest, HistoryPay
├── Providers/             # Service Providers (AppServiceProvider)
├── Service/               # ⚠️ SINGULAR — 12 services partner + 22 services activity (11 pagos + 11 clientes)
└── Traits/                # Traits compartidos (ApiResponse)

bootstrap/
└── app.php                # Bootstrapping, rutas, middleware, exception handling global

config/                    # Configuración Laravel estándar + cors.php, sanctum.php
database/
├── migrations/            # Migraciones de esquema
└── seeders/               # Seeders (DatabaseSeeder)

routes/
├── api.php                # ✅ TODAS las rutas API viven aquí
├── web.php                # Vacío (proyecto API-only)
└── console.php            # Comandos de consola

tests/                     # Tests con Pest (Feature/ y Unit/)
```

> **Nota crítica:** El directorio de services es `app/Service/` (singular), NO `app/Services/`. Respetar esta convención al crear nuevos services.
> 
> **Estructura de servicios de actividades:** 
> - `app/Service/activity/payment/` — 11 servicios `*PagoService.php` con métodos `paginated()`, `filterByMes()`, y `create()`. Cada uno implementa LEFT JOIN con tabla de clientes equivalente para enriquecimiento de datos.
> - `app/Service/activity/client/` — 11 servicios `*ClienteService.php` para gestión de clientes por actividad.

---

## 📏 Reglas de Desarrollo (Core Rules)

### 1. Nomenclatura

| Elemento      | Convención          | Ejemplo                                      |
| ------------- | ------------------- | -------------------------------------------- |
| Controllers   | PascalCase singular | `PartnerController`, `HallControlController` |
| Models        | PascalCase singular | `Partner`, `HallControl`, `ManagerBoards`    |
| Services      | PascalCase singular | `PartnerService`, `PartnerDebtService`       |
| FormRequests  | PascalCase singular | `PartnerRequest`, `HallControlRequest`       |
| Resources     | PascalCase singular | `PartnerResource`, `ManagerBoardsResource`   |
| Enums         | PascalCase          | `PartnerCategory`, `UserRole`                |
| Métodos       | camelCase           | `getAdvanceQuotes()`, `titularDebtSummary()`  |
| Propiedades   | camelCase + tipo    | `protected PartnerService $partnerService`   |
| Tablas DB     | Legacy: `0cc_*` / Laravel estándar                                |

### 2. Tipado

- **No se usa `declare(strict_types=1)`** actualmente en el proyecto.
- **Tipado de retorno obligatorio** en métodos de controllers y services (ej: `: JsonResponse`, `: Collection`, `: array`).
- **Type hints en parámetros** de constructores y métodos públicos.
- **Casts en modelos** para tipo seguro: `integer`, `decimal:2`, `date:Y-m-d`, Enums, `array`, `hashed`.

### 3. Validación

- **SIEMPRE** usar `FormRequest` classes. **NUNCA** validar inline en controllers.
- `authorize()` retorna `true` — permisos delegados al middleware `auth:sanctum`.
- Lógica de validación compleja va en `withValidator()` (ej: `HallControlRequest`, `HistoryPayRequest`).
- Mensajes de error personalizados en español vía el método `messages()`.
- Para reglas únicas con update: usar `Rule::unique()->ignore()` con detección de Route Model Binding.

### 4. Respuestas API

**Todo controller debe usar el trait `ApiResponse`:**

```php
use App\Traits\ApiResponse;

class MiController extends Controller
{
    use ApiResponse;
}
```

**Formato estándar de éxito:**
```json
{
    "status": "success",
    "message": "Descripción opcional",
    "data": { }
}
```

**Formato estándar de error:**
```json
{
    "status": "error",
    "message": "Descripción del error",
    "code": 400
}
```

**Métodos disponibles:**
- `$this->successResponse($data, $message = null, $code = 200)`
- `$this->errorResponse($message, $code)`

### 5. Manejo de Excepciones

| Capa          | Patrón                                                                         |
| ------------- | ------------------------------------------------------------------------------ |
| **Controllers** | `try/catch (Exception $e)` → `$this->errorResponse('mensaje', 500)`         |
| **Services**    | `throw new \Exception('mensaje', 422)` para reglas de negocio violadas       |
| **Global**      | `bootstrap/app.php` maneja: `AuthenticationException` (401), `AuthorizationException` (403), `ValidationException` (422), `NotFoundHttpException` (404), `QueryException` (500) |

### 6. Inyección de Dependencias

```php
// ✅ CORRECTO — Inyectar Services en el constructor
public function __construct(
    protected PartnerService $partnerService,
    protected PartnerDebtService $debtService
) {}

// ❌ INCORRECTO — Eloquent directo en controller
public function index() {
    $partners = Partner::all(); // NO hacer esto
}
```

### 7. Transacciones de Base de Datos

Usar `DB::transaction()` para operaciones que modifiquen múltiples registros:

```php
return DB::transaction(function () use ($data) {
    $model = Model::create($data);
    // más operaciones...
    return $model;
});
```

---

## 🗃 Modelos y Relaciones Clave

### Modelos del Dominio Principal

| Modelo            | Tabla                        | PK    | Timestamps | Notas                                          |
| ----------------- | ---------------------------- | ----- | ---------- | ---------------------------------------------- |
| `Partner`         | `0cc_socios`                 | `ind` | No         | Modelo central. Categoría: TITULAR / FAMILIAR  |
| `User`            | `users`                      | `id`  | Sí         | Auth con Sanctum. Rol basado en `acc`          |
| `Fee`             | `0cc_cuotas`                 | `ind` | No         | Cuotas mensuales con impuesto                  |
| `HistoryPay`      | `historial_pagos_separado`   | `ind` | No         | Registro individual de pagos                   |
| `Guest`           | `0cc_invitados_unificados`   | `ind` | No         | Invitados por fecha con límites de negocio     |
| `RegisteredGuest` | `0cc_invitados`              | `ind` | No         | Catálogo de invitados conocidos                |
| `Hall`            | `0cc_salones`                | `ind` | No         | Precios de salones (socio / no socio)          |
| `HallControl`     | `0cc_salones_control_unificado` | `ind` | No      | Reservas y control de salones                  |
| `Manager`         | `0cc_directivos_datos`       | `ind` | No         | Datos de directivos                            |
| `ManagerBoards`   | `0cc_directivos_juntas`      | `year`| No         | Juntas anuales. PK no auto-incrementable       |

### Modelos del Módulo de Actividades

> Los pagos viven en `app/Models/activities/payment/` y los clientes en `app/Models/activities/client/`. Todos tienen `$timestamps = false`.

| Modelo               | Tabla                              | PK                            | Notas                              |
| -------------------- | ---------------------------------- | ----------------------------- | ---------------------------------- |
| `NatacionPago`       | `0cc_natacion_pagos`               | `ind` (auto)                  | Columna `anio` como discriminador  |
| `OnboxPago`          | `0cc_onbox_pagos_all`              | `ind` (auto)                  | `mes` YYYY-MM, `d`, montos DECIMAL |
| `LeverPago`          | `0cc_lever_pagos_unificado`        | `id_pago` (auto)              | `mes` YYYY-MM, `d`, montos DECIMAL |
| `PinponPago`         | `0cc_pinpon_pagos_unificada`       | **Compuesta** (`anio_origen`, `ind_original`) | `$primaryKey = null` |
| `BasquetPago`        | `0cc_basquet_pagos`                | `ind` (auto)                  | `mes`, `d`, montos INT             |
| `StrongPago`         | `0cc_strong_pagos_unificada`       | `id_global` (auto)            | `ind_original`, `ano`, sin col `d` |
| `KaratePago`         | `0cc_karate_pagos`                 | `ind` (auto)                  | Sin col `d`, defaults 0 en montos  |
| `InglesPago`         | `0cc_ingles_pagos_unificado`       | **Compuesta** (`ano_tabla`, `ind`) | `$primaryKey = null`          |
| `VoleibolPago`       | `0cc_voleibol_pagos_unificado`     | `ind` (auto)                  | `ano_origen`, sin col `d`          |
| `BattingPago`        | `0cc_batting_pagos_unificada`      | `ind` (auto)                  | `mes`, `d`, montos INT             |
| `AlmaflamencoaPago`  | `0cc_almaflamenca_pagos_unificada` | `id_pago` (auto)              | `ind_original` nullable, sin `d`   |
| `NatacionCliente`    | `0cc_natacion_clientes`            | `ind` (auto)                  | Incluye representantes `repre_*`   |
| `OnboxCliente`       | `0cc_onbox_clientes`               | `ind` (auto)                  | `cedula` BIGINT, `d` tinytext      |
| `LeverCliente`       | `0cc_lever_clientes`               | `ind` (auto)                  | `cedula` BIGINT, `padres` NOT NULL |
| `PinponCliente`      | `0cc_pinpon_clientes`              | `ind` (auto)                  | `cedula` única                     |
| `BasquetCliente`     | `0cc_basquet_clientes`             | `ind` (auto)                  | `ind` INT UNSIGNED                 |
| `StrongCliente`      | `0cc_strong_clientes`              | `cedula`                      | Sin `ind`; PK por `cedula`         |
| `KarateCliente`      | `0cc_karate_clientes`              | `ind` (auto)                  | `cedula` única                     |
| `InglesCliente`      | `0cc_ingles_clientes`              | `ind` (auto)                  | `ind` INT UNSIGNED                 |
| `VoleibolCliente`    | `0cc_voleibol_clientes`            | `ind` (auto)                  | `ind` INT UNSIGNED                 |
| `BattingCliente`     | `0cc_batting_clientes`             | `ind` (auto)                  | `ind` INT UNSIGNED                 |
| `AlmaflamencaCliente` | `0cc_almaflamenca_clientes`        | `ind` (auto)                  | `ind` INT UNSIGNED                 |

> Nota de compatibilidad MySQL: en las tablas de clientes, la columna `socio` se define como `VARCHAR` con default `'No Socio'` aunque algunos SQL legacy la documenten como `TINYTEXT DEFAULT 'No Socio'`; MySQL no permite valores por defecto en columnas `TEXT/TINYTEXT`.

### Relaciones Principales

```
User ──hasMany──▶ Partner (by acc)

Partner (TITULAR)
    ├──hasMany──▶ Partner (FAMILIAR, same acc)
    ├──hasMany──▶ Guest
    └──hasMany──▶ HistoryPay

Guest ──belongsTo──▶ Partner (titular)
RegisteredGuest ──belongsTo──▶ Partner (titular)

ManagerBoards ──belongsTo (x13)──▶ Manager (one per cargo)
```

### Enums

```php
// app/Enum/PartnerCategory.php — Backed Enum: string
PartnerCategory::TITULAR   // 'titular'
PartnerCategory::FAMILIAR  // 'familiar'

// app/Enum/UserRole.php — Backed Enum: string
// Roles derivados del número de acción (acc):
// 1000        → SUPER_ADMIN
// 991-999     → ADMIN
// 961-990     → OPERATOR
// 931-960     → SUPERVISOR
// 901-930     → ALLY
// 801-900     → HONORARY
// resto       → PARTNER
```

---

## 🗺 Endpoints API (Resumen)

### Rutas Públicas (sin autenticación)

| Método | Ruta                           | Controller                  | Acción                                        |
| ------ | ------------------------------ | --------------------------- | --------------------------------------------- |
| POST   | `/register`                    | `AuthController`            | Registro de usuario                           |
| POST   | `/login`                       | `AuthController`            | Login (retorna token)                         |
| GET    | `/partners/solvencia`          | `PartnerController`         | Resumen de deuda pública                      |
| GET    | `/partners/access`             | `PartnerController`         | Validación de acceso                          |
| POST   | `/forgot-password/request`     | `PasswordResetController`   | Paso 1: validar acc+cédula y enviar OTP       |
| POST   | `/forgot-password/verify`      | `PasswordResetController`   | Paso 2: verificar código OTP de 6 dígitos     |
| POST   | `/forgot-password/reset`       | `PasswordResetController`   | Paso 3: establecer nueva contraseña           |

### Rutas Protegidas (`auth:sanctum`) — Segmentadas por Gates

| Gate / Permiso            | Recurso / Ruta                            | Controller                 | Notas                                   |
| ------------------------- | ----------------------------------------- | -------------------------- | --------------------------------------- |
| *(todos autenticados)*    | `POST /logout`, `GET /user`, `GET /halls-pay`, `GET /halls-pay/{id}` | `AuthController`, `HallController` | Sin restricción de rol |
| `list-socios`             | `GET /partners`                           | `PartnerController`        | SUPER_ADMIN + ADMIN + OPERATOR          |
| `view-own-debt`           | `GET /partners/debs/{id}`                 | `PartnerController`        | + Policy: PARTNER solo su acc           |
| `view-own-debt`           | `GET /partners/debs/advance/{id}`         | `PartnerController`        | + Policy: PARTNER solo su acc           |
| `access-finanzas`         | `GET /history`, `POST /history`, `PUT/DELETE /history/{id}` | `HistoryPayController`     | SUPER_ADMIN + ADMIN                     |
| `view-own-debt`           | `GET /history/{acc}`                      | `HistoryPayController`     | + ownership: PARTNER solo su acc        |
| `view-own-debt`           | `GET /history/{acc}/until/{mes}`          | `HistoryPayController`     | Pagos del mes dado (YYYY-MM); ownership igual que show |
| `access-finanzas`         | `GET /partners/solvencia/metrics`         | `PartnerController`        | Métricas globales de morosidad          |
| `access-finanzas`         | `GET /generate/exel/solvencia/{year}`     | `ExcelController`          | Exportar deuda a Excel                  |
| `access-solvencia`        | `GET /partners/solvencia/{year}`          | `PartnerController`        | + OPERATOR + SUPERVISOR                 |
| `manage-cuotas`           | `apiResource fee`                         | `FeeController`            | Solo SUPER_ADMIN                        |
| `list-socios`             | `GET /partners`                           | `PartnerController`        | SUPER_ADMIN + ADMIN + OPERATOR          |
| `view-socios`             | `GET /partners/{partner}`                 | `PartnerController`        | + Policy ownership                      |
| `manage-socios`           | `POST/PUT/DELETE /partners`, `GET/POST/PUT/DELETE /family` (excl. show) | `PartnerController`, `FamilyController` | + `apiResource /family` sin show |
| `view-socios`             | `GET /partners/{partner}`, `GET /family/{family}` | `PartnerController`, `FamilyController` | + Policy ownership; PARTNER/HONORARY solo su acc |
| `manage-directivos`       | `apiResource /manager`, `/board`          | `Manager*Controller`       | SUPER_ADMIN + ADMIN                     |
| `view-salones`            | `GET /halls-control`                      | `HallControlController`    | Todos los autenticados                  |
| `reserve-salones`         | `POST/DELETE /halls-control`              | `HallControlController`    | + Policy + FormRequest business rules   |
| `manage-halls-control`    | `PUT /halls-control/{id}`                 | `HallControlController`    | Solo SUPER_ADMIN + ADMIN                |
| `manage-salones-precios`  | `POST/PUT/DELETE /halls-pay`              | `HallController`           | SUPER_ADMIN + ADMIN                     |
| `access-invitados`        | `apiResource /guest`, `/register-guest`   | `Guest*Controller`         | + Policy ownership para PARTNER/HONORARY|
| `manage-users`            | `/user-admin`                             | `UserAdminController`      | SUPER_ADMIN + ADMIN                     |
| *(todos autenticados)*    | `GET /partners/photo/{cedula}`            | `PartnerPhotoController`   | Retorna URL pública de la foto del socio|
| *(todos autenticados)*    | `GET /activity/{actividad}`               | `*PagoController`@index    | 11 endpoints de pagos por actividad, orden desc por mes     |
| *(todos autenticados)*    | `GET /activity/{actividad}/{mes}`         | `*PagoController`@showByMes | Filtro por mes YYYY-MM; natacion/strong/ingles/voleibol usan sort compuesto |
| *(todos autenticados)*    | `GET /activity/client/{actividad}`        | `*ClienteController`@index | 11 endpoints de clientes por actividad, sin paginación       |
| `access-finanzas`         | `POST /activity/{actividad}`              | `*PagoController`@store    | SUPER_ADMIN + ADMIN; validación con `Store*PagoRequest`     |
| `access-finanzas`         | `POST /activity/client/{actividad}`       | `*ClienteController`@store | SUPER_ADMIN + ADMIN; validación con `Store*ClienteRequest`; unicidad de cédula por tabla |

**Rutas adicionales destacadas:**
- `GET /partners/debs/{id}` — Estado de cuenta (con Policy de propiedad)
- `GET /partners/debs/advance/{id}` — Cuotas adelantadas
- `GET /partners/solvencia/metrics` — Métricas globales de morosidad
- `GET /partners/solvencia/metrics/{metric}` — Socios por métrica de deuda
- `GET /partners/photo/{cedula}` — URL pública de foto del socio (imágenes en `public/assets/acc/`)
- `POST /logout` — Cierre de sesión
- `GET /generate/exel/solvencia/{year}` — Exportar deuda a Excel
- `GET /user-admin` — Listar usuarios (CRUD admin)
- `GET /activity/natacion` — Pagos de natación (paginado, `per_page` default 50), orden descendente por año y mes
- `GET /activity/natacion/{mes}` — Pagos de natación filtrados por mes (formato YYYY-MM)
- `POST /activity/natacion` — Registrar pago de natación (`access-finanzas`)
- `GET /activity/onbox` — Pagos de Onbox, orden descendente por mes
- `GET /activity/onbox/{mes}` — Pagos de Onbox filtrados por mes (YYYY-MM)
- `POST /activity/onbox` — Registrar pago de Onbox (`access-finanzas`)
- `GET /activity/lever` — Pagos de Lever, orden descendente por mes
- `GET /activity/lever/{mes}` — Pagos de Lever filtrados por mes (YYYY-MM)
- `POST /activity/lever` — Registrar pago de Lever (`access-finanzas`)
- `GET /activity/pinpon` — Pagos de Pin Pon, orden descendente por mes
- `GET /activity/pinpon/{mes}` — Pagos de Pin Pon filtrados por mes (YYYY-MM)
- `POST /activity/pinpon` — Registrar pago de Pin Pon (`access-finanzas`)
- `GET /activity/basquet` — Pagos de Básquet, orden descendente por mes
- `GET /activity/basquet/{mes}` — Pagos de Básquet filtrados por mes (YYYY-MM)
- `POST /activity/basquet` — Registrar pago de Básquet (`access-finanzas`)
- `GET /activity/strong` — Pagos de Strong, orden descendente por año y mes
- `GET /activity/strong/{mes}` — Pagos de Strong filtrados por mes
- `POST /activity/strong` — Registrar pago de Strong (`access-finanzas`)
- `GET /activity/karate` — Pagos de Karate, orden descendente por mes
- `GET /activity/karate/{mes}` — Pagos de Karate filtrados por mes
- `POST /activity/karate` — Registrar pago de Karate (`access-finanzas`)
- `GET /activity/ingles` — Pagos de Inglés, orden descendente por año tabla y mes
- `GET /activity/ingles/{mes}` — Pagos de Inglés filtrados por mes
- `POST /activity/ingles` — Registrar pago de Inglés (`access-finanzas`)
- `GET /activity/voleibol` — Pagos de Voleibol, orden descendente por año origen y mes
- `GET /activity/voleibol/{mes}` — Pagos de Voleibol filtrados por mes
- `POST /activity/voleibol` — Registrar pago de Voleibol (`access-finanzas`)
- `GET /activity/batting` — Pagos de Batting, orden descendente por mes
- `GET /activity/batting/{mes}` — Pagos de Batting filtrados por mes (YYYY-MM)
- `POST /activity/batting` — Registrar pago de Batting (`access-finanzas`)
- `GET /activity/almaflamenca` — Pagos de Alma Flamenca, orden descendente por mes
- `GET /activity/almaflamenca/{mes}` — Pagos de Alma Flamenca filtrados por mes
- `POST /activity/almaflamenca` — Registrar pago de Alma Flamenca (`access-finanzas`)
- `GET /activity/client/natacion` — Clientes de natación, sin paginación
- `GET /activity/client/onbox` — Clientes de Onbox, sin paginación
- `GET /activity/client/lever` — Clientes de Lever, sin paginación
- `GET /activity/client/pinpon` — Clientes de Pin Pon, sin paginación
- `GET /activity/client/basquet` — Clientes de Básquet, sin paginación
- `GET /activity/client/strong` — Clientes de Strong, sin paginación
- `GET /activity/client/karate` — Clientes de Karate, sin paginación
- `GET /activity/client/ingles` — Clientes de Inglés, sin paginación
- `GET /activity/client/voleibol` — Clientes de Voleibol, sin paginación
- `GET /activity/client/batting` — Clientes de Batting, sin paginación
- `GET /activity/client/almaflamenca` — Clientes de Alma Flamenca, sin paginación
- `POST /activity/client/natacion` — Registrar cliente de natación (`access-finanzas`); campos: cedula, nombre, socio, nacimiento, sexo; valida unicidad de cédula
- `POST /activity/client/onbox` — Registrar cliente de Onbox (`access-finanzas`)
- `POST /activity/client/lever` — Registrar cliente de Lever (`access-finanzas`); padres nullable (default '')
- `POST /activity/client/pinpon` — Registrar cliente de Pin Pon (`access-finanzas`)
- `POST /activity/client/basquet` — Registrar cliente de Básquet (`access-finanzas`)
- `POST /activity/client/strong` — Registrar cliente de Strong (`access-finanzas`); PK = cedula
- `POST /activity/client/karate` — Registrar cliente de Karate (`access-finanzas`)
- `POST /activity/client/ingles` — Registrar cliente de Inglés (`access-finanzas`)
- `POST /activity/client/voleibol` — Registrar cliente de Voleibol (`access-finanzas`)
- `POST /activity/client/batting` — Registrar cliente de Batting (`access-finanzas`)
- `POST /activity/client/almaflamenca` — Registrar cliente de Alma Flamenca (`access-finanzas`)

---

## 🔄 Flujo de Trabajo para el Agente

### Antes de Programar

1. **Lee este archivo completo.**
2. Identifica en qué módulo/dominio cae la tarea.
3. Revisa los archivos existentes del módulo para entender el patrón actual.

### Al Crear una Nueva Funcionalidad

Seguir este checklist:

```
□ Crear/actualizar Migration en database/migrations/
□ Crear/actualizar Model en app/Models/ (con fillable, casts, relaciones)
□ Crear Service en app/Service/ (lógica de negocio)
□ Crear FormRequest en app/Http/Requests/ (validación)
□ Crear Controller en app/Http/Controllers/ (thin, inyecta Service, usa ApiResponse)
□ Crear Resource en app/Http/Resources/ (si se necesita transformación)
□ Registrar rutas en routes/api.php
□ ✅ Actualizar AGENTS.md
```

### Template: Nuevo Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\NuevoRequest;
use App\Http\Resources\NuevoResource;
use App\Service\NuevoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class NuevoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NuevoService $nuevoService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $data = $this->nuevoService->getAll();
            return $this->successResponse($data, 'Listado obtenido');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener listado', 500);
        }
    }

    public function store(NuevoRequest $request): JsonResponse
    {
        try {
            $item = $this->nuevoService->create($request->validated());
            return $this->successResponse($item, 'Creado exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->nuevoService->getById($id);
            return $this->successResponse($item, 'Detalle obtenido');
        } catch (\Exception $e) {
            return $this->errorResponse('Recurso no encontrado', 404);
        }
    }

    public function update(NuevoRequest $request, int $id): JsonResponse
    {
        try {
            $item = $this->nuevoService->update($id, $request->validated());
            return $this->successResponse($item, 'Actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->nuevoService->delete($id);
            return $this->successResponse(null, 'Eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
```

### Template: Nuevo Service

```php
<?php

namespace App\Service;

use App\Models\NuevoModel;
use Illuminate\Support\Facades\DB;

class NuevoService
{
    public function getAll()
    {
        return NuevoModel::all();
    }

    public function getById(int $id): NuevoModel
    {
        return NuevoModel::findOrFail($id);
    }

    public function create(array $data): NuevoModel
    {
        return DB::transaction(fn () => NuevoModel::create($data));
    }

    public function update(int $id, array $data): NuevoModel
    {
        return DB::transaction(function () use ($id, $data) {
            $item = NuevoModel::findOrFail($id);
            $item->update($data);
            return $item;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $item = NuevoModel::findOrFail($id);
            return $item->delete();
        });
    }
}
```

---

## 🚨 Restricciones Específicas

1. **No colocar lógica de negocio en rutas ni en controllers.** Los controllers solo orquestan: reciben request, llaman al service, retornan response.
2. **No ejecutar queries Eloquent directas en controllers.** Toda interacción con la BD pasa por Services.
3. **No validar inline en controllers.** Siempre usar FormRequest classes.
4. **No crear endpoints en `routes/web.php`.** Este es un proyecto API-only; todas las rutas van en `routes/api.php`.
5. **Nunca retornar vistas Blade ni HTML como respuesta HTTP.** Todos los controllers retornan exclusivamente `JsonResponse`. Las vistas Blade (`resources/views/`) están reservadas **únicamente** para el cuerpo HTML de correos electrónicos (Mailables). El frontend React es responsable de toda la presentación.
5. **Usar `DB::transaction()`** para cualquier operación que modifique múltiples tablas/registros.
6. **CORS permisivo** — La configuración actual permite `*` en origins. En producción, restringir a dominios específicos en `config/cors.php`.
7. **Tokens Sanctum sin expiración** — `sanctum.expiration` está en `null`. Evaluar configurar expiración para producción.
8. **Directorio `app/Service/` es SINGULAR** — No crear `app/Services/` (con S).

---

## ⚠️ Inconsistencias Conocidas

Estas son desviaciones del patrón estándar detectadas en el código actual. Al trabajar en estos archivos, considerar alinearlos con las convenciones:

| Archivo                | Inconsistencia                                                                 |
| ---------------------- | ------------------------------------------------------------------------------ |
| `AuthController`       | No usa el trait `ApiResponse`. Respuestas JSON manuales. No delega a Service.  |
| `ExcelController`      | No usa el trait `ApiResponse`. Respuestas JSON manuales.                       |
| `access_controller()`  | Método en `PartnerController` que rompe la convención camelCase.               |
| Resources (6 de 10)    | Solo retornan `id`, `created_at`, `updated_at` — son scaffolding sin transformación real. |
| `UserAdminService`     | `updateUser()` tiene tipo de retorno `Manager` en vez de `User` (bug).         |
| Proyecto completo      | No se usa `declare(strict_types=1)` en ningún archivo.                         |

---

## 🔧 Mantenimiento — Regla de Oro

> **OBLIGATORIO:** Cada vez que se implemente una nueva funcionalidad o se realice un cambio estructural, este archivo `AGENTS.md` DEBE ser actualizado para reflejar el estado actual del sistema.

### ¿Cuándo actualizar?

- [ ] Se crea un nuevo **Model**, **Service**, **Controller**, **FormRequest**, **Resource** o **Enum**.
- [ ] Se agregan o modifican **rutas** en `api.php`.
- [ ] Se cambia la **arquitectura** o se introduce un nuevo patrón (ej: Repository, Events, Jobs).
- [ ] Se agrega una **dependencia** significativa en `composer.json`.
- [ ] Se resuelve una de las **inconsistencias conocidas** listadas arriba.
- [ ] Se modifica la configuración de **autenticación**, **CORS** o **excepciones globales**.
- [ ] Se modifica la **estructura de directorios**.

### ¿Qué secciones actualizar?

| Cambio realizado                     | Secciones a actualizar                                       |
| ------------------------------------ | ------------------------------------------------------------ |
| Nuevo modelo/migración               | Modelos y Relaciones Clave                                   |
| Nuevo controller + service + routes  | Endpoints API, Estructura de Directorios                     |
| Nueva dependencia en composer.json   | Stack Técnico                                                |
| Nuevo patrón arquitectónico          | Arquitectura y Patrones                                      |
| Fix de inconsistencia                | Inconsistencias Conocidas (remover la resuelta)              |
| Cambio en convenciones               | Reglas de Desarrollo                                         |

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2.30
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Tests applications using the Pest 3 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, architecture testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.
</laravel-boost-guidelines>
