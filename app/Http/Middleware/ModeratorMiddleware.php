<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModeratorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $moderator = Role::where("title", Role::MODERATOR)->first();
        $user = Auth::user();
        if ($user['role_id'] === $moderator['id']) {
            return $next($request);
        }
        return response()->json([
            'success' => false,
            'message' => __("messages.forbidden"),
        ], 403)->header('Status-Code', 403);
    }
}
