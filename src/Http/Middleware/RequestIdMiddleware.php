<?php

namespace Laikmosh\Plog\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laikmosh\Plog\Services\RequestIdService;

class RequestIdMiddleware
{
    protected $requestIdService;

    public function __construct(RequestIdService $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    public function handle(Request $request, Closure $next)
    {
        $requestId = $request->header('X-Request-ID');

        if (!$requestId) {
            $requestId = $this->requestIdService->generateRequestId();
        } else {
            $this->requestIdService->setRequestId($requestId);
        }

        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-Request-ID', $requestId);
        }

        return $response;
    }
}