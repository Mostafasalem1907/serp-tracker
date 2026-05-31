<x-app-layout>
    <x-slot name="title">الإعدادات</x-slot>

    <h1 class="text-2xl font-bold text-gray-800 mb-6">الإعدادات</h1>

    <div x-data="{ tab: 'general' }">

        {{-- التبويبات --}}
        <div class="flex border-b mb-6">
            @foreach(['general' => 'عام', 'apis' => 'الـ APIs', 'users' => 'المستخدمون', 'countries' => 'الدول'] as $key => $label)
                <button @click="tab='{{ $key }}'"
                        :class="tab==='{{ $key }}' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-500 hover:text-gray-700'"
                        class="px-6 py-3 text-sm transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- تبويب: عام --}}
        <div x-show="tab==='general'" x-cloak>
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl">
                <form method="POST" action="{{ route('settings.general') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اسم التطبيق</label>
                        <input type="text" name="app_name" value="{{ $generalSettings['app_name']?->value ?? 'SERP Rank Tracker' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone الافتراضي</label>
                        <select name="default_timezone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            @foreach(['Africa/Cairo' => 'مصر (Cairo)', 'Asia/Riyadh' => 'السعودية (Riyadh)', 'Africa/Casablanca' => 'المغرب (Casablanca)', 'UTC' => 'UTC'] as $tz => $label)
                                <option value="{{ $tz }}" {{ ($generalSettings['default_timezone']?->value ?? 'Africa/Cairo') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الدولة الافتراضية</label>
                        <select name="default_country_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ ($generalSettings['default_country_id']?->value ?? 1) == $country->id ? 'selected' : '' }}>{{ $country->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الحد الأقصى للكلمات في المشروع</label>
                        <input type="number" name="max_keywords_per_project" min="1" max="100"
                               value="{{ $generalSettings['max_keywords_per_project']?->value ?? 20 }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">وقت الفحص التلقائي (ساعة)</label>
                        <input type="number" name="check_interval_hours" min="1" max="24"
                               value="{{ $generalSettings['check_interval_hours']?->value ?? 12 }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">حفظ الإعدادات</button>
                </form>
            </div>
        </div>

        {{-- تبويب: APIs --}}
        <div x-show="tab==='apis'" x-cloak>
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl">
                <form method="POST" action="{{ route('settings.apis') }}" class="space-y-6">
                    @csrf

                    {{-- DataForSEO --}}
                    <div>
                        <h3 class="font-bold text-gray-700 mb-3">DataForSEO</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                                <input type="text" name="dataforseo_login" placeholder="البريد الإلكتروني في DataForSEO"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="dataforseo_password"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <button type="button" id="testDfSeoBtn" class="text-sm bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                                🔌 اختبار الاتصال
                            </button>
                            <div id="dfSeoResult" class="text-sm hidden"></div>
                        </div>
                    </div>

                    <hr>

                    {{-- Anthropic --}}
                    <div>
                        <h3 class="font-bold text-gray-700 mb-3">Anthropic (Claude AI)</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                            <input type="password" name="anthropic_api_key" placeholder="sk-ant-..."
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <button type="button" id="testAnthropicBtn" class="mt-2 text-sm bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                            🔌 اختبار الـ Key
                        </button>
                        <div id="anthropicResult" class="text-sm mt-1 hidden"></div>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">حفظ الـ API Keys</button>
                </form>
            </div>
        </div>

        {{-- تبويب: المستخدمون --}}
        <div x-show="tab==='users'" x-cloak>

            {{-- إضافة مستخدم جديد --}}
            <div class="bg-white rounded-xl shadow p-6 max-w-2xl mb-6">
                <h3 class="font-bold text-gray-700 mb-4">إضافة مستخدم جديد</h3>
                <form method="POST" action="{{ route('settings.users.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الاسم</label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">البريد</label>
                            <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
                            <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تأكيد</label>
                            <input type="password" name="password_confirmation" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الدور</label>
                            <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="member">عضو</option>
                                <option value="client">عميل</option>
                                <option value="admin">مدير</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ربط بعميل (للنوع client)</label>
                            <select name="client_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">— بدون ربط —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">إضافة المستخدم</button>
                </form>
            </div>

            {{-- قائمة المستخدمين --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b text-gray-600">
                        <tr>
                            <th class="text-right px-4 py-3 font-medium">الاسم</th>
                            <th class="text-right px-4 py-3 font-medium">البريد</th>
                            <th class="text-right px-4 py-3 font-medium">الدور</th>
                            <th class="text-right px-4 py-3 font-medium">العميل</th>
                            <th class="text-right px-4 py-3 font-medium">الحالة</th>
                            <th class="text-right px-4 py-3 font-medium">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs px-2 py-1 rounded-full {{ ['admin'=>'bg-red-100 text-red-700','member'=>'bg-blue-100 text-blue-700','client'=>'bg-green-100 text-green-700'][$user->role] }}">
                                        {{ ['admin'=>'مدير','member'=>'عضو','client'=>'عميل'][$user->role] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs {{ $user->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $user->is_active ? 'نشط' : 'متوقف' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('settings.users.update', $user) }}" class="inline" x-data="{ active: {{ $user->is_active ? 'true' : 'false' }} }">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="name" value="{{ $user->name }}">
                                            <input type="hidden" name="role" value="{{ $user->role }}">
                                            <input type="hidden" name="is_active" :value="active ? 0 : 1">
                                            <button type="submit" @click="active=!active"
                                                    class="text-xs px-2 py-1 rounded"
                                                    :class="active ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-green-100 text-green-600 hover:bg-green-200'">
                                                <span x-text="active ? 'تعطيل' : 'تفعيل'"></span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">أنت</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- تبويب: الدول --}}
        <div x-show="tab==='countries'" x-cloak>
            <div class="grid grid-cols-2 gap-6">

                {{-- إضافة دولة جديدة --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-bold text-gray-700 mb-4">إضافة دولة جديدة</h3>
                    <form method="POST" action="{{ route('settings.countries.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الاسم بالعربي</label>
                            <input type="text" name="name_ar" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الاسم بالإنجليزي</label>
                            <input type="text" name="name_en" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location Code (DataForSEO)</label>
                            <input type="text" name="location_code" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <input type="text" name="timezone" placeholder="Africa/Cairo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SE Domain</label>
                            <input type="text" name="se_domain" placeholder="google.com.eg" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">إضافة الدولة</button>
                    </form>
                </div>

                {{-- قائمة الدول --}}
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b text-gray-600">
                            <tr>
                                <th class="text-right px-4 py-3 font-medium">الدولة</th>
                                <th class="text-right px-4 py-3 font-medium">Location Code</th>
                                <th class="text-right px-4 py-3 font-medium">Timezone</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($countries as $country)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $country->name_ar }}</div>
                                        <div class="text-xs text-gray-400">{{ $country->se_domain }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs">{{ $country->location_code }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $country->timezone }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
// اختبار DataForSEO
document.getElementById('testDfSeoBtn').addEventListener('click', async () => {
    const result = document.getElementById('dfSeoResult');
    result.className = 'text-sm text-gray-500';
    result.textContent = 'جاري الاختبار...';
    result.classList.remove('hidden');

    const login    = document.querySelector('[name=dataforseo_login]').value;
    const password = document.querySelector('[name=dataforseo_password]').value;

    const resp = await fetch('{{ route("settings.test-dataforseo") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ login, password }),
    });
    const data = await resp.json();
    result.className = 'text-sm ' + (data.success ? 'text-green-600' : 'text-red-600');
    result.textContent = data.message;
});

// اختبار Anthropic
document.getElementById('testAnthropicBtn').addEventListener('click', async () => {
    const result  = document.getElementById('anthropicResult');
    result.className = 'text-sm text-gray-500';
    result.textContent = 'جاري الاختبار...';
    result.classList.remove('hidden');

    const api_key = document.querySelector('[name=anthropic_api_key]').value;

    const resp = await fetch('{{ route("settings.test-anthropic") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ api_key }),
    });
    const data = await resp.json();
    result.className = 'text-sm ' + (data.success ? 'text-green-600' : 'text-red-600');
    result.textContent = data.message;
});
</script>
</x-app-layout>
