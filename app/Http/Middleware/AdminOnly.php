<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * السماح بالوصول للـ admin فقط
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
