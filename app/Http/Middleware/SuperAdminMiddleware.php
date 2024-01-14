<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (User::query()->find(Auth::id())['role'] != 'super_admin') {
            return \response()->json([
                'success' => false,
                'data' => null,
                'message' => 'you do not have the authority to do that'
            ]);
        }
        return $next($request);
    }
}
