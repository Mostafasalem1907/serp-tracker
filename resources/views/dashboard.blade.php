<x-app-layout>
    <x-slot name="title">لوحة التحكم</x-slot>

    @if($stats)
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow p-6 text-center border-r-4 border-blue-500">
                <div class="text-3xl font-bold text-blue-600">{{ $stats['clients_count'] }}</div>
                <div class="text-gray-500 mt-1">إجمالي العملاء</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center border-r-4 border-green-500">
                <div class="text-3xl font-bold text-green-600">{{ $stats['projects_count'] }}</div>
                <div class="text-gray-500 mt-1">المشاريع النشطة</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 text-center border-r-4 border-purple-500">
                <div class="text-3xl font-bold text-purple-600">{{ $stats['keywords_count'] }}</div>
                <div class="text-gray-500 mt-1">الكلمات المفتاحية</div>
            </div>
        </div>
    @endif

    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">المشاريع</h2>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">+ مشروع جديد</a>
        @endif
    </div>

    @forelse($projects as $project)
        <div class="bg-white rounded-xl shadow mb-4 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-b">
                <div>
                    <h3 class="font-bold text-lg text-gray-800">{{ $project->name }}</h3>
                    <span class="text-sm text-gray-500">{{ $project->domain }} | {{ $project->client->name }} | {{ $project->country->name_ar }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400">{{ $project->statsToday['checked_today'] }}/{{ $project->statsToday['total_keywords'] }} مفحوص اليوم</span>
                    <a href="{{ route('projects.show', $project) }}" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition">عرض التفاصيل</a>
                </div>
            </div>
            @if(count($project->topKeywords) > 0)
                <div class="px-6 py-4">
                    <div class="grid grid-cols-5 gap-3">
                        @foreach($project->topKeywords as $kw)
                            <div class="text-center p-3 bg-gray-50 rounded-lg border">
                                <div class="text-2xl font-bold {{ ($kw['rank'] ?? 999) <= 10 ? 'text-green-600' : (($kw['rank'] ?? 999) <= 30 ? 'text-yellow-600' : 'text-red-500') }}">
                                    {{ $kw['rank'] ?? '—' }}
                                </div>
                                @if($kw['change'] !== null)
                                    <div class="text-xs {{ $kw['trend']==='up' ? 'text-green-500' : ($kw['trend']==='down' ? 'text-red-500' : 'text-gray-400') }}">
                                        @if($kw['trend']==='up') ▲ {{ $kw['change'] }}
                                        @elseif($kw['trend']==='down') ▼ {{ abs($kw['change']) }}
                                        @else — @endif
                                    </div>
                                @endif
                                <div class="text-xs text-gray-600 mt-1 truncate" title="{{ $kw['keyword'] }}">{{ Str::limit($kw['keyword'], 15) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="px-6 py-4 text-center text-gray-400 text-sm">
                    لم يتم فحص أي كلمات بعد — <a href="{{ route('projects.show', $project) }}" class="text-blue-500 hover:underline">ابدأ الفحص</a>
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-16 bg-white rounded-xl shadow">
            <div class="text-6xl mb-4">📋</div>
            <p class="text-gray-500 text-lg">لا توجد مشاريع بعد</p>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('projects.create') }}" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">أضف أول مشروع</a>
            @endif
        </div>
    @endforelse
</x-app-layout>
