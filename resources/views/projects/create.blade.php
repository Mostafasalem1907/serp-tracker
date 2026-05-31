<x-app-layout>
    <x-slot name="title">مشروع جديد</x-slot>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">إضافة مشروع جديد</h1>

        <div class="bg-white rounded-xl shadow p-6">
            <form method="POST" action="{{ route('projects.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">العميل</label>
                    <select name="client_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">— اختر العميل —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم المشروع</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الدومين</label>
                    <input type="text" name="domain" value="{{ old('domain') }}" placeholder="example.com" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الدولة المستهدفة</label>
                    <select name="country_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اللغة</label>
                        <select name="language" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="ar" {{ old('language','ar') === 'ar' ? 'selected' : '' }}>عربي</option>
                            <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>English</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الجهاز</label>
                        <select name="device" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="desktop" {{ old('device','desktop') === 'desktop' ? 'selected' : '' }}>Desktop</option>
                            <option value="mobile" {{ old('device') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">حفظ المشروع</button>
                    <a href="{{ route('projects.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
