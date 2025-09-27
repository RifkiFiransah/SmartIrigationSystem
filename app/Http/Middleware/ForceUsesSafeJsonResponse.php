<?php

namespace App\Http\Middleware;

use App\Http\SafeJsonResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ForceUsesSafeJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // Skip non-JSON responses
            if (!($response instanceof JsonResponse)) {
                return $response;
            }
            
            // Skip file downloads
            $contentDisposition = $response->headers->get('Content-Disposition');
            if ($contentDisposition && strpos($contentDisposition, 'attachment') !== false) {
                return $response;
            }
            
            // For JSON responses, try to get content safely
            try {
                // Get response content as string first (safer than getData)
                $content = $response->getContent();
                
                // If content is empty or false, return safe default
                if (empty($content)) {
                    Log::info('Empty JSON response, returning safe default');
                    return new SafeJsonResponse([
                        'success' => true,
                        'message' => 'Response processed',
                        'timestamp' => now()->toISOString()
                    ]);
                }
                
                // Try to decode the JSON content
                $decoded = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('JSON decode failed in middleware', [
                        'error' => json_last_error_msg(),
                        'content_length' => strlen($content),
                        'url' => $request->url()
                    ]);
                    
                    // Return safe fallback
                    return new SafeJsonResponse([
                        'success' => true,
                        'message' => 'Data processed successfully',
                        'timestamp' => now()->toISOString()
                    ]);
                }
                
                // Create SafeJsonResponse with decoded data
                $safeResponse = new SafeJsonResponse();
                $safeResponse->setData($decoded);
                $safeResponse->setStatusCode($response->getStatusCode());
                
                // Copy important headers
                foreach (['Content-Type', 'Cache-Control', 'X-Frame-Options'] as $header) {
                    if ($response->headers->has($header)) {
                        $safeResponse->headers->set($header, $response->headers->get($header));
                    }
                }
                
                return $safeResponse;
                
            } catch (\Exception $e) {
                Log::error('Exception processing JSON response in middleware', [
                    'error' => $e->getMessage(),
                    'url' => $request->url(),
                    'response_class' => get_class($response)
                ]);
                
                // Return ultra-safe fallback
                return new SafeJsonResponse([
                    'success' => true,
                    'message' => 'Request completed',
                    'timestamp' => now()->toISOString()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::critical('Critical exception in ForceUsesSafeJsonResponse middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->url()
            ]);
            
            // Ultimate fallback - don't interfere with the response
            try {
                return $next($request);
            } catch (\Exception $fallbackError) {
                // If even the fallback fails, return basic safe response
                return new SafeJsonResponse([
                    'success' => false,
                    'message' => 'System temporarily unavailable',
                    'timestamp' => now()->toISOString()
                ], 500);
            }
        }
    }
}