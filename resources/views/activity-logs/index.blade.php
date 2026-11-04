@php
    $labels = [
        'login' => 'Login',
        'logout' => 'Logout',
        'register' => 'Registrasi',
        'create_category' => 'Tambah kategori',
        'update_category' => 'Ubah kategori',
        'delete_category' => 'Hapus kategori',
        'create_item' => 'Tambah barang',
        'update_item' => 'Ubah barang',
        'delete_item' => 'Hapus barang',
        'create_loan' => 'Peminjaman',
        'return_loan' => 'Pengembalian',
        'create_user' => 'Tambah akun',
        'update_user' => 'Ubah akun',
        'delete_user' => 'Hapus akun',
    ];
    $tone = fn ($action) => match (true) {
        str_starts_with($action, 'delete') => 'bg-red-100 text-red-800',
        str_starts_with($action, 'create') => 'bg-emerald-100 text-emerald-800',
        str_starts_with($action, 'update'), $action === 'return_loan' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb :items="['Log aktivitas' => null]" />
        <h2 class="mt-1 text-xl font-semibold text-gray-800">Log aktivitas</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari pengguna atau deskripsi" autofocus
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                <select name="action" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">Semua aksi</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $labels[$action] ?? $action }}</option>
                    @endforeach
                </select>
                <x-btn variant="light">Terapkan</x-btn>
            </form>

            <div class="overflow-x-auto rounded-md bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Waktu</th>
                            <th class="px-4 py-3 font-medium">Pengguna</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                            <th class="px-4 py-3 font-medium">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3">{{ $log->created_at->translatedFormat('j M Y, H:i') }}</td>
                                <td class="px-4 py-3">{{ $log->user?->name ?? 'Akun dihapus' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $tone($log->action) }}">
                                        {{ $labels[$log->action] ?? $log->action }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada aktivitas yang cocok. Coba ubah kata kunci atau filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>