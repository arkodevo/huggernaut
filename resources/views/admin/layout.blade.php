<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950"
      data-cnfont="{{ auth()->user()?->chinese_font ?? 'biaukai' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — 流動 Living Lexicon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&family=Noto+Serif+TC:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="h-full">

<div class="flex h-full min-h-screen">

    {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
    <aside class="w-56 shrink-0 bg-gray-900 flex flex-col">

        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <span class="cn text-xl font-bold text-white tracking-tight">流動</span>
                <span class="text-xs text-gray-400 uppercase tracking-widest">Admin</span>
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            @php
                $navItem = fn(string $route, string $label, string $icon) =>
                    [
                        'route' => $route,
                        'label' => $label,
                        'icon'  => $icon,
                        'active' => request()->routeIs($route . '*'),
                    ];

                $navItems = [
                    $navItem('admin.dashboard',   'Dashboard',   '◈'),
                    $navItem('admin.words.index',  'Words',       '字'),
                    $navItem('admin.badges.index',           'Badges',             '🏅'),
                    $navItem('admin.attribute-settings.index', 'Attr Settings',   '⚗'),
                    $navItem('admin.search-logs.index',         'Search Logs',     '🔍'),
                    $navItem('admin.not-found.index',           'Not Found',       '⬚'),
                    $navItem('admin.shifu-engagements.index',   'Engagements', '師'),
                    $navItem('admin.preferences',  'Preferences', '⚙'),
                ];
            @endphp

            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors
                          {{ $item['active']
                              ? 'bg-indigo-600 text-white'
                              : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <span class="text-base">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- User + logout --}}
        <div class="px-4 py-4 border-t border-gray-800">
            <p class="text-xs text-gray-500 truncate mb-2">{{ auth()->user()->name }}</p>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left text-xs text-gray-500 hover:text-red-400 transition-colors">
                    Sign out
                </button>
            </form>
        </div>

    </aside>

    {{-- ── Main ─────────────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden bg-gray-50">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 px-6 py-6">
            @yield('content')
        </main>

    </div>

</div>

@stack('scripts')
</body>
</html>
