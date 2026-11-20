# Sistem Inventaris Sekolah

Aplikasi web untuk mencatat barang inventaris sekolah, peminjamannya, dan pengembaliannya secara terpusat. Dibuat sebagai proyek fullstack Gamelab dengan Laravel 11.

Pembuat: Rois Dhanys Riyansyah

## Daftar isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Instalasi](#instalasi)
- [Akun seeder](#akun-seeder)
- [Struktur database](#struktur-database)
- [Aturan bisnis](#aturan-bisnis)
- [Menjalankan tes](#menjalankan-tes)
- [Konvensi kode](#konvensi-kode)
- [Pemecahan masalah](#pemecahan-masalah)
- [Rencana pengembangan](#rencana-pengembangan)

## Fitur

**Admin**

- Dashboard ringkasan: total barang, total stok, sedang dipinjam, terlambat, barang paling sering dipinjam, pengguna paling aktif, dan daftar peminjaman terlambat.
- Kelola kategori dan barang (kode unik, stok, kondisi, foto barang, soft delete) dengan pencarian, filter, dan pagination.
- Tambah peminjaman atas nama pengguna lain, dan catat pengembalian lengkap dengan tanggal, kondisi barang, dan catatan.
- Kelola akun pengguna beserta role.
- Lihat Activity Log (pencarian dan filter jenis aksi).

**User (guru atau siswa)**

- Dashboard pribadi: jumlah peminjaman aktif, terlambat, dan total, plus peminjaman terakhir.
- Melihat daftar barang beserta fotonya.
- Meminjam barang atas nama sendiri, dan melihat riwayat peminjaman sendiri.

**Semua pengguna**

- Login dan registrasi (Laravel Breeze). Akun baru selalu berperan `user`.
- Halaman profil: foto profil, nama dan email, ubah password.
- Menu dan halaman menyesuaikan role.

## Teknologi

| Bagian | Yang dipakai |
|---|---|
| Framework | Laravel 11 (PHP 8.2) |
| Tampilan | Blade, Tailwind CSS, Alpine.js, Vite |
| Autentikasi | Laravel Breeze (stack Blade) |
| Database | MySQL |
| Pengujian | PHPUnit (Unit dan Feature test, SQLite di memori) |

## Instalasi

Prasyarat: PHP 8.2 atau lebih baru (ekstensi `gd`), Composer, Node.js dan npm, serta MySQL (misalnya dari XAMPP).

```bash
git clone <url-repository-ini>
cd sistem-inventaris-sekolah

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Buat database kosong di MySQL, misalnya `db_inventaris` (lewat phpMyAdmin atau `CREATE DATABASE db_inventaris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`). Lalu atur `.env`:

```
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_inventaris
DB_USERNAME=root
DB_PASSWORD=
```

`APP_LOCALE=id` membuat nama bulan tampil dalam bahasa Indonesia, dan `APP_TIMEZONE=Asia/Jakarta` membuat jam di Activity Log sesuai WIB.

Jalankan migrasi, seeder, dan tautan penyimpanan foto:

```bash
php artisan migrate --seed
php artisan storage:link
```

Jalankan aplikasi di dua terminal:

```bash
npm run dev          # terminal 1: Vite
php artisan serve    # terminal 2: buka http://127.0.0.1:8000
```

Setiap kali ada file migration baru, jalankan `php artisan migrate` sebelum mencoba fiturnya.

## Akun seeder

| Role | Email | Password |
|---|---|---|
| Admin | admin@sekolah.com | admin1234 |
| User | siswa@sekolah.com | siswa1234 |
| User | budi@sekolah.com | budi1234 |

Ganti semua password ini bila aplikasi dipasang di luar lingkungan belajar.

Data contoh yang ikut dibuat: 4 kategori, 10 barang, 5 peminjaman (1 dipinjam, 1 terlambat, 3 dikembalikan), 3 catatan pengembalian, dan beberapa catatan Activity Log. Untuk mengembalikan data ke kondisi awal: `php artisan migrate:fresh --seed` (menghapus seluruh data).

## Struktur database

Enam tabel utama:

| Tabel | Isi | Catatan |
|---|---|---|
| `users` | Akun, `role` (`admin` atau `user`), foto profil | |
| `categories` | Kategori barang | |
| `items` | Barang: kategori, nama, kode unik, stok, kondisi, foto | Soft delete |
| `loans` | Peminjaman: peminjam, barang, jumlah, tanggal pinjam, tenggat, status (`dipinjam`, `dikembalikan`, `terlambat`) | Soft delete |
| `return_logs` | Detail pengembalian: tanggal, kondisi saat kembali, catatan | Soft delete, satu per peminjaman |
| `activity_logs` | Jejak aktivitas: pengguna, jenis aksi, deskripsi | |

Relasi:

- `categories` 1 ke banyak `items`
- `users` 1 ke banyak `loans`
- `items` 1 ke banyak `loans`
- `loans` 1 ke 1 `return_logs` (kolom `loan_id` bersifat unik)
- `users` 1 ke banyak `activity_logs`

Kunci asing memakai `restrict` agar data yang masih dipakai tidak terhapus berantai. Pada `activity_logs`, penghapusan akun hanya mengosongkan kolom `user_id`, sehingga jejaknya tetap ada.

## Aturan bisnis

- **Stok** berkurang saat peminjaman dicatat dan bertambah saat pengembalian dicatat. Keduanya berjalan dalam satu transaksi database dengan penguncian baris, sehingga dua peminjaman bersamaan tidak bisa menembus stok.
- **Status terlambat** diperbarui otomatis saat halaman dashboard atau peminjaman dibuka, bagi peminjaman yang melewati tenggat.
- **Pengembalian** hanya bisa dicatat sekali per peminjaman, baik untuk status `dipinjam` maupun `terlambat`.
- **Peminjaman oleh User** langsung tercatat dan mengurangi stok. Belum ada langkah persetujuan Admin (lihat rencana pengembangan).
- **Penghapusan dibatasi:**
  - kategori yang masih dipakai barang tidak bisa dihapus (termasuk barang yang sudah di-soft-delete);
  - barang yang masih dipinjam tidak bisa dihapus;
  - akun yang sedang dipakai dan akun yang punya riwayat peminjaman tidak bisa dihapus lewat menu Pengguna;
  - Admin tidak bisa menurunkan role akunnya sendiri;
  - Admin dan akun yang punya riwayat peminjaman tidak bisa menghapus akun sendiri lewat halaman profil.
- **Foto** (barang dan profil): format JPG, PNG, atau WebP, maksimal 2 MB, minimal 100 x 100 piksel. Isi file diperiksa di server, nama file diacak, dan foto lama dihapus saat diganti.
- **Activity Log** mencatat operasi tambah, ubah, dan hapus (kategori, barang, pengguna), peminjaman, pengembalian, perubahan foto profil, serta login, logout, dan registrasi.
- **Tampilan:** angka berpemisah ribuan dan tanggal berformat Indonesia (misalnya `1 Feb 2026`).

## Menjalankan tes

```bash
php artisan test
```

Tes berjalan di SQLite memori, jadi database MySQL tidak tersentuh. Syaratnya:

- ekstensi PHP `pdo_sqlite` dan `gd` aktif;
- di `phpunit.xml`, dua baris ini tidak dikomentari:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Kelas dasar `tests/TestCase.php` menghentikan tes lebih dulu bila dua baris itu belum aktif, supaya database pengembangan tidak terhapus.

Cakupan tes:

- **Unit:** model (relasi, scope, status terlambat, soft delete, accessor foto, hash password), pencatat Activity Log, dan kelas penghitung skor reputasi.
- **Feature:** kontrol akses semua halaman per role, CRUD kategori dan barang (termasuk unggah, ganti, dan hapus foto), alur peminjaman dan pengembalian, manajemen pengguna, Activity Log beserta pencatatan login, logout, dan registrasi, dashboard per role, serta profil.

Jalankan sebagian saja dengan `php artisan test --testsuite=Unit`, `--testsuite=Feature`, atau `--filter=NamaTes`.

## Konvensi kode

- Nama tabel plural `snake_case`, model singular `PascalCase`, controller berakhiran `Controller`, dan penamaan file, route, model, serta controller memakai bahasa Inggris.
- Relasi memakai Eloquent Relationship, tanpa JOIN manual.
- Validasi memakai Form Request (`app/Http/Requests`).
- Akses halaman Admin dijaga middleware `role:admin`.
- View Blade dikelompokkan per folder (`items/`, `loans/`, dan seterusnya) dengan komponen bersama di `resources/views/components` (breadcrumb, pesan flash, tombol, kolom form, kartu statistik, avatar, foto barang).

Struktur ringkas:

```
app/
  Http/Controllers/     # Category, Item, Loan, ReturnLog, User, ActivityLog, Dashboard, Profile
  Http/Middleware/      # RoleMiddleware
  Http/Requests/        # Form Request tiap formulir
  Models/               # User, Category, Item, Loan, ReturnLog, ActivityLog
  Services/             # ReputationScorer (penghitung skor, belum terhubung ke alur aplikasi)
database/
  migrations/ seeders/
resources/views/        # layouts (sidebar), components, dan folder per modul
routes/web.php
tests/                  # Unit dan Feature
```

## Pemecahan masalah

| Gejala | Penyebab dan solusi |
|---|---|
| `SQLSTATE[HY000] [2002] ... actively refused it` | MySQL belum menyala. Jalankan MySQL di XAMPP, lalu ulangi. |
| `Unknown column '...' in 'field list'` | Ada migration yang belum dijalankan. Jalankan `php artisan migrate:status`, lalu `php artisan migrate`. |
| Foto tidak tampil | Tautan penyimpanan belum dibuat. Jalankan `php artisan storage:link`. |
| `Vite manifest not found` atau tampilan tanpa gaya | Jalankan `npm run dev`, atau `npm run build` untuk produksi. |
| Perubahan pada file Blade tidak terlihat | Jalankan `php artisan view:clear`, lalu muat ulang dengan Ctrl+F5. |
| Tes berhenti dengan pesan soal database pengembangan | Buka komentar `DB_CONNECTION` dan `DB_DATABASE` di `phpunit.xml`. |

Catatan untuk produksi: setel `APP_DEBUG=false`, jangan pernah memasukkan `.env` ke Git, dan ganti seluruh password akun seeder.

## Rencana pengembangan

Ide di luar spesifikasi minimum yang dapat ditambahkan:

- langkah persetujuan Admin sebelum peminjaman dianggap resmi, dengan pencatatan kondisi awal barang dan foto bukti;
- skor reputasi peminjam (Beta Reputation System) yang menentukan batas pinjam secara bertingkat, lengkap dengan time decay dan pembedaan kerusakan wajar dan lalai (kelas penghitungnya sudah ada di `app/Services/ReputationScorer.php`, placeholder skor ada di halaman profil);
- notifikasi saat mendekati tenggat, ekspor laporan ke PDF atau Excel, dan grafik tren peminjaman di dashboard.