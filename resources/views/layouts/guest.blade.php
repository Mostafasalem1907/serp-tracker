<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SERP Rank Tracker' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Cairo','sans-serif'] } } } }</script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
    <div class="mb-6 text-center">
        <h1 class="text-3xl font-bold text-blue-700">📊 SERP Rank Tracker</h1>
        <p class="text-gray-500 mt-1 text-sm">نظام تتبع ترتيب الكلمات المفتاحية</p>
    </div>
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        {{ $slot }}
    </div>
</body>
</html>
