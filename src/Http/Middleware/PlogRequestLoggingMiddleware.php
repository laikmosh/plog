<?php

namespace Laikmosh\Plog\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlogRequestLoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $this->logRequest($request, $response, $startTime);

        return $response;
    }

    protected function logRequest(Request $request, $response, $startTime)
    {
        $status = $response->getStatusCode();

        if ($status >= 200) {
            $duration = microtime(true) - $startTime;
            $url = $request->fullUrl();
            $method = $request->method();

            // Use the Laravel logger (which Plog intercepts)
            // But we need to ensure we pass the custom context so PlogHandler can pick it up
            // However, PlogHandler intercepts Log::error/info calls.
            // We want to log a specific "Request" level if possible, but Laravel Log facade 
            // usually maps to specific methods (debug, info, error, etc).
            // PlogHandler implementation: public function handle($level, $message, array $context = [])
            
            // To support custom level "request", we might need to call the handler directly 
            // OR use a macro if available. 
            // Looking at PlogHandler::handle, it takes $level as a string.
            // But standard Log facade doesn't support arbitrary levels easily via static methods.
            // We can use Log::log($level, $message, $context).

            $level = 'error'; // Default to error for >= 400, but user asked for "Request" level.
             // If we use "Request" as level, standard Monolog/Laravel channels might assume it's a standard level or fail.
             // However, PlogHandler seems to handle any string level in its handle method, 
             // but `Log::channel('single')->log('Request', ...)` might not work if 'Request' isn't a standard Monolog level.
             // 
             // Wait, PlogHandler.php line 43: $levelUpper = strtoupper($level);
             // It seems to just print it.
             // 
             // Let's try leveraging the PlogHandler directly if we want a custom "Request" level
             // to avoid Monolog issues, OR use 'info' and use a tag 'Request'.
             // 
             // User Request: "the log level should be called: 'Request'"
             // 
             // If I use `Log::log('Request', ...)` Laravel generally converts it to a standard level 
             // or throws error if invalid for Monolog.
             // But let's check PlogServiceProvider. It overrides logging config to use PlogCustomLogger.
             
            // Let's look at PlogCustomLogger to see if it allows arbitrary levels. 
            // If not, I might have to instantiate the PlogHandler directly.
            
            // For now, I will use `app('plog.handler')->handle(...)` directly to bypass Monolog level restrictions
            // and ensure "Request" is used as the level string.

            app('plog.handler')->handle(
                'Request', 
                $url, 
                [   
                    'response_time' => $duration, // This will be cast to float by PlogEntry
                    '_tags' => ['response_code:' . $status],
                    'method' => $method,
                    'status' => $status
                ]
            );
        }
    }
}
