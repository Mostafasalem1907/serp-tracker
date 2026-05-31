<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserTimezone
{
    /**
     * ضبط timezone التطبيق بناءً على إعداد المستخدم الحالي
     * الـ timestamps في DB بـ UTC — التحويل يتم هنا في كل request
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->timezone) {
            config(['app.timezone' => auth()->user()->timezone]);
            date_default_timezone_set(auth()->user()->timezone);
        }

        return $next($request);
    }
}
