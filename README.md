# Aplikasi Data Buku Perpustakaan — Tipe A

Aplikasi tugas UTS Praktikum Pemrograman Web. Implementasi fitur aplikasi hanya memakai `routes/web.php` dan Blade di `resources/views/buku/`; tidak menggunakan Controller buatan, Model, migration untuk tabel tugas, atau file helper. Database dibuat manual melalui phpMyAdmin.

## Kebutuhan
- PHP dan Composer yang sesuai dengan versi Laravel di proyek.
- MySQL/MariaDB (contoh lokal WAMP).
- Ekstensi PDO MySQL aktif.

## Menjalankan
1. Ekstrak proyek dan buka terminal pada folder proyek.
2. Jika `vendor/` belum ada, jalankan `composer install`.
3. Salin `.env.example` menjadi `.env`, lalu sesuaikan nama database, user, password, dan `APP_KEY`.
4. Jalankan `php artisan key:generate` (jika APP_KEY kosong), lalu `php artisan config:clear`.
5. Buka phpMyAdmin dan impor `database_perpustakaan.sql`. Bila NIM berbeda, ubah nama database pada file SQL dan `DB_DATABASE` di `.env` agar sesuai ketentuan `perpustakaan_NIM`.
6. Jalankan `php artisan optimize:clear`, kemudian `php artisan serve`.
7. Buka `http://127.0.0.1:8000/buku`.

## Fitur
- CRUD melalui route GET, POST, GET edit, PUT update, DELETE.
- Pencarian kode buku, judul, ISBN; filter kategori.
- Validasi server-side, sanitasi `trim`/`strip_tags`, `old()`, CSRF, method spoofing.
- PDO prepared statements, `try/catch`, `report($e)` dan pesan error yang aman.
- Status buku: Baru untuk tahun >= 2023, Lama untuk tahun < 2023.

## Struktur terkait tugas
- `routes/web.php`: route dan logika PDO.
- `resources/views/buku/index.blade.php`: form tambah, cari/filter, tabel.
- `resources/views/buku/edit.blade.php`: form edit.
- `resources/views/buku/error.blade.php`: pesan error aman.
- `database_perpustakaan.sql`: skema dan data awal.

Jangan pernah memasukkan `.env` ke GitHub. Gunakan `.env.example` tanpa password pribadi.

## Pengujian
Ikuti sepuluh skenario pada dokumen soal: Read data awal, Create valid, Create tidak valid, duplikat, Update, konflik Update, Delete dengan CSRF, pencarian tiga kolom, filter kategori, dan error handling. Catat hasil aktual setelah menjalankan aplikasi; jangan menyatakan lulus sebelum diuji.
