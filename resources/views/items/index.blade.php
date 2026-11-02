@php $isAdmin = auth()->user()->isAdmin(); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb :items="['Barang' => null]" />
        <div class="mt-1 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Data barang</h2>
            @if ($isAdmin)
                <x-btn :href="route('items.create')">Tambah barang</x-btn>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode" autofocus
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                <select name="category" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="condition" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">Semua kondisi</option>
                    @foreach (['baik', 'rusak ringan', 'rusak berat'] as $condition)
                        <option value="{{ $condition }}" @selected(request('condition') === $condition)>{{ ucfirst($condition) }}</option>
                    @endforeach
                </select>
                <x-btn variant="light">Terapkan</x-btn>
            </form>

            <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Kode</th>
                            <th class="px-4 py-3 font-medium">Nama barang</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 font-medium">Stok</th>
                            <th class="px-4 py-3 font-medium">Kondisi</th>
                            @if ($isAdmin)
                                <th class="px-4 py-3 font-medium">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $item)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item->code }}</td>
                                <td class="px-4 py-3">{{ $item->name }}</td>
                                <td class="px-4 py-3">{{ $item->category->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ number_format($item->stock, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ ucfirst($item->condition) }}</td>
                                @if ($isAdmin)
                                    <td class="flex gap-2 px-4 py-3">
                                        <x-btn variant="light" :href="route('items.edit', $item)">Ubah</x-btn>
                                        <form method="POST" action="{{ route('items.destroy', $item) }}"
                                              onsubmit="return confirm('Hapus barang {{ $item->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-btn variant="danger">Hapus</x-btn>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 6 : 5 }}" class="px-4 py-6 text-center text-gray-500">Tidak ada barang yang cocok. Coba ubah kata kunci atau filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>