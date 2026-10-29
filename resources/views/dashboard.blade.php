<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">
                        Selamat datang, {{ Auth::user()->name }}! 👋
                    </h3>

                    @if(Auth::user()->role === 'admin')
                        <!-- Tampilan Khusus Admin -->
                        <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                            <h4 class="font-bold text-indigo-800 mb-2">Pusat Kendali Admin</h4>
                            <p class="text-indigo-700 text-sm">
                                Anda login sebagai Administrator. Gunakan navigasi di atas untuk mengelola data <b>Kategori</b>, <b>Barang</b>, dan memantau seluruh transaksi <b>Peminjaman</b>.
                            </p>
                        </div>
                    @else
                        <!-- Tampilan Khusus User Biasa -->
                        <div class="p-4 bg-green-50 rounded-lg border border-green-100">
                            <h4 class="font-bold text-green-800 mb-2">Status Akun Peminjam</h4>
                            <p class="text-green-700 text-sm">
                                Anda bisa melihat daftar barang yang tersedia dan mengajukan peminjaman melalui menu <b>Peminjaman</b> di atas.
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>