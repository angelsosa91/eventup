<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Si el usuario no está autenticado, redirigir al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super admin tiene acceso a todo
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso
        if (!$user->hasPermission($permission)) {
            // Si es una petición AJAX, devolver error JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción.'
                ], 403);
            }

            // Si es una petición normal, redirigir con mensaje
            return redirect()->back()->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
