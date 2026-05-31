<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * التحقق من إكمال الـ First-Run Wizard
     * لو install.lock غير موجود → redirect لـ /install
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lockFile = storage_path('install.lock');

        // لو التطبيق غير مُهيَّأ بعد → إجبار المستخدم على الـ wizard
        if (!file_exists($lockFile)) {
            // السماح بالوصول لمسارات الـ wizard فقط
            if (!$request->is('install*')) {
                return redirect('/install');
            }
        } else {
            // التطبيق مُهيَّأ → منع الوصول لمسارات الـ wizard
            if ($request->is('install*')) {
                return redirect('/login');
            }
        }

        return $next($request);
    }
}
