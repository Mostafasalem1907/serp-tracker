<x-app-layout>
    <x-slot name="title">العملاء</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">العملاء</h1>
        <a href="{{ route('clients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">+ عميل جديد</a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 border-b">
                <tr>
                    <th class="text-right px-4 py-3 font-medium">الاسم</th>
                    <th class="text-right px-4 py-3 font-medium">البريد</th>
                    <th class="text-right px-4 py-3 font-medium">الهاتف</th>
                    <th class="text-right px-4 py-3 font-medium">المشاريع</th>
                    <th class="text-right px-4 py-3 font-medium">الحالة</th>
                    <th class="text-right px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($clients as $client)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $client->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->projects_count }}</td>
                        <td class="px-4 py-3">
                            @if($client->is_active)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">نشط</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">متوقف</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('clients.show', $client) }}" class="text-blue-600 hover:underline text-xs ml-2">عرض</a>
                            <a href="{{ route('clients.edit', $client) }}" class="text-yellow-600 hover:underline text-xs ml-2">تعديل</a>
                            <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('حذف هذا العميل؟')" class="text-red-600 hover:underline text-xs">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">لا يوجد عملاء بعد</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $clients->links() }}</div>
    </div>
</x-app-layout>
