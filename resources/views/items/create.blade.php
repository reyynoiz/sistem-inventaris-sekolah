<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb :items="['Barang' => route('items.index'), 'Tambah' => null]" />
        <h2 class="mt-1 text-xl font-semibold text-gray-800">Tambah barang</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-md bg-white p-6 shadow-sm">
                @include('items._form')
            </div>
        </div>
    </div>
</x-app-layout>