<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SERP Rank Tracker' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Cairo','sans-serif'] } } } }</script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
        /* Scrollbar خفيف للـ Sidebar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        /* Active link */
        .nav-link-active { background: #eff6ff; color: #2563eb; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen" x-data="{ sidebarOpen: true }">

<div class="flex min-h-screen">

    {{-- ========== SIDEBAR ========== --}}
    <aside
        :class="sidebarOpen ? 'w-64' : 'w-0 overflow-hidden'"
        class="bg-white border-l border-gray-200 flex flex-col transition-all duration-300 fixed top-0 right-0 h-full z-30 shadow-sm"
        style="min-width: 0;">

        {{-- شعار --}}
        <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-blue-700 text-lg">
                <span>📊</span>
                <span x-show="sidebarOpen" x-cloak>SERP Tracker</span>
            </a>
            <button @click="sidebarOpen = !sidebarOpen"
                    class="text-gray-400 hover:text-gray-600 transition p-1 rounded">
                <span x-show="sidebarOpen">◀</span>
                <span x-show="!sidebarOpen" x-cloak>▶</span>
            </button>
        </div>

        {{-- القوائم --}}
        <nav class="flex-1 overflow-y-auto sidebar-scroll py-3 px-2 space-y-1"
             x-show="sidebarOpen" x-cloak>

            {{-- الرئيسية --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                <span class="text-base">🏠</span>
                <span>الرئيسية</span>
            </a>

            {{-- العملاء (Admin فقط) --}}
            @if(auth()->user()->isAdmin() && isset($sidebarClients))
                <div x-data="{ open: {{ request()->routeIs('clients*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition {{ request()->routeIs('clients*') ? 'nav-link-active' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-base">👥</span>
                            <span>العملاء</span>
                            <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full">{{ $sidebarClients->count() }}</span>
                        </div>
                        <span class="text-gray-400 text-xs transition-transform" :class="open ? 'rotate-90' : ''">▶</span>
                    </button>

                    <div x-show="open" x-cloak class="mt-1 mr-4 space-y-0.5">
                        {{-- رابط كل العملاء --}}
                        <a href="{{ route('clients.index') }}"
                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-blue-600 hover:bg-blue-50 transition font-medium">
                            + كل العملاء
                        </a>
                        @foreach($sidebarClients as $sc)
                            <a href="{{ route('clients.show', $sc) }}"
                               class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition truncate {{ request()->is('clients/'.$sc->id.'*') ? 'nav-link-active' : '' }}"
                               title="{{ $sc->name }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0"></span>
                                <span class="truncate">{{ $sc->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- المشاريع --}}
            @if(isset($sidebarProjects))
                <div x-data="{ open: {{ request()->routeIs('projects*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition {{ request()->routeIs('projects*') ? 'nav-link-active' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-base">📁</span>
                            <span>المشاريع</span>
                            <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded-full">{{ $sidebarProjects->count() }}</span>
                        </div>
                        <span class="text-gray-400 text-xs transition-transform" :class="open ? 'rotate-90' : ''">▶</span>
                    </button>

                    <div x-show="open" x-cloak class="mt-1 mr-4 space-y-0.5">
                        <a href="{{ route('projects.index') }}"
                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-blue-600 hover:bg-blue-50 transition font-medium">
                            + كل المشاريع
                        </a>
                        @foreach($sidebarProjects as $sp)
                            <a href="{{ route('projects.show', $sp) }}"
                               class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition {{ request()->is('projects/'.$sp->id.'*') ? 'nav-link-active' : '' }}"
                               title="{{ $sp->name }} — {{ $sp->domain }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-300 flex-shrink-0"></span>
                                <div class="truncate">
                                    <div class="truncate">{{ $sp->name }}</div>
                                    <div class="text-gray-400 truncate" style="font-size:10px">{{ $sp->domain }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- فاصل --}}
            <div class="border-t border-gray-100 my-2"></div>

            {{-- السجل --}}
            @if(auth()->user()->isAdmin())
                <a href="{{ route('activity-log.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition {{ request()->routeIs('activity-log*') ? 'nav-link-active' : '' }}">
                    <span class="text-base">📋</span>
                    <span>سجل الأنشطة</span>
                </a>

                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition {{ request()->routeIs('settings*') ? 'nav-link-active' : '' }}">
                    <span class="text-base">⚙️</span>
                    <span>الإعدادات</span>
                </a>
            @endif
        </nav>

        {{-- معلومات المستخدم --}}
        <div x-show="sidebarOpen" x-cloak
             class="border-t border-gray-100 px-4 py-3 bg-gray-50">
            <div class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</div>
            <div class="flex items-center justify-between mt-1">
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ auth()->user()->isAdmin() ? 'bg-red-100 text-red-600' :
                       (auth()->user()->isMember() ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                    {{ ['admin'=>'مدير','member'=>'عضو','client'=>'عميل'][auth()->user()->role] }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition">
                        خروج
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ========== MAIN CONTENT ========== --}}
    <div class="flex-1 flex flex-col transition-all duration-300"
         :class="sidebarOpen ? 'mr-64' : 'mr-0'">

        {{-- شريط علوي مضغوط --}}
        <header class="bg-white border-b border-gray-200 px-4 py-2.5 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center gap-3">
                {{-- زرار toggle الـ sidebar --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="text-gray-400 hover:text-blue-600 transition p-1 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                {{-- Breadcrumb من الـ slot --}}
                @isset($header)
                    <div class="text-sm text-gray-500">{{ $header }}</div>
                @endisset
            </div>

            {{-- روابط سريعة --}}
            <div class="flex items-center gap-2 text-sm text-gray-500">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('projects.create') }}"
                       class="flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition text-xs font-medium">
                        <span>+</span> مشروع جديد
                    </a>
                @endif
            </div>
        </header>

        {{-- المحتوى --}}
        <main class="flex-1 p-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show"
                     x-init="setTimeout(() => show = false, 4000)"
                     class="mb-4 bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-xl flex justify-between items-center text-sm">
                    <span>✅ {{ session('success') }}</span>
                    <button @click="show = false" class="text-green-400 hover:text-green-600 text-xl leading-none">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl text-sm">
                    ❌ {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $e)
                            <li>• {{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
