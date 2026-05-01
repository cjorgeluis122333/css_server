<?php

namespace App\Providers;

use App\Enum\UserRole;
use App\Models\Guest;
use App\Models\HallControl;
use App\Models\Partner;
use App\Models\User;
use App\Policies\GuestPolicy;
use App\Policies\HallControlPolicy;
use App\Policies\HistoryPayPolicy;
use App\Policies\PartnerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerGates();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Partner::class, PartnerPolicy::class);
        Gate::policy(HallControl::class, HallControlPolicy::class);
        Gate::policy(Guest::class, GuestPolicy::class);
    }

    protected function registerGates(): void
    {
        // SUPER_ADMIN (acc 1000) bypasses all gates
        Gate::before(function (User $user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Finanzas: pagos, operaciones, contabilidad, reportes, Excel, métricas
        Gate::define('access-finanzas', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN);
        });

        // Solvencia: vista global de deuda de todos los titulares
        Gate::define('access-solvencia', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR);
        });

        // Solvencia propia: ver deuda/pagos de su propio acc
        Gate::define('view-own-debt', function (User $user): bool {
            return $user->hasRole(
                UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR, UserRole::PARTNER
            );
        });

        // Cuotas: solo SUPER_ADMIN (via Gate::before)
        Gate::define('manage-cuotas', function (User $user): bool {
            return false;
        });

        // Socios: ver datos individuales (PARTNER/HONORARY ven los propios via Policy)
        Gate::define('view-socios', function (User $user): bool {
            return $user->hasRole(
                UserRole::ADMIN, UserRole::OPERATOR, UserRole::HONORARY, UserRole::PARTNER
            );
        });

        // Socios: listar todos
        Gate::define('list-socios', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN);
        });

        // Socios: crear, editar, eliminar
        Gate::define('manage-socios', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN, UserRole::OPERATOR);
        });

        // Directivos: CRUD managers y boards
        Gate::define('manage-directivos', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN);
        });

        // Salones: ver todos los registros de control
        Gate::define('view-salones', function (User $user): bool {
            return $user->hasRole(
                UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR,
                UserRole::ALLY, UserRole::HONORARY, UserRole::PARTNER
            );
        });

        // Salones: reservar (sin pago) — PARTNER/HONORARY solo su acc via Policy
        Gate::define('reserve-salones', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN, UserRole::HONORARY, UserRole::PARTNER);
        });

        // Salones: ocupar (con pago presencial)
        Gate::define('occupy-salones', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN);
        });

        // Salones: gestión de precios (halls-pay CRUD)
        Gate::define('manage-salones-precios', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN);
        });

        // Invitados: PARTNER/HONORARY solo su acc via Policy
        Gate::define('access-invitados', function (User $user): bool {
            return $user->hasRole(
                UserRole::ADMIN, UserRole::OPERATOR, UserRole::SUPERVISOR,
                UserRole::HONORARY, UserRole::PARTNER
            );
        });

        // Administración de usuarios
        Gate::define('manage-users', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN);
        });

        // Actividades (módulo futuro)
        Gate::define('access-actividades', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN, UserRole::OPERATOR, UserRole::ALLY);
        });

        // Notificaciones (módulo futuro)
        Gate::define('access-notificaciones', function (User $user): bool {
            return $user->hasRole(UserRole::ADMIN);
        });
    }
}
