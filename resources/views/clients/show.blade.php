<x-app-layout>
    <x-slot name="title">{{ $client->name }}</x-slot>

    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="text-sm text-gray-500 mb-1"><a href="{{ route('clients.index') }}" class="hover:text-blue-600">العملاء</a> / {{ $client->name }}</div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $client->name }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $client->email }} {{ $client->phone ? '| '.$client->phone : '' }}</p>
        </div>
        <a href="{{ route('clients.edit', $client) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm">تعديل</a>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2">
            <h2 class="font-bold text-gray-700 mb-3">المشاريع ({{ $client->projects->count() }})</h2>
            @forelse($client->projects as $project)
                <div class="bg-white rounded-xl shadow p-4 mb-3 flex justify-between items-center">
                    <div>
                        <div class="font-medium text-gray-800">{{ $project->name }}</div>
                        <div class="text-xs text-gray-500">{{ $project->domain }} | {{ $project->country->name_ar }}</div>
                    </div>
                    <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline text-sm">عرض</a>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">لا توجد مشاريع</div>
            @endforelse
        </div>

        <div>
            <h2 class="font-bold text-gray-700 mb-3">سجل الأنشطة</h2>
            <div class="bg-white rounded-xl shadow p-4">
                @forelse($activityLogs as $log)
                    <div class="text-xs border-b py-2 last:border-0">
                        <div class="font-medium text-gray-700">{{ $log->action }}</div>
                        <div class="text-gray-400">{{ $log->user?->name ?? 'النظام' }} — {{ $log->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">لا توجد أنشطة</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
