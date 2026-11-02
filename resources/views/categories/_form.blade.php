<form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="space-y-5">
    @csrf
    @if ($category->exists)
        @method('PUT')
    @endif

    <x-field name="name" label="Nama kategori" required>
        <x-text-input id="name" name="name" type="text" class="block w-full"
                      :value="old('name', $category->name)" placeholder="Contoh: Alat Lab" required autofocus />
    </x-field>

    <div class="flex gap-2">
        <x-btn>Simpan</x-btn>
        <x-btn variant="light" :href="route('categories.index')">Batal</x-btn>
    </div>
</form>