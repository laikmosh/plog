<?php

namespace Laikmosh\Plog\Loggers;

use Laikmosh\Plog\Models\PlogEntry;
use Laikmosh\Plog\Models\PlogRequest;
use Laikmosh\Plog\Services\RequestIdService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class PlogHandler
{
    protected $app;
    protected static $nextLogTags = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function handle($level, $message, array $context = [])
    {
        if (!config('plog.enabled', true)) {
            return;
        }

        try {
            $metadata = $this->collectMetadata();

            // Check if tags were passed in the context with special key
            $tags = null;
            if (isset($context['_tags'])) {
                $tags = $context['_tags'];
                // Remove _tags from context so it doesn't appear in the logged data
                unset($context['_tags']);
            }

            // Fallback to static tags if no context tags
            if (!$tags) {
                $tags = self::$nextLogTags;
                self::$nextLogTags = [];
            }

            // Store request data if not already stored for this request ID
            if ($metadata['request_id'] && !$this->app->runningInConsole()) {
                $this->storeRequestData($metadata['request_id']);
            }

            $entry = PlogEntry::create([
                'level' => $level,
                'message' => $message,
                'context' => empty($context) ? null : $context,
                'user_id' => $metadata['user_id'],
                'session_id' => $metadata['session_id'],
                'request_id' => $metadata['request_id'],
                'environment' => $metadata['environment'],
                'endpoint' => $metadata['endpoint'],
                'file' => $metadata['file'],
                'line' => $metadata['line'],
                'class' => $metadata['class'],
                'method' => $metadata['method'],
                'tags' => empty($tags) ? null : $tags,
                'stack_trace' => $this->getCleanStackTrace(),
            ]);

            return $entry;
        } catch (\Exception $e) {
            // Silently fail if database is not ready
            // This prevents issues during package:discover
            return null;
        }
    }

    public static function setNextLogTags(array $tags)
    {
        self::$nextLogTags = $tags;
    }

    protected function collectMetadata()
    {
        $metadata = [
            'user_id' => null,
            'session_id' => null,
            'request_id' => null,
            'environment' => $this->getEnvironment(),
            'endpoint' => null,
            'file' => null,
            'line' => null,
            'class' => null,
            'method' => null,
        ];

        if (config('plog.capture.user_id', true) && Auth::check()) {
            $metadata['user_id'] = Auth::id();
        }

        if (config('plog.capture.session_id', true) && $this->app->runningInConsole() === false) {
            $metadata['session_id'] = session()->getId();
        }

        if (config('plog.capture.request_id', true)) {
            $metadata['request_id'] = app(RequestIdService::class)->getRequestId();
        }

        if (config('plog.capture.endpoint', true)) {
            $metadata['endpoint'] = $this->getEndpoint();
        }

        if (config('plog.capture.file_info', true) || config('plog.capture.class_info', true)) {
            $trace = $this->getRelevantStackTrace();
            if ($trace) {
                if (config('plog.capture.file_info', true)) {
                    $metadata['file'] = $trace['file'] ?? null;
                    $metadata['line'] = $trace['line'] ?? null;
                }
                if (config('plog.capture.class_info', true)) {
                    $metadata['class'] = $trace['class'] ?? null;
                    $metadata['method'] = $trace['method'] ?? null;
                }
            }
        }

        return $metadata;
    }

    protected function getEnvironment()
    {
        if ($this->app->runningInConsole()) {
            if ($this->app->runningUnitTests()) {
                return 'testing';
            }
            if (isset($_SERVER['LARAVEL_QUEUE_WORKER']) || isset($_ENV['LARAVEL_QUEUE_WORKER'])) {
                return 'queue';
            }
            return 'cli';
        }

        return 'http';
    }

    protected function getEndpoint()
    {
        if ($this->app->runningInConsole()) {
            $command = $_SERVER['argv'] ?? [];
            return implode(' ', $command);
        }

        // Get HTTP method and full URI with query parameters
        $method = Request::method();
        $uri = Request::getRequestUri(); // This includes query parameters

        return $method . ' ' . $uri;
    }

    protected function getRelevantStackTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        $vendorKeywords = ['vendor', 'laravel', 'illuminate', 'monolog', 'plog'];
        $fallbackFrame = null;

        foreach ($trace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            // Store the first valid frame as fallback
            if (!$fallbackFrame) {
                $fallbackFrame = $frame;
            }

            $isVendor = false;
            foreach ($vendorKeywords as $keyword) {
                if (stripos($frame['file'], $keyword) !== false) {
                    $isVendor = true;
                    break;
                }
            }

            if (!$isVendor) {
                return [
                    'file' => $frame['file'],
                    'line' => $frame['line'] ?? null,
                    'class' => $frame['class'] ?? 'root',
                    'method' => $frame['function'] ?? 'main',
                ];
            }
        }

        // Fallback to the first frame found if no user code frame is available
        if ($fallbackFrame) {
            return [
                'file' => $fallbackFrame['file'],
                'line' => $fallbackFrame['line'] ?? null,
                'class' => $fallbackFrame['class'] ?? 'root',
                'method' => $fallbackFrame['function'] ?? 'main',
            ];
        }

        // Ultimate fallback - this should rarely happen
        return [
            'file' => 'unknown',
            'line' => null,
            'class' => 'root',
            'method' => 'main',
        ];
    }

    protected function storeRequestData($requestId)
    {
        // Check if request data already exists for this request ID
        if (PlogRequest::where('request_id', $requestId)->exists()) {
            return;
        }

        try {
            $headers = Request::header();
            $body = $this->getRequestBody();
            $queryParams = Request::query();
            $cookies = Request::cookie();

            // Filter sensitive data
            $filteredHeaders = $this->filterSensitiveData($headers);
            $filteredCookies = $this->filterSensitiveData($cookies);

            PlogRequest::create([
                'request_id' => $requestId,
                'method' => Request::method(),
                'url' => Request::fullUrl(),
                'headers' => $filteredHeaders,
                'body' => $body,
                'query_params' => $queryParams,
                'cookies' => $filteredCookies,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if unable to store request data
        }
    }

    protected function getRequestBody()
    {
        try {
            $content = Request::getContent();

            // Try to decode JSON
            if (Request::isJson()) {
                $decoded = json_decode($content, true);
                return $decoded !== null ? $this->filterSensitiveData($decoded) : $content;
            }

            // For form data
            if (Request::isMethod('POST') || Request::isMethod('PUT') || Request::isMethod('PATCH')) {
                return $this->filterSensitiveData(Request::all());
            }

            return $content;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function filterSensitiveData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $sensitiveKeys = [
            'password', 'password_confirmation', 'token', 'secret', 'key',
            'authorization', 'x-api-key', 'x-auth-token', 'cookie',
            'csrf_token', '_token', 'api_key', 'private_key'
        ];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);

            if (in_array($lowerKey, $sensitiveKeys) || str_contains($lowerKey, 'password') || str_contains($lowerKey, 'secret')) {
                $data[$key] = '[FILTERED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->filterSensitiveData($value);
            }
        }

        return $data;
    }

    protected function getCleanStackTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);
        $vendorKeywords = ['vendor', 'laravel', 'illuminate', 'monolog', 'plog'];
        $cleanTrace = [];

        foreach ($trace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            // Skip vendor/framework files
            $isVendor = false;
            foreach ($vendorKeywords as $keyword) {
                if (stripos($frame['file'], $keyword) !== false) {
                    $isVendor = true;
                    break;
                }
            }

            if (!$isVendor) {
                $cleanFrame = [
                    'file' => $frame['file'],
                    'line' => $frame['line'] ?? null,
                    'class' => $frame['class'] ?? null,
                    'method' => $frame['function'] ?? null,
                ];

                // Make file path relative to project root if possible
                if (isset($frame['file']) && strpos($frame['file'], base_path()) === 0) {
                    $cleanFrame['file'] = str_replace(base_path() . '/', '', $frame['file']);
                }

                $cleanTrace[] = $cleanFrame;
            }
        }

        return empty($cleanTrace) ? null : $cleanTrace;
    }
}