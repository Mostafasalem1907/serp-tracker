<x-app-layout>
    <x-slot name="title">عميل جديد</x-slot>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">إضافة عميل جديد</h1>
        <div class="bg-white rounded-xl shadow p-6">
            <form method="POST" action="{{ route('clients.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم العميل <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes') }}</textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">حفظ العميل</button>
                    <a href="{{ route('clients.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
