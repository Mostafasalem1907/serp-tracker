<x-app-layout>
    <x-slot name="title">{{ $project->name }}</x-slot>

    {{-- رأس الصفحة --}}
    <div class="flex justify-between items-start mb-5">
        <div>
            <div class="text-xs text-gray-400 mb-1">
                <a href="{{ route('projects.index') }}" class="hover:text-blue-600">المشاريع</a>
                <span class="mx-1">/</span>
                <span class="text-gray-600">{{ $project->name }}</span>
            </div>
            <h1 class="text-xl font-bold text-gray-800">{{ $project->name }}</h1>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $project->domain }}</span>
                <span class="text-xs text-gray-500">{{ $project->client->name }}</span>
                <span class="text-xs text-gray-400">|</span>
                <span class="text-xs text-gray-500">{{ $project->country->name_ar }}</span>
                <span class="text-xs text-gray-400">|</span>
                <span class="text-xs text-gray-500">{{ $project->device === 'desktop' ? '🖥️ Desktop' : '📱 Mobile' }}</span>
                <span class="text-xs text-gray-400">|</span>
                <span class="text-xs text-gray-500">{{ $project->language === 'ar' ? '🇸🇦 عربي' : '🇬🇧 English' }}</span>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap justify-end">
            @if(auth()->user()->isAdmin() || auth()->user()->isMember())
                <form method="POST" action="{{ route('keywords.check-all', $project) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1 bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium">
                        🔍 فحص الكل
                    </button>
                </form>
            @endif
            <button onclick="runAiAnalysis({{ $project->id }})"
                    class="flex items-center gap-1 bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                🤖 تحليل AI
            </button>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('projects.edit', $project) }}"
                   class="flex items-center gap-1 bg-gray-100 text-gray-600 px-3 py-2 rounded-lg hover:bg-gray-200 transition text-sm">
                    ✏️ تعديل
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-3 gap-5">

        {{-- ============ العمود الرئيسي: الكلمات ============ --}}
        <div class="col-span-2 space-y-4">

            {{-- إضافة كلمة --}}
            @if(auth()->user()->isAdmin())
                <div class="bg-white rounded-xl shadow-sm border p-3" x-data="{ open: false }">
                    <button @click="open=!open"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 w-full">
                        <span x-text="open ? '✕ إغلاق' : '+ إضافة كلمة مفتاحية جديدة'"></span>
                    </button>
                    <div x-show="open" x-cloak class="mt-3">
                        <form method="POST" action="{{ route('keywords.store', $project) }}"
                              class="flex gap-2 items-center">
                            @csrf
                            <input type="text" name="keyword" placeholder="أدخل الكلمة المفتاحية..." required
                                   autofocus
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">

                            {{-- اختيار الجهاز — الافتراضي يرث من المشروع --}}
                            <select name="device"
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="">{{ $project->device === 'mobile' ? '📱 Mobile (افتراضي)' : '🖥️ Desktop (افتراضي)' }}</option>
                                <option value="desktop">🖥️ Desktop</option>
                                <option value="mobile">📱 Mobile</option>
                            </select>

                            <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition font-medium whitespace-nowrap">
                                إضافة
                            </button>
                        </form>
                        <p class="text-xs text-gray-400 mt-1.5">
                            💡 اتركه على الافتراضي أو اختر جهاز مختلف لنفس الكلمة
                        </p>
                    </div>
                </div>
            @endif

            {{-- جدول الكلمات --}}
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden"
                 x-data="{
                    search:  '',
                    device:  'all',
                    rank:    'all',
                    trend:   'all',
                    kd:      'all',
                    get activeCount() {
                        return [this.search, this.device !== 'all', this.rank !== 'all',
                                this.trend !== 'all', this.kd !== 'all']
                               .filter(Boolean).length;
                    },
                    reset() { this.search=''; this.device='all'; this.rank='all'; this.trend='all'; this.kd='all'; },
                    matches(row) {
                        const kw     = (row.dataset.keyword  || '').toLowerCase();
                        const dev    =  row.dataset.device   || '';
                        const r      = parseInt(row.dataset.rank) || 0;
                        const noRank = row.dataset.rank === '';
                        const trnd   =  row.dataset.trend   || '';
                        const kd     = (row.dataset.kd      || '').toUpperCase();

                        // بحث نصي
                        if (this.search && !kw.includes(this.search.toLowerCase())) return false;
                        // جهاز
                        if (this.device !== 'all' && dev !== this.device) return false;
                        // ترتيب
                        if (this.rank === 'top3'      && !(r > 0 && r <= 3))   return false;
                        if (this.rank === 'top10'     && !(r > 0 && r <= 10))  return false;
                        if (this.rank === 'top30'     && !(r > 0 && r <= 30))  return false;
                        if (this.rank === 'out100'    && !(noRank === false && r === 0)) return false;
                        if (this.rank === 'unchecked' && row.dataset.checked !== 'false') return false;
                        // اتجاه
                        if (this.trend !== 'all' && trnd !== this.trend) return false;
                        // KD
                        if (this.kd !== 'all' && kd !== this.kd) return false;

                        return true;
                    }
                 }">

                {{-- رأس: عنوان + آخر فحص --}}
                <div class="px-4 py-3 border-b bg-gray-50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse inline-block"></span>
                        <span class="font-bold text-gray-700 text-sm">
                            TOP ORGANIC KEYWORDS ({{ $project->keywords->count() }})
                        </span>
                        {{-- عداد الفلاتر النشطة --}}
                        <span x-show="activeCount > 0" x-cloak
                              class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full font-bold"
                              x-text="activeCount + ' فلتر'"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-400">
                            @php $lastCheck = $project->keywords->flatMap->rankChecks->sortByDesc('checked_at')->first(); @endphp
                            {{ $lastCheck ? 'آخر فحص: '.$lastCheck->checked_at->diffForHumans() : 'لم يُفحص بعد' }}
                        </span>
                        {{-- زرار مسح الفلاتر --}}
                        <button x-show="activeCount > 0" x-cloak
                                @click="reset()"
                                class="text-xs text-red-500 hover:text-red-700 hover:underline transition">
                            × مسح الفلاتر
                        </button>
                    </div>
                </div>

                {{-- ===== شريط الفلاتر ===== --}}
                <div class="px-4 py-3 border-b bg-white flex flex-wrap gap-2 items-center">

                    {{-- 🔍 بحث نصي --}}
                    <div class="relative flex-1 min-w-40">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">🔍</span>
                        <input type="text"
                               x-model.debounce.200ms="search"
                               placeholder="ابحث في الكلمات..."
                               class="w-full border rounded-lg pr-8 pl-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none
                                      transition"
                               :class="search ? 'border-blue-400 bg-blue-50' : 'border-gray-300'">
                    </div>

                    {{-- 📱 الجهاز --}}
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden text-xs font-medium">
                        <button @click="device='all'"
                                :class="device==='all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="px-3 py-1.5 transition border-l border-gray-300">
                            الكل
                        </button>
                        <button @click="device='desktop'"
                                :class="device==='desktop' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="px-3 py-1.5 transition border-l border-gray-300">
                            🖥️
                        </button>
                        <button @click="device='mobile'"
                                :class="device==='mobile' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="px-3 py-1.5 transition">
                            📱
                        </button>
                    </div>

                    {{-- 📊 الترتيب --}}
                    <select x-model="rank"
                            :class="rank !== 'all' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-600'"
                            class="border rounded-lg px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-blue-500 outline-none bg-white transition">
                        <option value="all">📊 كل الترتيبات</option>
                        <option value="top3">🥇 أول 3</option>
                        <option value="top10">✅ أول 10</option>
                        <option value="top30">📌 أول 30</option>
                        <option value="out100">❌ خارج 100</option>
                        <option value="unchecked">⏳ لم يُفحص</option>
                    </select>

                    {{-- 📈 الاتجاه --}}
                    <select x-model="trend"
                            :class="trend !== 'all' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-600'"
                            class="border rounded-lg px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-blue-500 outline-none bg-white transition">
                        <option value="all">📈 كل الاتجاهات</option>
                        <option value="up">▲ تحسّن</option>
                        <option value="down">▼ تراجع</option>
                        <option value="stable">🟠 ثابت</option>
                        <option value="new">🆕 جديد</option>
                    </select>

                    {{-- 💡 KD --}}
                    <select x-model="kd"
                            :class="kd !== 'all' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-600'"
                            class="border rounded-lg px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-blue-500 outline-none bg-white transition">
                        <option value="all">💡 كل KD</option>
                        <option value="LOW">🟢 LOW — سهل</option>
                        <option value="MED">🟡 MED — متوسط</option>
                        <option value="HIGH">🔴 HIGH — صعب</option>
                    </select>

                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-gray-50/80">
                            <tr class="text-gray-500 text-xs">
                                <th class="text-right px-4 py-2.5 font-semibold">Keyword</th>
                                <th class="text-center px-2 py-2.5 font-semibold w-12" title="الجهاز">Device</th>
                                <th class="text-center px-2 py-2.5 font-semibold w-20">Pos.</th>
                                <th class="text-center px-2 py-2.5 font-semibold w-16" title="اتجاه التغيير">Trend</th>
                                <th class="text-center px-2 py-2.5 font-semibold w-24">Volume</th>
                                <th class="text-center px-2 py-2.5 font-semibold w-24">CPC $</th>
                                <th class="text-center px-2 py-2.5 font-semibold w-16">KD</th>
                                <th class="text-center px-3 py-2.5 font-semibold w-36">آخر فحص</th>
                                @if(auth()->user()->isAdmin() || auth()->user()->isMember())
                                    <th class="text-center px-2 py-2.5 font-semibold w-16"></th>
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <th class="w-8"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($project->keywords as $keyword)
                                @php
                                    $latest      = $keyword->latestRankCheck;
                                    $currentRank = $latest?->rank;

                                    // الفحص السابق لحساب التغيير
                                    $prevCheck = $keyword->rankChecks()
                                        ->when($latest, fn($q) => $q->where('id', '<', $latest->id))
                                        ->orderByDesc('checked_at')
                                        ->first();
                                    $prevRank = $prevCheck?->rank;

                                    // تحديد الاتجاه
                                    if ($currentRank && $prevRank) {
                                        $diff = $prevRank - $currentRank; // موجب = تحسّن (نزل الرقم)
                                        $trend = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'stable');
                                    } elseif ($currentRank && !$prevRank) {
                                        $trend = 'new'; // ظهر لأول مرة
                                        $diff  = null;
                                    } else {
                                        $trend = 'none';
                                        $diff  = null;
                                    }
                                @endphp
                                @php
                                    // data attributes للـ Alpine filter
                                    $rowDev     = $keyword->effective_device;
                                    $rowRank    = $currentRank ?? 0;
                                    $rowTrend   = $trend;
                                    $rowKd      = strtoupper((string)($keyword->competition ?? ''));
                                    if ($rowKd === 'MEDIUM') $rowKd = 'MED';
                                    $rowChecked = $latest ? 'true' : 'false';
                                @endphp
                                <tr class="hover:bg-blue-50/30 transition group"
                                    id="kw-row-{{ $keyword->id }}"
                                    x-show="matches($el)"
                                    data-keyword="{{ strtolower($keyword->keyword) }}"
                                    data-device="{{ $rowDev }}"
                                    data-rank="{{ $currentRank !== null ? $currentRank : ($latest ? '0' : '') }}"
                                    data-trend="{{ $rowTrend }}"
                                    data-kd="{{ $rowKd }}"
                                    data-checked="{{ $rowChecked }}">

                                    {{-- الكلمة --}}
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800">{{ $keyword->keyword }}</div>
                                        @if($latest?->url)
                                            <a href="{{ $latest->url }}" target="_blank"
                                               class="text-xs text-blue-400 hover:text-blue-600 hover:underline block truncate max-w-xs"
                                               title="{{ $latest->url }}">
                                                {{ Str::limit($latest->url, 50) }}
                                            </a>
                                        @elseif($latest && !$latest->rank)
                                            <span class="text-xs text-gray-400">خارج أول 100</span>
                                        @endif
                                    </td>

                                    {{-- الجهاز --}}
                                    <td class="px-2 py-3 text-center">
                                        @php $dev = $keyword->effective_device; @endphp
                                        <span title="{{ $dev === 'mobile' ? 'Mobile' : 'Desktop' }}"
                                              class="text-lg cursor-default">
                                            {{ $dev === 'mobile' ? '📱' : '🖥️' }}
                                        </span>
                                        @if($keyword->device !== null)
                                            {{-- أيقونة صغيرة تدل إن الجهاز محدد صراحةً (مش موروث) --}}
                                            <div class="text-xs text-blue-400 leading-none">✱</div>
                                        @endif
                                    </td>

                                    {{-- الترتيب --}}
                                    <td class="px-2 py-3 text-center rank-cell" id="rank-{{ $keyword->id }}">
                                        @if($latest)
                                            <span class="font-bold text-lg leading-tight
                                                {{ !$currentRank ? 'text-gray-300' :
                                                   ($currentRank <= 3 ? 'text-emerald-600' :
                                                   ($currentRank <= 10 ? 'text-blue-600' :
                                                   ($currentRank <= 30 ? 'text-amber-600' : 'text-red-500'))) }}">
                                                {{ $currentRank ?? '—' }}
                                            </span>
                                            @if($prevRank)
                                                <div class="text-xs text-gray-400">({{ $prevRank }})</div>
                                            @endif
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>

                                    {{-- Trend: سهم اتجاه --}}
                                    <td class="px-2 py-3 text-center trend-cell" id="trend-{{ $keyword->id }}">
                                        @if($trend === 'up')
                                            <div class="flex flex-col items-center">
                                                <span class="text-emerald-500 text-xl leading-none">▲</span>
                                                <span class="text-emerald-600 text-xs font-bold">+{{ $diff }}</span>
                                            </div>
                                        @elseif($trend === 'down')
                                            <div class="flex flex-col items-center">
                                                <span class="text-red-500 text-xl leading-none">▼</span>
                                                <span class="text-red-600 text-xs font-bold">{{ $diff }}</span>
                                            </div>
                                        @elseif($trend === 'stable')
                                            <span class="inline-block w-4 h-4 rounded-full bg-amber-400 border-2 border-amber-500"
                                                  title="ثابت"></span>
                                        @elseif($trend === 'new')
                                            <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full font-medium">جديد</span>
                                        @else
                                            <span class="text-gray-200">—</span>
                                        @endif
                                    </td>

                                    {{-- Volume --}}
                                    <td class="px-2 py-3 text-center volume-cell" id="vol-{{ $keyword->id }}">
                                        @if($keyword->search_volume !== null)
                                            <span class="font-medium text-gray-700">
                                                {{ number_format($keyword->search_volume) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- CPC --}}
                                    <td class="px-2 py-3 text-center cpc-cell" id="cpc-{{ $keyword->id }}">
                                        @if($keyword->cpc !== null)
                                            <span class="font-medium text-gray-700">
                                                {{ number_format((float)$keyword->cpc, 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- KD --}}
                                    <td class="px-2 py-3 text-center comp-cell" id="comp-{{ $keyword->id }}">
                                        @if($keyword->competition !== null)
                                            @php
                                                $comp = strtoupper((string)$keyword->competition);
                                                if (is_numeric($keyword->competition)) {
                                                    $comp = $keyword->competition <= 0.33 ? 'LOW' : ($keyword->competition <= 0.66 ? 'MED' : 'HIGH');
                                                }
                                                $comp = $comp === 'MEDIUM' ? 'MED' : $comp;
                                            @endphp
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                                {{ $comp === 'LOW' ? 'bg-emerald-100 text-emerald-700' :
                                                   ($comp === 'MED' ? 'bg-amber-100 text-amber-700' :
                                                   'bg-red-100 text-red-700') }}">
                                                {{ $comp }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- آخر فحص: تاريخ + وقت --}}
                                    <td class="px-3 py-3 text-center checked-cell" id="checked-{{ $keyword->id }}">
                                        @if($latest)
                                            <div class="text-xs text-gray-600 font-medium">
                                                {{ $latest->checked_at->format('Y/m/d') }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $latest->checked_at->format('H:i') }}
                                            </div>
                                        @else
                                            <span class="text-gray-300 text-xs">لم يُفحص</span>
                                        @endif
                                    </td>

                                    {{-- زرار فحص --}}
                                    @if(auth()->user()->isAdmin() || auth()->user()->isMember())
                                        <td class="px-2 py-3 text-center">
                                            <button onclick="checkKeywordNow({{ $keyword->id }}, this)"
                                                    data-keyword-id="{{ $keyword->id }}"
                                                    class="check-btn text-xs bg-green-50 text-green-700 px-2.5 py-1 rounded-lg
                                                           hover:bg-green-100 transition border border-green-200 font-medium">
                                                فحص
                                            </button>
                                        </td>
                                    @endif

                                    {{-- حذف --}}
                                    @if(auth()->user()->isAdmin())
                                        <td class="px-1 py-3 text-center">
                                            <form method="POST" action="{{ route('keywords.destroy', $keyword) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('حذف: {{ addslashes($keyword->keyword) }}؟')"
                                                        class="text-gray-200 hover:text-red-500 transition text-xl leading-none
                                                               opacity-0 group-hover:opacity-100">
                                                    ×
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-16 text-gray-400">
                                        <div class="text-5xl mb-3">🔑</div>
                                        <p class="font-medium">لا توجد كلمات مفتاحية بعد</p>
                                        @if(auth()->user()->isAdmin())
                                            <p class="text-xs mt-1">اضغط "+ إضافة كلمة مفتاحية" أعلاه للبدء</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse

                            {{-- رسالة "لا توجد نتائج للفلتر" --}}
                            @if($project->keywords->count() > 0)
                                <tr x-show="
                                    $el.closest('tbody')
                                       .querySelectorAll('tr[data-keyword]')
                                       .length > 0 &&
                                    Array.from($el.closest('tbody').querySelectorAll('tr[data-keyword]'))
                                         .every(r => r.style.display === 'none')
                                " x-cloak>
                                    <td colspan="9" class="text-center py-10 text-gray-400">
                                        <div class="text-3xl mb-2">🔎</div>
                                        <p class="font-medium text-sm">لا توجد كلمات تطابق الفلتر</p>
                                        <button @click="reset()"
                                                class="mt-2 text-xs text-blue-500 hover:underline">
                                            مسح الفلاتر
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============ العمود الجانبي ============ --}}
        <div class="space-y-4">

            {{-- ملخص --}}
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h3 class="font-bold text-gray-700 text-sm mb-3">📊 ملخص</h3>
                @php
                    $ranked   = $project->keywords->filter(fn($k) => $k->latestRankCheck?->rank !== null);
                    $top3     = $ranked->filter(fn($k) => $k->latestRankCheck->rank <= 3)->count();
                    $top10    = $ranked->filter(fn($k) => $k->latestRankCheck->rank <= 10)->count();
                    $top30    = $ranked->filter(fn($k) => $k->latestRankCheck->rank <= 30)->count();
                    $out100   = $project->keywords->filter(fn($k) => $k->latestRankCheck && !$k->latestRankCheck->rank)->count();
                    $unchecked = $project->keywords->filter(fn($k) => !$k->latestRankCheck)->count();
                @endphp
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>أول 3
                        </span>
                        <span class="font-bold text-emerald-600">{{ $top3 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>أول 10
                        </span>
                        <span class="font-bold text-blue-600">{{ $top10 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>أول 30
                        </span>
                        <span class="font-bold text-amber-600">{{ $top30 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>خارج 100
                        </span>
                        <span class="font-bold text-red-500">{{ $out100 }}</span>
                    </div>
                    @if($unchecked)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-300 inline-block"></span>لم يُفحص
                            </span>
                            <span class="font-bold text-gray-400">{{ $unchecked }}</span>
                        </div>
                    @endif
                    <div class="border-t pt-2 flex justify-between items-center">
                        <span class="text-gray-600 font-medium">الإجمالي</span>
                        <span class="font-bold text-gray-700">{{ $project->keywords->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- آخر تحليل AI --}}
            @if($aiAnalyses->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border p-4">
                    <h3 class="font-bold text-gray-700 text-sm mb-2">🤖 آخر تحليل AI</h3>
                    <div class="text-xs text-gray-400 mb-2">
                        {{ $aiAnalyses->first()->created_at->format('Y/m/d H:i') }}
                        — {{ $aiAnalyses->first()->user->name }}
                    </div>
                    <div class="text-xs text-gray-600 max-h-32 overflow-y-auto leading-relaxed">
                        {!! nl2br(e(Str::limit($aiAnalyses->first()->response, 350))) !!}
                    </div>
                    <a href="{{ route('ai.history', $project) }}"
                       class="text-xs text-blue-500 hover:underline mt-2 block">
                        عرض كل التحليلات →
                    </a>
                </div>
            @endif

            {{-- آخر الأنشطة --}}
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <h3 class="font-bold text-gray-700 text-sm mb-3">📋 آخر الأنشطة</h3>
                @forelse($activityLogs->take(6) as $log)
                    <div class="text-xs border-b last:border-0 py-2">
                        <div class="font-medium text-gray-700">{{ $log->action }}</div>
                        <div class="text-gray-400">
                            {{ $log->user?->name ?? '⚙️ النظام' }}
                            — {{ $log->created_at->format('d/m H:i') }}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-xs">لا توجد أنشطة</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ========== الرسم البياني — نفس عرض جدول الكلمات ========== --}}
    @if($project->keywords->count() > 0)
    <div class="mt-5 grid grid-cols-3 gap-5">
    <div class="col-span-2 bg-white rounded-xl shadow-sm border overflow-hidden">

        {{-- رأس البوكس --}}
        <div class="px-5 py-3 border-b bg-gray-50 flex justify-between items-center"
             x-data="{ showLegend: true }">
            <div class="flex items-center gap-3">
                <span class="font-bold text-gray-700 text-sm">📈 تاريخ الترتيب — آخر 30 يوم</span>
                <span class="text-xs text-gray-400">
                    ({{ now()->subDays(29)->locale('ar')->isoFormat('D MMM') }}
                    →
                    {{ now()->locale('ar')->isoFormat('D MMM YYYY') }})
                </span>
            </div>
            <div class="flex items-center gap-3">
                {{-- زرار إظهار/إخفاء الـ legend --}}
                <button @click="showLegend = !showLegend"
                        class="text-xs text-gray-400 hover:text-gray-600 transition">
                    <span x-text="showLegend ? 'إخفاء المفتاح' : 'إظهار المفتاح'"></span>
                </button>
            </div>
        </div>

        <div class="p-5">
            {{-- Legend يدوي — أوضح من الـ default --}}
            <div class="flex flex-wrap gap-x-5 gap-y-1.5 mb-4" id="chartLegend"></div>

            {{-- الـ Canvas --}}
            <div class="relative" style="height: 280px;">
                <canvas id="rankChart"></canvas>
            </div>

            {{-- ملاحظة --}}
            <p class="text-xs text-gray-400 mt-3 text-center">
                المحور العمودي مقلوب — الترتيب 1 في الأعلى هو الأفضل
            </p>
        </div>
    </div>
    </div>{{-- end grid --}}
    @endif

    {{-- ========== Modal تحليل AI ========== --}}
    <div id="aiModal"
         class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[85vh] flex flex-col">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-800">🤖 تحليل الذكاء الاصطناعي</h2>
                <button onclick="document.getElementById('aiModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100">
                    ×
                </button>
            </div>
            <div id="aiContent"
                 class="flex-1 overflow-y-auto px-6 py-4 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">
                <div class="text-center py-12 text-gray-400">⏳ جاري التحليل...</div>
            </div>
        </div>
    </div>

<script>
// ========== رسم البياني ==========
const chartData = @json($chartData);
const chartEl   = document.getElementById('rankChart');

if (chartEl && chartData.datasets && chartData.datasets.length > 0) {
    const palette = [
        '#3B82F6','#10B981','#F59E0B','#EF4444',
        '#8B5CF6','#EC4899','#14B8A6','#F97316',
        '#6366F1','#84CC16',
    ];

    // بناء الـ datasets مع ألوان
    const datasets = chartData.datasets.map((d, i) => {
        const color = palette[i % palette.length];
        // تمييز Mobile بخط منقط
        const dash = d.device === 'mobile' ? [5, 3] : [];
        return {
            label:            d.label + (d.device === 'mobile' ? ' 📱' : ' 🖥️'),
            data:             d.data,
            borderColor:      color,
            backgroundColor:  color + '15',   // شفافية خفيفة للـ fill
            borderWidth:      2,
            borderDash:       dash,
            tension:          0.35,
            pointRadius:      d.data.map(v => v !== null ? 4 : 0),
            pointHoverRadius: 7,
            pointBackgroundColor: color,
            fill:             false,
            spanGaps:         true,
        };
    });

    const chart = new Chart(chartEl, {
        type: 'line',
        data: { labels: chartData.labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    reverse: true,
                    min: 1,
                    suggestedMax: 100,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    border: { dash: [4, 4] },
                    ticks: {
                        stepSize: 10,
                        font: { size: 11 },
                        color: '#94a3b8',
                        callback: v => `#${v}`,
                    },
                    title: {
                        display: true,
                        text: 'الترتيب',
                        font: { size: 11 },
                        color: '#94a3b8',
                    },
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        color: '#94a3b8',
                        maxRotation: 0,
                        // نظهر بس labels اللي فيها اسم شهر (تحتوي على حروف عربية)
                        callback: function(val, idx) {
                            const label = this.getLabelForValue(val);
                            // لو رقم فقط نعرضه كل 5 أيام
                            const isMonthLabel = /[أ-ي]/.test(label);
                            if (isMonthLabel) return label;
                            const dayNum = parseInt(label);
                            return (dayNum % 5 === 0) ? label : '';
                        },
                    },
                },
            },
            plugins: {
                legend: { display: false }, // legend يدوي أسفل
                tooltip: {
                    rtl: true,
                    textDirection: 'rtl',
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        title: items => {
                            // نعرض التاريخ الكامل في الـ tooltip
                            const idx  = items[0].dataIndex;
                            const iso  = chartData.days[idx];
                            if (!iso) return '';
                            const d = new Date(iso);
                            return d.toLocaleDateString('ar-EG', {
                                weekday: 'short', day: 'numeric',
                                month: 'long', year: 'numeric',
                            });
                        },
                        label: ctx => {
                            if (ctx.raw === null || ctx.raw === undefined)
                                return `  ${ctx.dataset.label}: لم يُفحص`;
                            return `  ${ctx.dataset.label}: المرتبة #${ctx.raw}`;
                        },
                    },
                },
            },
        },
    });

    // بناء Legend يدوي
    const legendEl = document.getElementById('chartLegend');
    if (legendEl) {
        datasets.forEach((ds, i) => {
            const dot = `<span style="display:inline-block;width:10px;height:10px;
                         border-radius:50%;background:${palette[i % palette.length]};
                         margin-left:4px;flex-shrink:0"></span>`;
            const item = document.createElement('button');
            item.className = 'flex items-center gap-1 text-xs text-gray-600 hover:text-gray-900 transition px-2 py-1 rounded hover:bg-gray-100';
            item.innerHTML = dot + `<span>${ds.label}</span>`;
            item.dataset.index = i;
            // toggle عند الضغط
            item.addEventListener('click', () => {
                const meta = chart.getDatasetMeta(i);
                meta.hidden = !meta.hidden;
                item.style.opacity = meta.hidden ? '0.4' : '1';
                chart.update();
            });
            legendEl.appendChild(item);
        });
    }
}

// ========== فحص كلمة واحدة فوري ==========
async function checkKeywordNow(kwId, btn) {
    // حالة التحميل
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite">⟳</span>';
    btn.className = btn.className.replace('green', 'yellow').replace('border-green-200','border-yellow-200');

    showToast('🔍 جاري الفحص...', 'info');

    try {
        const res  = await fetch(`/keywords/${kwId}/check`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.success) {
            updateRankCell(kwId, data);
            updateTrendCell(kwId, data);
            updateMetaCells(kwId, data);
            updateCheckedCell(kwId, data);
            showToast(`✅ ${data.message}`, 'success');
        } else {
            showToast(`❌ ${data.error}`, 'error');
        }
    } catch(e) {
        showToast('❌ خطأ في الاتصال', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'فحص';
        btn.className = 'check-btn text-xs bg-green-50 text-green-700 px-2.5 py-1 rounded-lg hover:bg-green-100 transition border border-green-200 font-medium';
    }
}

// تحديث خلية الترتيب
function updateRankCell(kwId, data) {
    const el = document.getElementById(`rank-${kwId}`);
    if (!el) return;
    const r = data.rank;
    const color = !r ? 'text-gray-300'
                : r <= 3  ? 'text-emerald-600'
                : r <= 10 ? 'text-blue-600'
                : r <= 30 ? 'text-amber-600'
                : 'text-red-500';
    el.innerHTML = `<span class="font-bold text-lg leading-tight ${color}">${r ?? '—'}</span>`;
}

// تحديث خلية الـ Trend
function updateTrendCell(kwId, data) {
    const el = document.getElementById(`trend-${kwId}`);
    if (!el) return;

    const { change, trend } = data;
    if (trend === 'up') {
        el.innerHTML = `<div class="flex flex-col items-center">
            <span class="text-emerald-500 text-xl leading-none">▲</span>
            <span class="text-emerald-600 text-xs font-bold">+${change}</span>
        </div>`;
    } else if (trend === 'down') {
        el.innerHTML = `<div class="flex flex-col items-center">
            <span class="text-red-500 text-xl leading-none">▼</span>
            <span class="text-red-600 text-xs font-bold">${change}</span>
        </div>`;
    } else if (trend === 'stable') {
        el.innerHTML = `<span class="inline-block w-4 h-4 rounded-full bg-amber-400 border-2 border-amber-500" title="ثابت"></span>`;
    } else if (trend === 'new') {
        el.innerHTML = `<span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full font-medium">جديد</span>`;
    } else {
        el.innerHTML = `<span class="text-gray-200">—</span>`;
    }
}

// تحديث Volume + CPC + KD
function updateMetaCells(kwId, data) {
    const vol  = document.getElementById(`vol-${kwId}`);
    const cpc  = document.getElementById(`cpc-${kwId}`);
    const comp = document.getElementById(`comp-${kwId}`);

    if (vol && data.search_volume != null)
        vol.innerHTML = `<span class="font-medium text-gray-700">${Number(data.search_volume).toLocaleString()}</span>`;

    if (cpc && data.cpc != null)
        cpc.innerHTML = `<span class="font-medium text-gray-700">${parseFloat(data.cpc).toFixed(2)}</span>`;

    if (comp && data.competition != null) {
        const c = data.competition.toString().toUpperCase().replace('MEDIUM','MED');
        const cls = c === 'LOW' ? 'bg-emerald-100 text-emerald-700'
                  : c === 'MED' ? 'bg-amber-100 text-amber-700'
                  : 'bg-red-100 text-red-700';
        comp.innerHTML = `<span class="text-xs font-semibold px-2 py-0.5 rounded-full ${cls}">${c}</span>`;
    }
}

// تحديث خلية آخر فحص
function updateCheckedCell(kwId, data) {
    const el = document.getElementById(`checked-${kwId}`);
    if (!el) return;
    const now = new Date();
    const date = now.toLocaleDateString('ar-EG', {year:'numeric', month:'2-digit', day:'2-digit'})
                    .replace(/\//g, '/');
    const time = now.toLocaleTimeString('ar', {hour:'2-digit', minute:'2-digit', hour12: false});
    el.innerHTML = `<div class="text-xs text-gray-600 font-medium">${now.getFullYear()}/${String(now.getMonth()+1).padStart(2,'0')}/${String(now.getDate()).padStart(2,'0')}</div>
                    <div class="text-xs text-gray-400">${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}</div>`;
}

// Toast
function showToast(msg, type = 'info') {
    const bg = type==='success' ? '#059669' : type==='error' ? '#dc2626' : '#2563eb';
    const t  = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
        background:${bg};color:#fff;padding:10px 20px;border-radius:12px;
        font-size:13px;font-weight:600;z-index:9999;font-family:Cairo,sans-serif;
        box-shadow:0 4px 20px rgba(0,0,0,.2);transition:opacity .3s;`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); },
               type === 'error' ? 5000 : 3000);
}

// ========== تحليل AI ==========
async function runAiAnalysis(projectId) {
    const modal   = document.getElementById('aiModal');
    const content = document.getElementById('aiContent');
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="text-center py-12 text-gray-400">⏳ جاري التحليل... قد يستغرق دقيقة أو أكثر</div>';

    try {
        const res  = await fetch(`/projects/${projectId}/ai-analyze`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Content-Type': 'application/json',
            },
        });
        const data = await res.json();
        if (data.success) {
            content.textContent = data.analysis;
        } else {
            content.innerHTML = `<div class="p-4 bg-red-50 text-red-600 rounded-lg text-sm">❌ ${data.error}</div>`;
        }
    } catch(e) {
        content.innerHTML = '<div class="p-4 bg-red-50 text-red-600 rounded-lg text-sm">❌ خطأ في الاتصال</div>';
    }
}

// Spin animation
const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
</x-app-layout>
