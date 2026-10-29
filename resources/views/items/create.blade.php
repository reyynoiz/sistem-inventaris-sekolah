<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Barang</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('items.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kode Barang <span class="text-red-500">*</span></label>
                            <input type="text" name="code" class="border rounded w-full py-2 px-3" required autofocus>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Barang <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="border rounded w-full py-2 px-3" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select name="category_id" class="border rounded w-full py-2 px-3" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Stok <span class="text-red-500">*</span></label>
                            <input type="number" name="stock" class="border rounded w-full py-2 px-3" required min="0">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kondisi <span class="text-red-500">*</span></label>
                            <select name="condition" class="border rounded w-full py-2 px-3" required>
                                <option value="baik">Baik</option>
                                <option value="rusak ringan">Rusak Ringan</option>
                                <option value="rusak berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
                    <a href="{{ route('items.index') }}" class="ml-2 text-gray-600">Batal</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>