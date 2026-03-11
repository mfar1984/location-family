<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'timestamp' => now()->timestamp * 1000,
                ], 422);
            }
        });

        // Handle database exceptions
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->is('api/*')) {
                // Log the database error with context
                \Log::error('Database error occurred', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A',
                    'bindings' => $e->getBindings() ?? [],
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'timestamp' => now()->toDateTimeString(),
                ]);

                return response()->json([
                    'error' => true,
                    'message' => 'Database error occurred',
                    'code' => 'DATABASE_ERROR',
                    'timestamp' => now()->timestamp * 1000,
                ], 500);
            }
        });

        // Handle PDO exceptions (lower-level database errors)
        $exceptions->render(function (\PDOException $e, $request) {
            if ($request->is('api/*')) {
                // Log the PDO error with context
                \Log::error('PDO error occurred', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'timestamp' => now()->toDateTimeString(),
                ]);

                return response()->json([
                    'error' => true,
                    'message' => 'Database connection error',
                    'code' => 'DATABASE_CONNECTION_ERROR',
                    'timestamp' => now()->timestamp * 1000,
                ], 500);
            }
        });

        // Handle model not found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => true,
                    'message' => 'Resource not found',
                    'code' => 'RESOURCE_NOT_FOUND',
                    'timestamp' => now()->timestamp * 1000,
                ], 404);
            }
        });

        // Handle general exceptions for API routes
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                // Log the general error with context
                \Log::error('Unexpected error occurred', [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'timestamp' => now()->toDateTimeString(),
                ]);

                return response()->json([
                    'error' => true,
                    'message' => 'An unexpected error occurred',
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'timestamp' => now()->timestamp * 1000,
                ], 500);
            }
        });
    })->create();
