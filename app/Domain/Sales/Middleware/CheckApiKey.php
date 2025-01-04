<?php

namespace App\Domain\Sales\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $validApiKey = config('services.api.key');
        
        if (!$request->header('X-API-Key') || $request->header('X-API-Key') !== $validApiKey) {
            return response()->json([
                'error' => 'Unauthorized. Invalid API key.',
            ], 401);
        }

        return $next($request);
    }
}