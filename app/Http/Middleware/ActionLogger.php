<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActionLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        
           $requestData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'time' => now()->toDateTimeString(),
            'request_body' => $request->all(),
        ];
        $operationType = $this->getOperationType($request);
        $endpoint = $this->getEndpoint($request);
        Log::info($endpoint, $requestData);
        return $next($request);
    }
    private function getEndpoint($request)
    {
        // Get the URL path
        $path = parse_url($request->url(), PHP_URL_PATH);
    
        // Extract the endpoint from the URL path
        $endpoint = explode('/', $path);
        $endpoint = end($endpoint);
    
        return $endpoint;
    }
    private function getOperationType($request)
    {
        // Check the HTTP method to determine the operation type
        switch ($request->method()) {
            case 'POST':
                return 'inserted';
            case 'PUT':
            case 'PATCH':
                return 'updated';
            case 'DELETE':
                return 'deleted';
            default:
                return 'performed';
        }
    }

}
