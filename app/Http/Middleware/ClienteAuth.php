<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClienteAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('cliente_id')) {
            return redirect()->route('vitrine.auth');
        }

        return $next($request);
    }
}
