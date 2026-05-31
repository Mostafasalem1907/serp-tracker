<x-app-layout>
    <x-slot name="title">تاريخ التحليلات — {{ $project->name }}</x-slot>

    <div class="mb-6">
        <div class="text-sm text-gray-500 mb-1">
            <a href="{{ route('projects.show', $project) }}" class="hover:text-blue-600">{{ $project->name }}</a> / تحليلات AI
        </div>
        <h1 class="text-2xl font-bold text-gray-800">تاريخ تحليلات الذكاء الاصطناعي</h1>
    </div>

    @forelse($analyses as $analysis)
        <div class="bg-white rounded-xl shadow mb-4 overflow-hidden">
            <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-b">
                <div class="text-sm">
                    <span class="font-medium text-gray-700">{{ $analysis->user->name }}</span>
                    <span class="text-gray-400 mx-2">|</span>
                    <span class="text-gray-500">{{ $analysis->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <span class="text-xs text-gray-400">
                    {{ number_format($analysis->tokens_input) }} input + {{ number_format($analysis->tokens_output) }} output tokens
                </span>
            </div>
            <div class="px-6 py-4">
                <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed max-h-64 overflow-y-auto">
                    {{ $analysis->response }}
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-16 bg-white rounded-xl shadow">
            <div class="text-6xl mb-4">🤖</div>
            <p class="text-gray-500">لا توجد تحليلات بعد</p>
            <a href="{{ route('projects.show', $project) }}" class="mt-4 inline-block text-blue-600 hover:underline">
                اذهب للمشروع لتشغيل تحليل جديد
            </a>
        </div>
    @endforelse

    <div class="mt-4">{{ $analyses->links() }}</div>
</x-app-layout>
