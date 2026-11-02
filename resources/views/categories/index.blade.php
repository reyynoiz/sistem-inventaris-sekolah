<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb :items="['Kategori' => null]" />
        <div class="mt-1 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Kategori barang</h2>
            <x-btn :href="route('categories.create')">Tambah kategori</x-btn>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <x-flash />

            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kategori" autofocus
                       class="w-full max-w-xs rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                <x-btn variant="light">Cari</x-btn>
            </form>

            <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">No</th>
                            <th class="px-4 py-3 font-medium">Nama kategori</th>
                            <th class="px-4 py-3 font-medium">Jumlah barang</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-4 py-3">{{ $categories->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3">{{ $category->name }}</td>
                                <td class="px-4 py-3">{{ number_format($category->items_count, 0, ',', '.') }}</td>
                                <td class="flex gap-2 px-4 py-3">
                                    <x-btn variant="light" :href="route('categories.edit', $category)">Ubah</x-btn>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                          onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-btn variant="danger">Hapus</x-btn>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada kategori yang cocok. Tambah kategori untuk mengelompokkan barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>