<?php

return [
    /*
     * إعدادات خاصة بتطبيق SERP Rank Tracker
     * القيم الحساسة تُقرأ من جدول settings وليس من هنا
     */

    // مسار ملف install.lock
    'install_lock_path' => storage_path('install.lock'),

    // الحد الأقصى الافتراضي لعدد الكلمات في المشروع الواحد
    'default_max_keywords' => 20,

    // الفترة الافتراضية للفحص التلقائي بالساعات
    'default_check_interval' => 12,

    // عدد أيام الاحتفاظ بنتائج الفحص
    'rank_history_days' => 90,
];
