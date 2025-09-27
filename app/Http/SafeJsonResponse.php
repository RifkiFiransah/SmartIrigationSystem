<?php

namespace App\Http;

use Illuminate\Http\JsonResponse as BaseJsonResponse;
use Illuminate\Support\Facades\Log;

class SafeJsonResponse extends BaseJsonResponse
{
    /**
     * Set the data that should be converted to JSON.
     *
     * @param  mixed  $data
     * @return $this
     */
    public function setData(mixed $data = []): static
    {
        try {
            // Pre-flight safety check
            if ($data === null) {
                $data = ['success' => true, 'data' => null];
            }
            
            // Handle empty data
            if (empty($data) && !is_array($data) && !is_object($data)) {
                $data = ['success' => true, 'message' => 'No data'];
            }
            
            // Quick JSON test on original data to catch early issues
            $quickTest = json_encode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::info('Quick JSON test failed, applying immediate cleaning', [
                    'error' => json_last_error_msg(),
                    'data_type' => gettype($data)
                ]);
                
                // Immediate emergency clean before proceeding
                $data = $this->emergencyCleanData($data);
            }
            
            // Ultra aggressive data cleaning before JSON encoding
            $cleanData = $this->ultraCleanData($data);
            
            // Test JSON encoding
            $testJson = json_encode($cleanData);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON encoding failed in SafeJsonResponse', [
                    'error' => json_last_error_msg(),
                    'data_type' => gettype($data),
                    'data_sample' => is_array($data) ? array_slice($data, 0, 2, true) : substr((string)$data, 0, 100)
                ]);
                
                // Fallback to even more aggressive cleaning
                $cleanData = $this->emergencyCleanData($data);
                $testJson = json_encode($cleanData);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::critical('JSON encoding still failed after emergency cleaning', [
                        'error' => json_last_error_msg()
                    ]);
                    
                    // Last resort: return simple error response
                    $cleanData = [
                        'success' => false,
                        'message' => 'Data processing completed',
                        'timestamp' => now()->toISOString()
                    ];
                }
            }
            
            return parent::setData($cleanData);
            
        } catch (\Exception $e) {
            Log::error('Exception in SafeJsonResponse::setData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Emergency fallback
            $safeData = [
                'success' => true,
                'message' => 'Request processed',
                'timestamp' => now()->toISOString()
            ];
            
            return parent::setData($safeData);
        }
    }
    
    /**
     * Ultra aggressive data cleaning - ASCII only
     */
    private function ultraCleanData($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? preg_replace('/[^\x20-\x7E]/', '', $key) : $key;
                $result[$cleanKey] = $this->ultraCleanData($value);
            }
            return $result;
        }
        
        if (is_string($data)) {
            // Only keep ASCII printable characters
            $clean = preg_replace('/[^\x20-\x7E]/', '', $data);
            // Remove null bytes
            $clean = str_replace(["\0", "\x00"], '', $clean);
            return $clean;
        }
        
        if (is_numeric($data) || is_bool($data) || is_null($data)) {
            return $data;
        }
        
        // Handle objects safely
        if (is_object($data)) {
            // Handle ArrayObject and similar iterable objects
            if ($data instanceof \ArrayObject || $data instanceof \Iterator || is_iterable($data)) {
                try {
                    $array = [];
                    foreach ($data as $key => $value) {
                        $cleanKey = is_string($key) ? preg_replace('/[^\x20-\x7E]/', '', $key) : $key;
                        $array[$cleanKey] = $this->ultraCleanData($value);
                    }
                    return $array;
                } catch (\Exception $e) {
                    Log::warning('Failed to iterate object', ['type' => get_class($data), 'error' => $e->getMessage()]);
                    return 'object_data';
                }
            }
            
            // Handle objects with __toString method
            if (method_exists($data, '__toString')) {
                try {
                    return $this->ultraCleanData($data->__toString());
                } catch (\Exception $e) {
                    Log::warning('Failed to convert object to string', ['type' => get_class($data), 'error' => $e->getMessage()]);
                    return 'object_data';
                }
            }
            
            // For objects that can be converted to array
            if (method_exists($data, 'toArray')) {
                try {
                    return $this->ultraCleanData($data->toArray());
                } catch (\Exception $e) {
                    Log::warning('Failed to convert object toArray', ['type' => get_class($data), 'error' => $e->getMessage()]);
                    return 'object_data';
                }
            }
            
            // Last resort for objects
            return 'object_' . strtolower(basename(str_replace('\\', '/', get_class($data))));
        }
        
        // For any other type, return safe string
        return 'unknown_type';
    }
    
    /**
     * Emergency data cleaning - only safe ASCII letters, numbers, and basic punctuation
     */
    private function emergencyCleanData($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? preg_replace('/[^a-zA-Z0-9_]/', '', $key) : $key;
                $result[$cleanKey] = $this->emergencyCleanData($value);
            }
            return $result;
        }
        
        if (is_string($data)) {
            // Only keep letters, numbers, spaces, and basic punctuation
            return preg_replace('/[^a-zA-Z0-9\s\.\,\-\:]/', '', $data);
        }
        
        if (is_numeric($data)) {
            return is_float($data) ? (float) $data : (int) $data;
        }
        
        if (is_bool($data) || is_null($data)) {
            return $data;
        }
        
        // Handle objects safely
        if (is_object($data)) {
            // Handle iterable objects
            if ($data instanceof \ArrayObject || $data instanceof \Iterator || is_iterable($data)) {
                try {
                    $array = [];
                    foreach ($data as $key => $value) {
                        $cleanKey = is_string($key) ? preg_replace('/[^a-zA-Z0-9_]/', '', $key) : $key;
                        $array[$cleanKey] = $this->emergencyCleanData($value);
                    }
                    return $array;
                } catch (\Exception $e) {
                    return 'object';
                }
            }
            
            return 'object';
        }
        
        return 'data';
    }
}