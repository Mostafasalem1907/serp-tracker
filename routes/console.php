<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * جدولة المهام التلقائية
 * ملاحظة: في cPanel يُستخدم Cron Job مباشرة بدلاً من Laravel Scheduler
 * الأوامر الموضحة هنا للمرجع فقط
 *
 * Cron 1: 0 *\/12 * * * → php artisan ranks:check-all
 * Cron 2: * * * * *   → php artisan queue:work --stop-when-empty --tries=3 --timeout=60
 */
