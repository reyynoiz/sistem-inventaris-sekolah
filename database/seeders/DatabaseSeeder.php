<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Item;
use App\Models\Loan;
use App\Models\ReturnLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun (1 Admin + 2 User)
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
        ]);
        $siswa = User::create([
            'name' => 'Siswa Pengguna',
            'email' => 'siswa@sekolah.com',
            'password' => Hash::make('siswa1234'),
            'role' => 'user',
        ]);
        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@sekolah.com',
            'password' => Hash::make('budi1234'),
            'role' => 'user',
        ]);

        // 2. Kategori
        $categories = [];
        foreach (['Elektronik', 'Alat Lab', 'Alat Olahraga', 'Furnitur'] as $name) {
            $categories[$name] = Category::create(['name' => $name]);
        }

        // 3. Barang (10 data)
        $items = [
            ['Elektronik', 'Laptop ASUS ROG', 'ELK-001', 5, 'baik'],
            ['Elektronik', 'Proyektor Epson', 'ELK-002', 3, 'rusak ringan'],
            ['Elektronik', 'Speaker Portabel', 'ELK-003', 4, 'baik'],
            ['Alat Lab', 'Mikroskop Binokuler', 'LAB-001', 5, 'baik'],
            ['Alat Lab', 'Gelas Ukur 100 ml', 'LAB-002', 20, 'baik'],
            ['Alat Lab', 'Pembakar Bunsen', 'LAB-003', 8, 'baik'],
            ['Alat Olahraga', 'Bola Basket', 'OLR-001', 10, 'baik'],
            ['Alat Olahraga', 'Raket Badminton', 'OLR-002', 12, 'baik'],
            ['Furnitur', 'Meja Guru', 'FNT-001', 10, 'baik'],
            ['Furnitur', 'Kursi Lipat', 'FNT-002', 30, 'rusak ringan'],
        ];

        foreach ($items as [$category, $name, $code, $stock, $condition]) {
            Item::create([
                'category_id' => $categories[$category]->id,
                'name' => $name,
                'code' => $code,
                'stock' => $stock,
                'condition' => $condition,
            ]);
        }

        // 4. Peminjaman (5 data) + pengembalian (3 data)
        // [peminjam, kode barang, jumlah, pinjam (hari lalu), tenggat (hari dari sekarang), status, kembali (hari lalu), kondisi, catatan]
        $loans = [
            [$siswa, 'ELK-001', 1, 3, 4, 'dipinjam', null, null, null],
            [$budi, 'OLR-001', 2, 14, -7, 'terlambat', null, null, null],
            [$siswa, 'LAB-001', 1, 20, -13, 'dikembalikan', 14, 'baik', null],
            [$budi, 'ELK-002', 1, 30, -23, 'dikembalikan', 23, 'rusak ringan', 'Lensa tergores saat disimpan.'],
            [$siswa, 'OLR-002', 2, 10, -3, 'dikembalikan', 4, 'baik', 'Dikembalikan lengkap bersama sarungnya.'],
        ];

        foreach ($loans as [$user, $code, $qty, $daysAgo, $dueIn, $status, $returnedAgo, $condition, $notes]) {
            $item = Item::where('code', $code)->first();

            $loan = Loan::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'quantity' => $qty,
                'loan_date' => now()->subDays($daysAgo),
                'due_date' => now()->addDays($dueIn),
                'status' => $status,
            ]);

            if ($status === 'dikembalikan') {
                ReturnLog::create([
                    'loan_id' => $loan->id,
                    'returned_at' => now()->subDays($returnedAgo),
                    'condition_on_return' => $condition,
                    'notes' => $notes,
                ]);
            } else {
                $item->decrement('stock', $qty); // masih dipinjam, stok berkurang
            }
        }

        // 5. Activity log contoh
        $logs = [
            [$admin, 'login', 'Administrator masuk ke aplikasi'],
            [$admin, 'create_item', 'Menambah barang Laptop ASUS ROG (ELK-001)'],
            [$siswa, 'create_loan', 'Meminjam Laptop ASUS ROG x1'],
            [$admin, 'return_loan', 'Mencatat pengembalian Mikroskop Binokuler dari Siswa Pengguna (kondisi: baik)'],
            [$admin, 'create_category', 'Menambah kategori Furnitur'],
        ];

        foreach ($logs as [$user, $action, $description]) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'description' => $description,
            ]);
        }
    }
}