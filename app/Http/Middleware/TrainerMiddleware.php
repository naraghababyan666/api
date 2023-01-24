<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::user()->role_id == Role::TRAINER || Auth::user()->role_id == Role::TRAINING_CENTER || Auth::user()->role_id == Role::STUDENT){
            return $next($request);
        }
        return response()->json(['success' => false, 'message' => 'Permission denied!']);
    }
}
