<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تثبيت التطبيق — الخطوة 1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8">

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-700">📊 SERP Rank Tracker</h1>
        <p class="text-gray-500 mt-2">معالج التثبيت — الخطوة 1 من 2</p>
        <div class="flex justify-center gap-2 mt-3">
            <span class="w-8 h-2 rounded-full bg-blue-600"></span>
            <span class="w-8 h-2 rounded-full bg-gray-200"></span>
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4 text-gray-700">إعداد قاعدة البيانات</h2>

    @if($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('install.save-step1') }}">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DB Host</label>
                    <input type="text" name="db_host" value="{{ old('db_host', 'localhost') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DB Port</label>
                    <input type="text" name="db_port" value="{{ old('db_port', '3306') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">اسم قاعدة البيانات</label>
                <input type="text" name="db_database" value="{{ old('db_database') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">اسم المستخدم</label>
                <input type="text" name="db_username" value="{{ old('db_username', 'root') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
                <input type="password" name="db_password" value="{{ old('db_password') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        {{-- زرار Test Connection --}}
        <div class="mt-4">
            <button type="button" id="testBtn"
                    class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                🔌 اختبار الاتصال
            </button>
            <div id="testResult" class="mt-2 text-sm text-center hidden"></div>
        </div>

        <button type="submit" class="w-full mt-4 bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition">
            التالي ←
        </button>
    </form>
</div>

<script>
document.getElementById('testBtn').addEventListener('click', async function() {
    const form   = this.closest('form') || document.querySelector('form');
    const result = document.getElementById('testResult');
    result.className = 'mt-2 text-sm text-center text-gray-500';
    result.textContent = 'جاري الاختبار...';
    result.classList.remove('hidden');

    const data = {
        db_host:     document.querySelector('[name=db_host]').value,
        db_database: document.querySelector('[name=db_database]').value,
        db_username: document.querySelector('[name=db_username]').value,
        db_password: document.querySelector('[name=db_password]').value,
        _token:      document.querySelector('[name=_token]').value,
    };

    try {
        const resp = await fetch('{{ route("install.test-database") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data._token },
            body: JSON.stringify(data),
        });
        const json = await resp.json();
        result.className = 'mt-2 text-sm text-center ' + (json.success ? 'text-green-600' : 'text-red-600');
        result.textContent = json.message;
    } catch(e) {
        result.className = 'mt-2 text-sm text-center text-red-600';
        result.textContent = 'حدث خطأ أثناء الاختبار';
    }
});
</script>
</body>
</html>
