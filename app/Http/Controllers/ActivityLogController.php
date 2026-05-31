<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * عرض سجل الأنشطة الكامل — Admin فقط
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        // فلتر بالمستخدم
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // فلتر بنوع النشاط
        if ($request->filled('action')) {
            $query->where('action', 'like', $request->action . '%');
        }

        // فلتر بنوع الكيان
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // فلتر بالتاريخ من
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // فلتر بالتاريخ إلى
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs  = $query->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        // أنواع الأنشطة المتاحة للفلترة
        $actionGroups = [
            'client'   => 'العملاء',
            'project'  => 'المشاريع',
            'keyword'  => 'الكلمات المفتاحية',
            'rank'     => 'الفحص',
            'ai'       => 'الذكاء الاصطناعي',
            'settings' => 'الإعدادات',
            'user'     => 'المستخدمون',
            'api_key'  => 'مفاتيح الـ API',
        ];

        return view('activity-log.index', compact('logs', 'users', 'actionGroups'));
    }
}
