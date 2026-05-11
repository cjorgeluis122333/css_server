<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyResetCodeRequest;
use App\Service\PasswordResetService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PasswordResetService $passwordResetService
    ) {}

    /**
     * Step 1 — Validate acc + cedula and send a 6-digit OTP to the registered email.
     */
    public function request(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $data           = $request->validated();
            $obfuscatedEmail = $this->passwordResetService->requestReset(
                (int) $data['acc'],
                (string) $data['cedula']
            );

            return $this->successResponse(
                ['correo' => $obfuscatedEmail],
                'Se ha enviado un código de verificación al correo registrado. Válido por 2 minutos.'
            );
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    /**
     * Step 2 — Verify the OTP code. On success the session is unlocked for the reset step.
     */
    public function verify(VerifyResetCodeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->passwordResetService->verifyCode(
                (int) $data['acc'],
                (string) $data['code']
            );

            return $this->successResponse(
                null,
                'Código verificado correctamente. Ahora puedes establecer tu nueva contraseña.'
            );
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    /**
     * Step 3 — Set the new password. Requires prior code verification.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->passwordResetService->resetPassword(
                (int) $data['acc'],
                (string) $data['password']
            );

            return $this->successResponse(
                null,
                'Contraseña actualizada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.'
            );
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }
}
