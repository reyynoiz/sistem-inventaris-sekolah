<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Peminjaman</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex justify-between">
                <a href="{{ route('loans.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + Pinjam Barang
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="p-4 font-semibold text-gray-600">Peminjam</th>
                            <th class="p-4 font-semibold text-gray-600">Barang</th>
                            <th class="p-4 font-semibold text-gray-600">Jumlah</th>
                            <th class="p-4 font-semibold text-gray-600">Tgl Pinjam</th>
                            <th class="p-4 font-semibold text-gray-600">Tenggat</th>
                            <th class="p-4 font-semibold text-gray-600">Status</th>
                            <th class="p-4 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4">{{ $loan->user->name ?? Auth::user()->name }}</td>
                                <td class="p-4">{{ $loan->item->name }}</td>
                                <td class="p-4">{{ $loan->quantity }}</td>
                                <td class="p-4">{{ $loan->loan_date->translatedFormat('j M Y') }}</td>
                                <td class="p-4">{{ $loan->due_date->translatedFormat('j M Y') }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-sm font-semibold
                                        {{ $loan->status === 'dipinjam' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                        {{ $loan->status === 'dikembalikan' ? 'bg-green-200 text-green-800' : '' }}
                                        {{ $loan->status === 'terlambat' ? 'bg-red-200 text-red-800' : '' }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($loan->status === 'dipinjam' && Auth::user()->role === 'admin')
                                        <form action="{{ route('loans.return', $loan->id) }}" method="POST" onsubmit="return confirm('Proses pengembalian barang ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm">
                                                Kembalikan
                                            </button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">Belum ada data peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>