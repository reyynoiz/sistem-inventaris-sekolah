<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin & User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Siswa Pengguna',
            'email' => 'siswa@sekolah.com',
            'password' => Hash::make('siswa1234'),
            'role' => 'user',
        ]);

        // 2. Buat Kategori
        $elektronik = Category::create(['name' => 'Elektronik']);
        $furnitur = Category::create(['name' => 'Furnitur']);

        // 3. Buat Barang (Items)
        Item::create([
            'category_id' => $elektronik->id,
            'name' => 'Laptop ASUS ROG',
            'code' => 'ELK-001',
            'stock' => 5,
            'condition' => 'baik',
        ]);

        Item::create([
            'category_id' => $furnitur->id,
            'name' => 'Meja Guru',
            'code' => 'FNT-001',    
            'stock' => 10,
            'condition' => 'baik',
        ]);
    }
}