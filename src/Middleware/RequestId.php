<?php

namespace Cerpus\Helper\Middleware;

use Closure;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

/**
 * Receive and respond with a request Id in the header.
 * Class RequestId
 * @package Cerpus\Helper\Middleware
 */
class RequestId
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headerName = 'X-Request-Id';
        $requestId = $request->header($headerName, Uuid::uuid4());

        // If the requestid is not a UUID, generate a new one.
        if (!Uuid::isValid($requestId)) {
            $requestId = Uuid::uuid4();
        }

        app()->singleton('requestId', function ($app) use ($requestId) {
            return $requestId;
        });

        /** @var Response $response */
        $response = $next($request);

        // Some response types is missing the ->header() method.
        // A file download will for instance not get the X-Request-Id header added to the response.
        if (method_exists($response, 'header')) {
            $response->header($headerName, $requestId);
        }

        return $response;
    }
}
