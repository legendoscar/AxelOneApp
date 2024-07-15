<?php

namespace Modules\UserManagement\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserBelongsToAnyOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (is_null($user->organization_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sorry, the org ID is missing. Switch into a business account to proceed.'], 403);
        }

        return $next($request);
    }
}
