<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })

    ->withExceptions(function (Exceptions $exceptions) {

        // Forzamos JSON en el prefijo API
        $exceptions->shouldRenderJsonWhen(function (Request $request, $e) {
            return $request->is('api/*');
        });

        // Manejo global de excepciones para la API
        $exceptions->render(function (Throwable $e, Request $request) {
            // Solo intervenimos si es una petición de API
            if ($request->is('api/*')) {

                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'No autorizado: Token faltante o expirado.',
                        'code'    => 401
                    ], 401);
                }

                if ($e instanceof ValidationException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Los datos proporcionados no son válidos.',
                        'errors'  => $e->errors(),
                        'code'    => 422
                    ], 422);
                }

                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'El recurso solicitado no existe.',
                        'code'    => 404
                    ], 404);
                }

                if ($e instanceof QueryException) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Error de integridad en la base de datos.',
                        'code'    => 500,
                        'debug'   => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }

                // Error genérico para cualquier otra cosa en la API
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage() ?: 'Error interno del servidor.',
                    'code'    => 500
                ], 500);
            }

            // --- ESTE ES EL RETURN QUE FALTABA ---
            // Si no es API, retornamos null para que Laravel use el manejador por defecto (HTML)
            return null;
        });
    })->create();
