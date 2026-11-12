@php
    $isAdmin = Auth::user()->role === 'admin';

    $icons = [
        'dashboard' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'cube' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
        'tag' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z',
        'swap' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
        'user' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'log' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    ];

    // [label, route, pola route aktif, ikon, khusus admin]
    $links = [
        ['Dashboard', 'dashboard', ['dashboard'], 'dashboard', false],
        ['Barang', 'items.index', ['items.*'], 'cube', false],
        ['Kategori', 'categories.index', ['categories.*'], 'tag', true],
        ['Peminjaman', 'loans.index', ['loans.*', 'returns.*'], 'swap', false],
        ['Pengguna', 'users.index', ['users.*'], 'user', true],
        ['Log Aktivitas', 'activity-logs.index', ['activity-logs.*'], 'log', true],
    ];
    $links = array_filter($links, fn ($link) => $isAdmin || ! $link[4]);
@endphp

<!-- Latar gelap saat sidebar terbuka di layar sempit -->
<div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" aria-hidden="true"></div>

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col overflow-y-auto bg-gradient-to-b from-indigo-600 to-indigo-800 text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:translate-x-0">

    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/15 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-base font-bold tracking-wide">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['cube'] }}" />
            </svg>
            INVENTARIS
        </a>

        <button type="button" @click="sidebarOpen = false" class="rounded-md p-1 text-indigo-100 hover:bg-white/10 lg:hidden" aria-label="Tutup menu">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Menu utama">
        @foreach ($links as [$label, $route, $patterns, $icon])
            @php $active = request()->routeIs(...$patterns); @endphp
            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
               class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-white/20 text-white' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}" />
                </svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <!-- Info akun -->
    <div class="shrink-0 border-t border-white/15 px-5 py-4 text-xs text-indigo-100">
        Masuk sebagai <span class="font-semibold text-white">{{ ucfirst(Auth::user()->role) }}</span>
    </div>
</aside>