<x-app-layout>
    <x-slot name="title">سجل الأنشطة</x-slot>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">سجل الأنشطة</h1>
    </div>

    {{-- فلاتر البحث --}}
    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <form method="GET" action="{{ route('activity-log.index') }}" class="grid grid-cols-4 gap-3">
            <select name="user_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">— كل المستخدمين —</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="action" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">— كل الأنشطة —</option>
                @foreach($actionGroups as $key => $label)
                    <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">بحث</button>
                <a href="{{ route('activity-log.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-lg text-sm py-2 hover:bg-gray-200 transition">إعادة</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 border-b">
                <tr>
                    <th class="text-right px-4 py-3 font-medium">النشاط</th>
                    <th class="text-right px-4 py-3 font-medium">المستخدم</th>
                    <th class="text-right px-4 py-3 font-medium">الكيان</th>
                    <th class="text-right px-4 py-3 font-medium">ملاحظات</th>
                    <th class="text-right px-4 py-3 font-medium">الوقت</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->user?->name ?? '⚙️ النظام' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            @if($log->model_type)
                                {{ $log->model_type }} #{{ $log->model_id }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $log->notes ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs" title="{{ $log->created_at }}">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-gray-400">لا توجد أنشطة</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
