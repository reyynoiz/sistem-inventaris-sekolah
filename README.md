# Sistem Inventaris Sekolah

Aplikasi web untuk mencatat barang inventaris sekolah, peminjamannya, dan pengembaliannya secara terpusat. Dibuat sebagai proyek fullstack Gamelab dengan Laravel 11.

**Pembuat:** Rois Dhanys Riyansyah

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

### Admin
- **Dashboard ringkasan:** total barang, total stok, sedang dipinjam, terlambat, barang paling sering dipinjam, pengguna paling aktif, dan daftar peminjaman terlambat.
- **Kelola kategori dan barang:** (kode unik, stok, kondisi, foto barang, soft delete) dengan pencarian, filter, dan pagination.
- **Manajemen Peminjaman:** Tambah peminjaman atas nama pengguna lain, dan catat pengembalian lengkap dengan tanggal, kondisi barang, dan catatan.
- **Kelola akun pengguna:** beserta role.
- **Activity Log:** Lihat jejak aktivitas dengan pencarian dan filter jenis aksi.

### User (Guru atau Siswa)
- **Dashboard pribadi:** jumlah peminjaman aktif, terlambat, dan total, plus peminjaman terakhir.
- **Katalog Barang:** Melihat daftar barang beserta fotonya.
- **Peminjaman Mandiri:** Meminjam barang atas nama sendiri, dan melihat riwayat peminjaman sendiri.

### Semua Pengguna
- **Login dan registrasi** (Laravel Breeze). Akun baru selalu berperan user.
- **Halaman profil:** foto profil, nama dan email, ubah password.
- **Navigasi Dinamis:** Menu dan halaman menyesuaikan role.

## Teknologi

| Bagian | Yang dipakai |
|---|---|
| **Framework** | Laravel 11 (PHP 8.2) |
| **Tampilan** | Blade, Tailwind CSS, Alpine.js, Vite |
| **Autentikasi** | Laravel Breeze (stack Blade) |
| **Database** | MySQL |
| **Pengujian** | PHPUnit (Unit dan Feature test, SQLite di memori) |

## Instalasi

**Prasyarat:** PHP 8.2 atau lebih baru (ekstensi gd), Composer, Node.js dan npm, serta MySQL (misalnya dari XAMPP).

1. Clone repositori dan masuk ke folder proyek:
   ```bash
   git clone <url-repository-ini>
   cd sistem-inventaris-sekolah
