<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\LoaRequest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartmentScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (! $user->role->is(UserRole::DepartmentHead)) {
            return $next($request);
        }

        $loa = $request->route('loaRequest');

        if (! $loa) {
            return $next($request);
        }

        if ($loa instanceof LoaRequest
            && $user->department_id
            && (int) $loa->department_id === (int) $user->department_id) {
            return $next($request);
        }

        abort(403, 'You may only access LOA requests from your own department.');
    }
}
