@php $control = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500'; @endphp
<form method="POST" action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}"
      enctype="multipart/form-data" class="space-y-5" x-data="{ preview: null }">
    @csrf
    @if ($item->exists)
        @method('PUT')
    @endif

    <x-field name="name" label="Nama barang" required>
        <x-text-input id="name" name="name" type="text" class="block w-full"
                      :value="old('name', $item->name)" placeholder="Contoh: Proyektor Epson" required autofocus />
    </x-field>

    <x-field name="code" label="Kode barang" required>
        <x-text-input id="code" name="code" type="text" class="block w-full"
                      :value="old('code', $item->code)" placeholder="Contoh: ELK-002" required />
    </x-field>

    <x-field name="category_id" label="Kategori" required>
        <select id="category_id" name="category_id" class="{{ $control }}" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </x-field>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-field name="stock" label="Stok tersedia" required>
            <x-text-input id="stock" name="stock" type="number" min="0" class="block w-full"
                          :value="old('stock', $item->stock ?? 0)" placeholder="Contoh: 5" required />
        </x-field>

        <x-field name="condition" label="Kondisi" required>
            <select id="condition" name="condition" class="{{ $control }}" required>
                @foreach (['baik', 'rusak ringan', 'rusak berat'] as $condition)
                    <option value="{{ $condition }}" @selected(old('condition', $item->condition ?? 'baik') === $condition)>{{ ucfirst($condition) }}</option>
                @endforeach
            </select>
        </x-field>
    </div>

    <x-field name="photo" label="Foto barang">
        <div class="flex items-center gap-4">
            <template x-if="preview">
                <img :src="preview" alt="Pratinjau foto baru" class="h-20 w-20 rounded-md object-cover">
            </template>
            <div x-show="! preview">
                <x-item-photo :item="$item" size="h-20 w-20" />
            </div>

            <div class="min-w-0 flex-1">
                <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                       @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                       class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-gray-800 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-gray-700">
                <p class="mt-1 text-xs text-gray-500">Opsional. JPG, PNG, atau WebP, maksimal 2 MB, minimal 100 x 100 piksel.</p>
            </div>
        </div>

        @if ($item->photo)
            <label class="mt-3 inline-flex items-center">
                <input type="checkbox" name="remove_photo" value="1" class="rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-500">
                <span class="ms-2 text-sm text-gray-600">Hapus foto saat ini</span>
            </label>
        @endif
    </x-field>

    <div class="flex gap-2">
        <x-btn>Simpan</x-btn>
        <x-btn variant="light" :href="route('items.index')">Batal</x-btn>
    </div>
</form>