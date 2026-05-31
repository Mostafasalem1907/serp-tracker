<?php

namespace App\Providers;

use App\Http\View\SidebarComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // تمرير بيانات الـ Sidebar (عملاء + مشاريع) لكل الـ views
        View::composer('*', SidebarComposer::class);
    }
}
