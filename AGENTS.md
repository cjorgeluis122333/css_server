# 🤖 Project Context & Agent Rules

> **Última actualización:** 30 de abril de 2026
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
- **Torneos de Dominó** (módulo en desarrollo: torneos, rondas, partidas, equipos, jugadores).
- **Exportación a Excel** de reportes de deuda.
- **Administración de usuarios** con roles jerárquicos basados en número de acción.

El frontend es una aplicación **React** en un repositorio separado que consume esta API mediante **Laravel Sanctum**.

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
| **Service Layer**             | `app/Service/` — 11 services con lógica de negocio                             |
| **FormRequest Validation**    | `app/Http/Requests/` — validación desacoplada de controllers                   |
| **API Response Trait**        | `app/Traits/ApiResponse.php` — formato estándar JSON                           |
| **API Resources**             | `app/Http/Resources/` — transformación de modelos + display condicional RBAC   |
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
│   ├── Controllers/       # 14 controllers (thin, delegan a Services)
│   ├── Middleware/         # Middleware personalizado
│   ├── Requests/          # FormRequest validation classes
│   └── Resources/         # API Resource transformations (con display condicional RBAC)
├── Models/                # 16 modelos Eloquent
├── Policies/              # 4 Policies: Partner, HallControl, Guest, HistoryPay
├── Providers/             # Service Providers (AppServiceProvider)
├── Service/               # ⚠️ SINGULAR — 11 services de lógica de negocio
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
| Tablas DB     | Legacy: `0cc_*` / `domino_*` / Laravel estándar                  |

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

### Modelos del Módulo de Torneos (En Desarrollo)

| Modelo         | Tabla                        | PK   | Timestamps        |
| -------------- | ---------------------------- | ---- | ------------------ |
| `Tournament`   | `domino_2025_torneos`        | `id` | `fecha_creacion`   |
| `Round`        | `domino_2025_rondas`         | `id` | `created_at`       |
| `Game`         | `domino_2025_partidas`       | `id` | `fecha_actualizacion` |
| `Couple`       | `domino_2025_parejas`        | `id` | Sí                 |
| `Team`         | `domino_2025_equipos`        | `id` | Sí                 |
| `Player`       | `domino_2025_jugadores`      | `id` | Sí                 |
| `Substitution` | `domino_2025_sustituciones`  | `id` | Sí                 |

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

Tournament ──hasMany──▶ Round ──hasMany──▶ Game
Tournament ──hasMany──▶ Couple
Tournament ──hasMany──▶ Substitution
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

| Método | Ruta                    | Controller            | Acción                    |
| ------ | ----------------------- | --------------------- | ------------------------- |
| POST   | `/register`             | `AuthController`      | Registro de usuario       |
| POST   | `/login`                | `AuthController`      | Login (retorna token)     |
| GET    | `/partners/solvencia`   | `PartnerController`   | Resumen de deuda pública  |
| GET    | `/partners/access`      | `PartnerController`   | Validación de acceso      |

### Rutas Protegidas (`auth:sanctum`) — Segmentadas por Gates

| Gate / Permiso            | Recurso / Ruta                            | Controller                 | Notas                                   |
| ------------------------- | ----------------------------------------- | -------------------------- | --------------------------------------- |
| *(todos autenticados)*    | `POST /logout`, `GET /user`, `GET /halls-pay`, `GET /halls-pay/{id}` | `AuthController`, `HallController` | Sin restricción de rol |
| `list-socios`             | `GET /partners`                           | `PartnerController`        | SUPER_ADMIN + ADMIN + OPERATOR          |
| `view-own-debt`           | `GET /partners/debs/{id}`                 | `PartnerController`        | + Policy: PARTNER solo su acc           |
| `view-own-debt`           | `GET /partners/debs/advance/{id}`         | `PartnerController`        | + Policy: PARTNER solo su acc           |
| `access-finanzas`         | `GET /history`, `POST /history`, `PUT/DELETE /history/{id}` | `HistoryPayController`     | SUPER_ADMIN + ADMIN                     |
| `view-own-debt`           | `GET /history/{acc}`                      | `HistoryPayController`     | + ownership: PARTNER solo su acc        |
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

**Rutas adicionales destacadas:**
- `GET /partners/debs/{id}` — Estado de cuenta (con Policy de propiedad)
- `GET /partners/debs/advance/{id}` — Cuotas adelantadas
- `GET /partners/solvencia/metrics` — Métricas globales de morosidad
- `GET /partners/solvencia/metrics/{metric}` — Socios por métrica de deuda
- `POST /logout` — Cierre de sesión
- `GET /generate/exel/solvencia/{year}` — Exportar deuda a Excel
- `GET /user-admin` — Listar usuarios (CRUD admin)

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
| FormRequests (6)       | Clases vacías sin reglas: `GameRequest`, `PlayerRequest`, `RoundRequest`, `SubstitutionRequest`, `TeamRequest`, `TournamentRequest`. |
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
