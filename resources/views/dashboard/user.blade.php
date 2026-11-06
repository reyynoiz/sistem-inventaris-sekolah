@php
    $badge = [
        'dipinjam' => 'bg-blue-100 text-blue-800',
        'dikembalikan' => 'bg-emerald-100 text-emerald-800',
        'terlambat' => 'bg-red-100 text-red-800',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
        <p class="mt-1 text-sm text-gray-500">Selamat datang, {{ auth()->user()->name }}.</p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-flash />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat label="Sedang kamu pinjam" :value="$borrowed" tone="blue" />
                <x-stat label="Terlambat dikembalikan" :value="$overdue" tone="red" />
                <x-stat label="Total peminjaman" :value="$total" />
            </div>

            <section class="rounded-md bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-medium text-gray-900">Peminjaman terakhir</h3>
                    <div class="flex gap-2">
                        <x-btn variant="light" :href="route('loans.index')">Lihat semua</x-btn>
                        <x-btn :href="route('loans.create')">Pinjam barang</x-btn>
                    </div>
                </div>

                <ul class="mt-3 divide-y divide-gray-100 text-sm">
                    @forelse ($latestLoans as $loan)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span>
                                {{ $loan->item->name }} x{{ number_format($loan->quantity, 0, ',', '.') }}
                                <span class="block text-xs text-gray-500">
                                    Tenggat {{ $loan->due_date->translatedFormat('j M Y') }}
                                </span>
                            </span>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$loan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </li>
                    @empty
                        <li class="py-2 text-gray-500">Kamu belum pernah meminjam. Pilih barang lalu ajukan peminjaman.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>