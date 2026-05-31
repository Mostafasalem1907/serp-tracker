<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تثبيت التطبيق — الخطوة 2</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-8">
<div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8">

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-700">📊 SERP Rank Tracker</h1>
        <p class="text-gray-500 mt-2">معالج التثبيت — الخطوة 2 من 2</p>
        <div class="flex justify-center gap-2 mt-3">
            <span class="w-8 h-2 rounded-full bg-blue-300"></span>
            <span class="w-8 h-2 rounded-full bg-blue-600"></span>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('install.complete') }}">
        @csrf

        {{-- بيانات حساب المدير --}}
        <h3 class="font-bold text-gray-700 mb-3">حساب المدير الرئيسي</h3>
        <div class="space-y-3 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
                <input type="password" name="admin_password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">تأكيد كلمة المرور</label>
                <input type="password" name="admin_password_confirmation" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        {{-- إعدادات DataForSEO --}}
        <h3 class="font-bold text-gray-700 mb-3">DataForSEO API <span class="text-gray-400 font-normal text-sm">(اختياري — يمكن إضافته لاحقاً)</span></h3>
        <div class="space-y-3 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Login (البريد الإلكتروني)</label>
                <input type="text" name="dataforseo_login" value="{{ old('dataforseo_login') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="dataforseo_password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        {{-- Anthropic API Key --}}
        <h3 class="font-bold text-gray-700 mb-3">Anthropic API Key <span class="text-gray-400 font-normal text-sm">(اختياري)</span></h3>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
            <input type="password" name="anthropic_api_key"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
        </div>

        <button type="submit" id="submitBtn"
                class="w-full bg-green-600 text-white py-3 rounded-lg font-bold hover:bg-green-700 transition">
            🚀 إتمام التثبيت
        </button>
    </form>
</div>

<script>
// إظهار loading عند الإرسال لأن migrate قد يأخذ وقتاً
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('submitBtn').textContent = '⏳ جاري التثبيت...';
    document.getElementById('submitBtn').disabled = true;
});
</script>
</body>
</html>
