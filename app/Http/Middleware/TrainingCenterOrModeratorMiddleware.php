<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingCenterOrModeratorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (Auth::user()->role_id == Role::TRAINING_CENTER || Auth::user()->role_id == Role::MODERATOR|| Auth::user()->role_id == Role::TRAINER) {
            return $next($request);
        }
        return response()->json([
            'success' => false,
            'message' => __("messages.forbidden"),
        ], 403)->header('Status-Code', 403);
    }
}
