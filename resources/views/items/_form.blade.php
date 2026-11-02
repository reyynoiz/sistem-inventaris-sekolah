@php $control = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500'; @endphp
<form method="POST" action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}" class="space-y-5">
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

    <div class="flex gap-2">
        <x-btn>Simpan</x-btn>
        <x-btn variant="light" :href="route('items.index')">Batal</x-btn>
    </div>
</form>