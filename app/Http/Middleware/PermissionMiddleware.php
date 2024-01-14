<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $permission
     * @return Response
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = User::query()->find(Auth::id());
        if ($user['role'] == 'super_admin') {
            return $next($request);
        }

        $permissions = $user['permissions'];
        if ($permissions) {
            foreach ($permissions as $per) {
                if ($per['type'] == $permission)
                    return $next($request);
            }
        }
        return \response()->json([
            'message' => 'you do not have the authority to do that',
            'success' => false,
            'date' => null
        ]);
    }
}
