<x-guest-layout>
    <h2 class="text-xl font-bold text-gray-700 mb-6 text-center">تسجيل الدخول</h2>

    @if(session('status'))
        <div class="mb-4 text-sm text-green-600 bg-green-50 p-3 rounded-lg">{{ session('status') }}</div>
    @endif

    @if(session('success'))
        <div class="mb-4 text-sm text-green-600 bg-green-50 p-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300">
                تذكرني
            </label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">نسيت كلمة المرور؟</a>
            @endif
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-bold hover:bg-blue-700 transition">
            دخول
        </button>
    </form>
</x-guest-layout>
