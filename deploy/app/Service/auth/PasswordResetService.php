<?php

namespace App\Service\auth;

use App\Mail\PasswordResetMail;
use App\Models\partners\Partner;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    private const CODE_TTL_SECONDS = 120;  // 2 minutes

    private const VERIFIED_TTL_SECONDS = 600; // 10 minutes

    private function codeKey(int $acc): string
    {
        return "password_reset_code:{$acc}";
    }

    private function verifiedKey(int $acc): string
    {
        return "password_reset_verified:{$acc}";
    }

    /**
     * Validates acc + cedula, creates a 6-digit OTP, caches it and sends it by email.
     * Returns the obfuscated email so the frontend can show it to the user.
     *
     * @throws \Exception 404 if no account exists for the given acc+cedula
     */
    public function requestReset(int $acc, string $cedula): string
    {
        $user = User::where('acc', $acc)
            ->where('cedula', $cedula)
            ->first();

        if (! $user) {
            throw new \Exception('No existe una cuenta registrada para los datos proporcionados.', 404);
        }

        $code = (string) random_int(100000, 999999);

        Cache::put($this->codeKey($acc), $code, self::CODE_TTL_SECONDS);

        // Remove any previous verified flag so the user must re-verify
        Cache::forget($this->verifiedKey($acc));

        Mail::to($user->correo)->send(new PasswordResetMail($code, $acc));

        return $this->obfuscateEmail($user->correo);
    }

    /**
     * Verifies the OTP code. On success, marks the acc as verified in cache.
     *
     * @throws \Exception 422 if code is invalid or expired
     */
    public function verifyCode(int $acc, string $code): void
    {
        $stored = Cache::get($this->codeKey($acc));

        if ($stored === null) {
            throw new \Exception('El código ha expirado. Por favor solicita uno nuevo.', 422);
        }

        if (! hash_equals($stored, $code)) {
            throw new \Exception('El código ingresado no es válido.', 422);
        }

        // Code consumed — remove it and mark as verified
        Cache::forget($this->codeKey($acc));
        Cache::put($this->verifiedKey($acc), true, self::VERIFIED_TTL_SECONDS);
    }

    /**
     * Resets the password. Requires prior code verification for the same acc.
     *
     * @throws \Exception 422 if verification step was not completed or has expired
     * @throws \Exception 404 if the user account no longer exists
     */
    public function resetPassword(int $acc, string $password): void
    {
        if (! Cache::get($this->verifiedKey($acc))) {
            throw new \Exception('La verificación del código ha expirado o no se completó. Inicia el proceso nuevamente.', 422);
        }

        $user = User::where('acc', $acc)->first();

        if (! $user) {
            throw new \Exception('No existe una cuenta para esta acción.', 404);
        }

        $user->update(['password' => Hash::make($password)]);

        Cache::forget($this->verifiedKey($acc));
    }

    // -------------------------------------------------------------------------
    // Direct password reset flow (no external service / no email required)
    // -------------------------------------------------------------------------

    private function directTokenKey(int $acc): string
    {
        return "password_direct_token:{$acc}";
    }

    /**
     * Validates acc + cedula + correo against the Partner (0cc_socios) record.
     * Then verifies that the partner has a registered user account.
     * On success generates a short-lived token and returns it so the
     * frontend can use it directly in the reset step.
     *
     * @throws \Exception 404 if no titular partner matches the given data
     * @throws \Exception 404 if the partner has no user account registered
     */
    public function directValidate(int $acc, string $cedula, string $correo): string
    {
        $partner = Partner::holders()
            ->where('acc', $acc)
            ->where('cedula', $cedula)
            ->where('correo', $correo)
            ->first();

        if (! $partner) {
            throw new \Exception('No se encontró un socio titular con los datos proporcionados.', 404);
        }

        $user = User::where('acc', $acc)->first();

        if (! $user) {
            throw new \Exception('El socio no cuenta con una cuenta de usuario registrada. Por favor comunícate con la administración.', 404);
        }

        $token = bin2hex(random_bytes(32));

        Cache::put($this->directTokenKey($acc), hash('sha256', $token), self::VERIFIED_TTL_SECONDS);

        return $token;
    }

    /**
     * Resets the password using the token issued by directValidate().
     *
     * @throws \Exception 422 if token is invalid or expired
     * @throws \Exception 404 if the user account no longer exists
     */
    public function directReset(int $acc, string $token, string $password): void
    {
        $stored = Cache::get($this->directTokenKey($acc));

        if ($stored === null || ! hash_equals($stored, hash('sha256', $token))) {
            throw new \Exception('El token es inválido o ha expirado. Inicia el proceso nuevamente.', 422);
        }

        $user = User::where('acc', $acc)->first();

        if (! $user) {
            throw new \Exception('No existe una cuenta para esta acción.', 404);
        }

        $user->update(['password' => Hash::make($password)]);

        Cache::forget($this->directTokenKey($acc));
    }

    private function obfuscateEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);

        $visible = substr($local, 0, min(2, strlen($local)));
        $masked = str_repeat('*', max(0, strlen($local) - 2));

        return "{$visible}{$masked}@{$domain}";
    }
}
