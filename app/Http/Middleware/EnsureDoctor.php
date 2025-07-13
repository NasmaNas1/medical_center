<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\GeneralTrait;

class EnsureDoctor
{    
        use GeneralTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {  
        if (!$request->user() || !$request->user() instanceof \App\Models\Doctor) {
        return $this->forbiddenResponse(); }
        return $next($request);
    }
}
