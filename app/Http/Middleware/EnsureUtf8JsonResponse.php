<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUtf8JsonResponse
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
            
            // Skip file downloads and non-JSON responses
            if (!($response instanceof JsonResponse)) {
                return $response;
            }
            
            // Skip if this is a file download (PDF, Excel, etc.)
            $contentDisposition = $response->headers->get('Content-Disposition');
            if ($contentDisposition && strpos($contentDisposition, 'attachment') !== false) {
                return $response;
            }
            
            // Only process Livewire/AJAX JSON responses
            if ($response instanceof JsonResponse) {
                try {
                    // Get the response content as string first to avoid encoding issues
                    $content = $response->getContent();
                    
                    // If content is already a valid JSON string, try to decode and clean
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                        // Content is valid JSON, clean it
                        $cleanData = $this->sanitizeForJson($decoded);
                        
                        // Test encoding
                        $testJson = json_encode($cleanData);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            Log::warning('JSON re-encoding failed, applying ultra clean', [
                                'error' => json_last_error_msg(),
                                'url' => $request->url()
                            ]);
                            
                            $cleanData = $this->ultraClean($cleanData);
                            $testJson = json_encode($cleanData);
                            
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                Log::error('JSON encoding failed after ultra clean in middleware', [
                                    'error' => json_last_error_msg(),
                                    'url' => $request->url()
                                ]);
                                // Don't interrupt the response, let it pass through
                                return $response;
                            }
                        }
                        
                        // Create new response with cleaned data
                        $newResponse = response()->json($cleanData);
                        $newResponse->headers = $response->headers;
                        return $newResponse;
                    }
                    
                } catch (\Exception $e) {
                    Log::warning('Exception in UTF-8 middleware JSON processing', [
                        'error' => $e->getMessage(),
                        'url' => $request->url()
                    ]);
                    // Don't interrupt the response, let it pass through
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Critical exception in UTF-8 middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->url()
            ]);
            
            // Don't interrupt the flow, return the original response
            return $next($request);
        }
    }
    
    /**
     * Sanitize data for JSON encoding
     */
    private function sanitizeForJson($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? $this->cleanString($key) : $key;
                $result[$cleanKey] = $this->sanitizeForJson($value);
            }
            return $result;
        }
        
        if (is_string($data)) {
            return $this->cleanString($data);
        }
        
        return $data;
    }
    
    /**
     * Clean string for UTF-8 compliance
     */
    private function cleanString($str)
    {
        // Convert to UTF-8 if needed
        if (!mb_check_encoding($str, 'UTF-8')) {
            $str = mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str, 'UTF-8,ISO-8859-1,ASCII', true) ?: 'UTF-8');
        }
        
        // Remove control characters
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $str);
        
        // Ensure valid UTF-8
        $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        
        return $str;
    }
    
    /**
     * Ultra aggressive cleaning - ASCII only
     */
    private function ultraClean($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? preg_replace('/[^\x20-\x7E]/', '', $key) : $key;
                $result[$cleanKey] = $this->ultraClean($value);
            }
            return $result;
        }
        
        if (is_string($data)) {
            return preg_replace('/[^\x20-\x7E]/', '', $data);
        }
        
        return $data;
    }
}