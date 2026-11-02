@php $control = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500'; @endphp
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb :items="['Peminjaman' => route('loans.index'), 'Catat pengembalian' => null]" />
        <h2 class="mt-1 text-xl font-semibold text-gray-800">Catat pengembalian</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl space-y-4 px-4 sm:px-6 lg:px-8">
            <dl class="grid grid-cols-2 gap-3 rounded-md bg-white p-4 text-sm shadow-sm">
                <dt class="text-gray-500">Peminjam</dt>
                <dd>{{ $loan->user->name }}</dd>
                <dt class="text-gray-500">Barang</dt>
                <dd>{{ $loan->item->name }} x{{ number_format($loan->quantity, 0, ',', '.') }}</dd>
                <dt class="text-gray-500">Tanggal pinjam</dt>
                <dd>{{ $loan->loan_date->translatedFormat('j M Y') }}</dd>
                <dt class="text-gray-500">Tenggat</dt>
                <dd>{{ $loan->due_date->translatedFormat('j M Y') }}</dd>
                @if ($loan->status === 'terlambat')
                    <dt class="text-gray-500">Keterlambatan</dt>
                    <dd class="font-medium text-red-700">{{ (int) $loan->due_date->diffInDays(today()) }} hari</dd>
                @endif
            </dl>

            <div class="rounded-md bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('returns.store', $loan) }}" class="space-y-5">
                    @csrf

                    <x-field name="returned_at" label="Tanggal pengembalian" required>
                        <x-text-input id="returned_at" name="returned_at" type="date" class="block w-full"
                                      :value="old('returned_at', now()->toDateString())" required autofocus />
                    </x-field>

                    <x-field name="condition_on_return" label="Kondisi saat dikembalikan" required>
                        <select id="condition_on_return" name="condition_on_return" class="{{ $control }}" required>
                            @foreach (['baik', 'rusak ringan', 'rusak berat'] as $condition)
                                <option value="{{ $condition }}" @selected(old('condition_on_return', 'baik') === $condition)>{{ ucfirst($condition) }}</option>
                            @endforeach
                        </select>
                    </x-field>

                    <x-field name="notes" label="Catatan">
                        <textarea id="notes" name="notes" rows="3" class="{{ $control }}"
                                  placeholder="Contoh: kabel charger tidak lengkap">{{ old('notes') }}</textarea>
                    </x-field>

                    <div class="flex gap-2">
                        <x-btn>Simpan pengembalian</x-btn>
                        <x-btn variant="light" :href="route('loans.index')">Batal</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>