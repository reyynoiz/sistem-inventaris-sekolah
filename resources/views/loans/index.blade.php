@php
    $isAdmin = auth()->user()->isAdmin();
    $badge = [
        'dipinjam' => 'bg-blue-100 text-blue-800',
        'dikembalikan' => 'bg-emerald-100 text-emerald-800',
        'terlambat' => 'bg-red-100 text-red-800',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb :items="['Peminjaman' => null]" />
        <div class="mt-1 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ $isAdmin ? 'Data peminjaman' : 'Riwayat peminjaman' }}</h2>
            <x-btn :href="route('loans.create')">{{ $isAdmin ? 'Tambah peminjaman' : 'Pinjam barang' }}</x-btn>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" autofocus
                       placeholder="{{ $isAdmin ? 'Cari peminjam atau barang' : 'Cari nama barang' }}"
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">Semua status</option>
                    @foreach (['dipinjam' => 'Dipinjam', 'terlambat' => 'Terlambat', 'dikembalikan' => 'Dikembalikan'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-btn variant="light">Terapkan</x-btn>
            </form>

            <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            @if ($isAdmin)
                                <th class="px-4 py-3 font-medium">Peminjam</th>
                            @endif
                            <th class="px-4 py-3 font-medium">Barang</th>
                            <th class="px-4 py-3 font-medium">Jumlah</th>
                            <th class="px-4 py-3 font-medium">Tanggal pinjam</th>
                            <th class="px-4 py-3 font-medium">Tenggat</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Dikembalikan</th>
                            @if ($isAdmin)
                                <th class="px-4 py-3 font-medium">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($loans as $loan)
                            <tr>
                                @if ($isAdmin)
                                    <td class="px-4 py-3">{{ $loan->user->name }}</td>
                                @endif
                                <td class="px-4 py-3">{{ $loan->item->name }}</td>
                                <td class="px-4 py-3">{{ number_format($loan->quantity, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $loan->loan_date->translatedFormat('j M Y') }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $loan->due_date->translatedFormat('j M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$loan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if ($loan->returnLog)
                                        <span class="whitespace-nowrap">{{ $loan->returnLog->returned_at->translatedFormat('j M Y') }}</span>
                                        <span class="block text-xs">{{ ucfirst($loan->returnLog->condition_on_return) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                @if ($isAdmin)
                                    <td class="px-4 py-3">
                                        @if (in_array($loan->status, ['dipinjam', 'terlambat']))
                                            <x-btn variant="light" :href="route('returns.create', $loan)">Catat pengembalian</x-btn>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 6 }}" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada peminjaman yang cocok. Coba ubah kata kunci atau filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $loans->links() }}
        </div>
    </div>
</x-app-layout>