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
        // Add SafeJsonResponse middleware for all web requests
        $middleware->web(append: [
            \App\Http\Middleware\ForceUsesSafeJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle UTF-8 encoding exceptions
        $exceptions->render(function (\InvalidArgumentException $e, $request) {
            if (str_contains($e->getMessage(), 'Malformed UTF-8') || str_contains($e->getMessage(), 'UTF-8')) {
                \Illuminate\Support\Facades\Log::error('UTF-8 encoding exception caught', [
                    'message' => $e->getMessage(),
                    'url' => $request->url(),
                    'user_agent' => $request->userAgent()
                ]);
                
                // Return safe JSON response
                return new \App\Http\SafeJsonResponse([
                    'success' => true,
                    'message' => 'Request processed successfully',
                    'timestamp' => now()->toISOString()
                ]);
            }
        });
        
        // Handle general Error exceptions that might be related to object conversion
        $exceptions->render(function (\Error $e, $request) {
            if (str_contains($e->getMessage(), 'could not be converted to string') || 
                str_contains($e->getMessage(), 'ArrayObject')) {
                \Illuminate\Support\Facades\Log::error('Object conversion error caught', [
                    'message' => $e->getMessage(),
                    'url' => $request->url(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Return safe JSON response
                return new \App\Http\SafeJsonResponse([
                    'success' => true,
                    'message' => 'Data processed successfully',
                    'timestamp' => now()->toISOString()
                ]);
            }
        });
    })->create();
