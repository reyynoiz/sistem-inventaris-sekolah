@php
    $isAdmin = auth()->user()->isAdmin();
    $control = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500';
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb :items="['Peminjaman' => route('loans.index'), ($isAdmin ? 'Tambah' : 'Pinjam barang') => null]" />
        <h2 class="mt-1 text-xl font-semibold text-gray-800">{{ $isAdmin ? 'Tambah peminjaman' : 'Pinjam barang' }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-md bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('loans.store') }}" class="space-y-5">
                    @csrf

                    @if ($isAdmin)
                        <x-field name="user_id" label="Peminjam" required>
                            <select id="user_id" name="user_id" class="{{ $control }}" required autofocus>
                                <option value="">Pilih peminjam</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </x-field>
                    @endif

                    <x-field name="item_id" label="Barang" required>
                        <select id="item_id" name="item_id" class="{{ $control }}" required @unless ($isAdmin) autofocus @endunless>
                            <option value="">Pilih barang</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>
                                    {{ $item->name }} (stok {{ number_format($item->stock, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </x-field>

                    <x-field name="quantity" label="Jumlah" required>
                        <x-text-input id="quantity" name="quantity" type="number" min="1" class="block w-full"
                                      :value="old('quantity', 1)" placeholder="Contoh: 1" required />
                    </x-field>

                    <div class="grid gap-5 {{ $isAdmin ? 'sm:grid-cols-2' : '' }}">
                        @if ($isAdmin)
                            <x-field name="loan_date" label="Tanggal pinjam" required>
                                <x-text-input id="loan_date" name="loan_date" type="date" class="block w-full"
                                              :value="old('loan_date', now()->toDateString())" required />
                            </x-field>
                        @endif

                        <x-field name="due_date" label="Tenggat pengembalian" required>
                            <x-text-input id="due_date" name="due_date" type="date" class="block w-full"
                                          :value="old('due_date', now()->addDays(7)->toDateString())" required />
                        </x-field>
                    </div>

                    <div class="flex gap-2">
                        <x-btn>Simpan peminjaman</x-btn>
                        <x-btn variant="light" :href="route('loans.index')">Batal</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>