<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">Selamat datang, {{ auth()->user()->name }}. Ini ringkasan inventaris sekolah.</p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-flash />

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-stat label="Total barang" :value="$totalItems" />
                <x-stat label="Total stok tersedia" :value="$totalStock" tone="green" />
                <x-stat label="Sedang dipinjam" :value="$borrowed" tone="blue" />
                <x-stat label="Terlambat" :value="$overdue" tone="red" />
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <section class="rounded-md bg-white p-4 shadow-sm">
                    <h3 class="font-medium text-gray-900">Barang paling sering dipinjam</h3>
                    <ul class="mt-3 divide-y divide-gray-100 text-sm">
                        @forelse ($topItems as $item)
                            <li class="flex justify-between py-2">
                                <span>{{ $item->name }}</span>
                                <span class="text-gray-500">{{ number_format($item->loans_count, 0, ',', '.') }}x dipinjam</span>
                            </li>
                        @empty
                            <li class="py-2 text-gray-500">Belum ada data peminjaman.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="rounded-md bg-white p-4 shadow-sm">
                    <h3 class="font-medium text-gray-900">Pengguna paling aktif</h3>
                    <ul class="mt-3 divide-y divide-gray-100 text-sm">
                        @forelse ($topUsers as $user)
                            <li class="flex justify-between py-2">
                                <span>{{ $user->name }}</span>
                                <span class="text-gray-500">{{ number_format($user->loans_count, 0, ',', '.') }}x meminjam</span>
                            </li>
                        @empty
                            <li class="py-2 text-gray-500">Belum ada data peminjaman.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <section class="rounded-md bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-gray-900">Peminjaman terlambat</h3>
                    <x-btn variant="light" :href="route('loans.index', ['status' => 'terlambat'])">Lihat semua</x-btn>
                </div>
                <ul class="mt-3 divide-y divide-gray-100 text-sm">
                    @forelse ($overdueLoans as $loan)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span>
                                {{ $loan->user->name }} meminjam {{ $loan->item->name }} x{{ number_format($loan->quantity, 0, ',', '.') }}
                                <span class="block text-xs text-red-700">
                                    Tenggat {{ $loan->due_date->translatedFormat('j M Y') }},
                                    terlambat {{ (int) abs($loan->due_date->diffInDays(today())) }} hari
                                </span>
                            </span>
                            <x-btn variant="light" :href="route('returns.create', $loan)">Catat pengembalian</x-btn>
                        </li>
                    @empty
                        <li class="py-2 text-gray-500">Tidak ada peminjaman yang terlambat.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>