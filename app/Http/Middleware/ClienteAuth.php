<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClienteAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $clienteId = session('cliente_id');
        if (!$clienteId) {
            return redirect()->route('vitrine.auth');
        }

        $cliente = \App\Models\Cliente::find($clienteId);
        if (!$cliente || !$cliente->ativo) {
            session()->forget(['cliente_id', 'cliente_nome']);
            return redirect()->route('vitrine.auth');
        }

        return $next($request);
    }
}
