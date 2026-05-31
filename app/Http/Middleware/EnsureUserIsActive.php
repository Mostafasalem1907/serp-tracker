<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * التحقق من أن المستخدم لا يزال مفعلاً
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_active) {
            auth()->logout();
            return redirect('/login')->withErrors(['email' => 'تم تعطيل حسابك. تواصل مع المدير.']);
        }

        return $next($request);
    }
}
