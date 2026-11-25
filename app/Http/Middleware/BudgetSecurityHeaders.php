<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BudgetSecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response=$next($request);
        $response->headers->set('Cache-Control','no-store, private');
        $response->headers->set('X-Content-Type-Options','nosniff');
        $response->headers->set('X-Frame-Options','DENY');
        $response->headers->set('Referrer-Policy','no-referrer');
        $response->headers->set('Content-Security-Policy',"default-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
        return $response;
    }
}
