<x-app-layout>
    <x-slot name="title">المشاريع</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">المشاريع</h1>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">+ مشروع جديد</a>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 border-b">
                <tr>
                    <th class="text-right px-4 py-3 font-medium">المشروع</th>
                    <th class="text-right px-4 py-3 font-medium">العميل</th>
                    <th class="text-right px-4 py-3 font-medium">الدولة</th>
                    <th class="text-right px-4 py-3 font-medium">الكلمات</th>
                    <th class="text-right px-4 py-3 font-medium">الحالة</th>
                    <th class="text-right px-4 py-3 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $project->name }}</div>
                            <div class="text-xs text-gray-400">{{ $project->domain }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->client->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->country->name_ar }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->keywords_count }}</td>
                        <td class="px-4 py-3">
                            @if($project->is_active)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">نشط</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">متوقف</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline text-xs ml-2">عرض</a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('projects.edit', $project) }}" class="text-yellow-600 hover:underline text-xs ml-2">تعديل</a>
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('حذف هذا المشروع؟')" class="text-red-600 hover:underline text-xs">حذف</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">لا توجد مشاريع</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $projects->links() }}</div>
    </div>
</x-app-layout>
