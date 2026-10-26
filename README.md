# Cakrawala — Inventory & Sales Management System

**Project UTS Web Programming 2 · Integrated Laravel Project**

Aplikasi web untuk mengelola data inventori, pemasok, pembelian/stock-in, dan penjualan/stock-out menggunakan Laravel, MySQL, Eloquent ORM, Blade, autentikasi session, role-based access control, dan database transaction.

> Project ini disusun dari brief UTS pada PPT yang diberikan. Gunakan akun dummy di bawah hanya untuk demo lokal. Sebelum presentasi, jalankan test, buka aplikasi, ambil screenshot aktual, dan pastikan kamu bisa menjelaskan serta memodifikasi kode sendiri.

## Identitas visual — Cakrawala

Tema antarmuka menggunakan nuansa langit dan garis cakrawala: navy/samudra untuk navigasi, toska sebagai aksen utama, biru langit untuk informasi, serta warna sunrise sebagai aksen hangat. Gaya diterapkan konsisten pada login, dashboard, master data, form, daftar transaksi, detail transaksi, status, dan responsivitas mobile. Tema hanya mengubah presentasi; aturan bisnis, validasi, serta otorisasi tetap berada di lapisan aplikasi Laravel.

## 1. Project overview

Masalah yang ditangani adalah pencatatan stok yang tidak konsisten, penjualan di luar jumlah stok, riwayat transaksi yang sulit ditelusuri, serta kebutuhan akses berbeda antara Admin/Owner dan Staff.

Fokus business logic:

- **Purchase / Stock-in:** header dan seluruh item disimpan dalam satu database transaction; stok bertambah di transaksi yang sama setelah item berhasil disimpan.
- **Sales / Stock-out:** harga jual diambil dari database, stok dikunci ketika diproses, stok yang tidak cukup ditolak dengan pesan jelas, dan header/item/perubahan stok di-rollback bersama jika terjadi kegagalan.
- **Cancel transaction:** hanya Admin yang dapat membatalkan; penjualan mengembalikan stok, sedangkan pembelian hanya dapat dibatalkan bila stok tersedia cukup untuk dikurangi kembali.
- **Data integrity:** SKU dan email unik, foreign key, validasi server-side, CSRF token, hashing password, serta pembatasan role di middleware server.

## 2. Requirements

- PHP **8.2 atau lebih baru** (project menggunakan Laravel 12 agar kompatibel dengan lebih banyak instalasi XAMPP).
- Composer 2.x (Composer Runtime API 2.2+).
- MySQL atau MariaDB (XAMPP dapat digunakan).
- Ekstensi PHP yang umum dibutuhkan Laravel dan database driver, minimal `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `ctype`, `fileinfo`, dan `xml`.
- Browser modern.

Tidak perlu `npm install` atau proses build frontend untuk versi ini: CSS dan JavaScript aplikasi disimpan lokal di `public/`.

## 3. Installation (Windows + XAMPP)

1. Pastikan Apache/MySQL dari XAMPP sudah siap. Untuk menjalankan Laravel via `php artisan serve`, Apache tidak harus digunakan; MySQL harus aktif.
2. Letakkan folder project, misalnya di `C:\xampp\htdocs\uts_inventory_sales`, lalu buka PowerShell pada folder tersebut.
3. Periksa versi dan ekstensi:

   ```powershell
   php -v
   composer --version
   php -m
   ```

4. Pasang dependency Laravel:

   ```powershell
   composer install
   ```

5. Buat file environment:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

6. Buka phpMyAdmin di XAMPP dan buat database bernama `uts_inventory_sales` dengan collation `utf8mb4_unicode_ci` (atau nama lain, asal sama dengan `DB_DATABASE` di `.env`).
7. Sesuaikan koneksi database di `.env` (lihat bagian berikutnya).
8. Buat tabel dan isi demo data:

   ```powershell
   php artisan migrate --seed
   ```

9. Jalankan aplikasi:

   ```powershell
   php artisan serve
   ```

10. Buka `http://127.0.0.1:8000/login`.

Jika project dipindahkan atau di-clone ke perangkat lain, jalankan lagi `composer install`, buat `.env` lokal, `php artisan key:generate`, lalu konfigurasi database dan `php artisan migrate --seed`. File `.env` tidak boleh di-commit.

## 4. Environment setup

Contoh konfigurasi MySQL pada `.env`:

```dotenv
APP_NAME="Cakrawala Inventory & Sales"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uts_inventory_sales
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan instalasi lokal. Jangan menyalin password asli atau file `.env` ke repository. Untuk deployment, matikan `APP_DEBUG` dan gunakan kredensial database khusus.

## 5. Migration & seeding

Migration membuat tabel berikut secara berurutan:

- `users`: akun, email unik, password hash, dan role `admin`/`staff`.
- `categories`: kategori dengan nama unik.
- `suppliers`: nama dan kontak supplier.
- `products`: kategori, SKU unik, harga modal/jual, stok saat ini, dan minimum stock.
- `purchases` dan `purchase_items`: header pembelian dan detail stock-in.
- `sales` dan `sale_items`: header penjualan dan detail stock-out.
- `password_reset_tokens` serta `sessions`: tabel pendukung Laravel.

Foreign key menggunakan aturan restrict untuk master/product yang sudah dipakai transaksi. Produk yang sudah tercatat dalam purchase/sale tidak dapat dihapus secara hard delete. Seeder menambahkan akun demo, kategori, supplier, produk, dan transaksi contoh; stok sample dibuat lewat service transaksi agar pembelian dan penjualan mempengaruhi stok dengan aturan yang sama seperti aplikasi.

Perintah berguna:

```powershell
php artisan migrate:status
php artisan migrate:fresh --seed
php artisan route:list
```

`migrate:fresh` menghapus seluruh tabel dan data lokal. Gunakan hanya ketika database memang boleh di-reset.

## 6. Demo account

| Role | Email | Password |
|---|---|---|
| Admin / Owner | `admin@inventory.test` | `password` |
| Staff | `staff@inventory.test` | `password` |

Ini adalah akun dummy untuk environment lokal, bukan akun pribadi. Ganti password demo sebelum aplikasi dipakai di jaringan publik. Login dilindungi throttling; setelah beberapa percobaan gagal, tunggu sesuai pesan yang tampil.

## 7. Features

### Authentication & access

- Login/logout menggunakan session Laravel.
- Password disimpan sebagai hash.
- Middleware `role:admin` melindungi pengelolaan user, CRUD master data, dan pembatalan transaksi.
- Staff dapat melihat master data dan membuat transaksi, tetapi hanya dapat melihat transaksi yang dibuat oleh dirinya sendiri.

### Master data

- Kategori: tambah, daftar, edit, hapus dengan validasi; kategori yang masih memiliki produk tidak dapat dihapus.
- Produk: daftar, tambah, detail, edit, SKU unik, kategori, harga, stock, minimum stock, pencarian dan filter.
- Supplier: daftar, tambah, edit, cari, dan hapus hanya jika belum punya riwayat pembelian.
- User/role: Admin membuat, mengubah role, mereset password melalui edit, dan menghapus akun tanpa riwayat transaksi (tidak dapat menghapus akun yang sedang login).
- Low-stock list menampilkan produk dengan `stock <= minimum_stock`.

### Transactions

- Purchase memilih satu supplier dan beberapa produk. Kuantitas harus lebih dari nol, harga modal tidak boleh negatif, total dihitung server, lalu stok ditambah.
- Sales menerima beberapa produk. Harga penjualan diambil dari data produk, tidak dipercaya dari browser. Stok tidak cukup menyebabkan transaksi ditolak tanpa perubahan data.
- Nomor transaksi unik, pengguna pembuat, waktu, status, header, item, total dan perubahan stok disimpan.
- Daftar transaksi memiliki pencarian nomor, filter status/tanggal, pagination, serta halaman detail.
- Admin dapat membatalkan transaksi selesai dengan aturan pengembalian stok yang konsisten.

### Dashboard & UI

- Total produk, penjualan hari ini/bulan ini, jumlah penjualan, stok rendah, pembelian/penjualan terbaru.
- Layout responsif untuk desktop dan layar kecil.
- Pesan validasi/error/sukses, empty states, konfirmasi penghapusan/pembatalan.
- CSRF protection dan escaping output menggunakan Blade.

### Run tests

```powershell
php artisan test
```

Test feature menguji guest redirect, login salah, server-side role access, purchase stock-in, sale stock-out, harga jual server-side, stok tidak cukup, rollback multi-item, SKU duplikat, dan pembatalan sale. Lihat `docs/test-cases.md` dan `docs/test-run-report.md` untuk skenario dan hasil pemeriksaan yang benar-benar dilakukan pada paket ini. Karena Composer tidak tersedia saat source dibuat, `composer.lock` belum dapat dihasilkan di lingkungan build; setelah `composer install` berhasil pada komputer kamu, sertakan file `composer.lock` saat commit ke repository agar resolusi dependency dapat direproduksi.

### ERD & screenshots

- ERD dan penjelasan relasi: [`docs/ERD.md`](docs/ERD.md).
- Skenario testing: [`docs/test-cases.md`](docs/test-cases.md).
- Panduan screenshot aplikasi: [`docs/screenshots/README.md`](docs/screenshots/README.md).

Setelah aplikasi berjalan lokal, ambil screenshot nyata dari halaman Dashboard, Product index, Purchase create/detail, dan Sales create/detail. Screenshot harus berasal dari aplikasi yang dijalankan, bukan mockup, dan jangan tampilkan kredensial sensitif.

## Upload ke GitHub

Buat repository kosong di akun GitHub milikmu, lalu jalankan perintah berikut dari folder project (ganti URL dengan URL repository yang kamu buat sendiri):

```powershell
git init
git add .
git commit -m "feat: UTS inventory sales management"
git branch -M main
git remote add origin <URL_REPOSITORY_KAMU>
git push -u origin main
```

Jika repository sudah ada, clone dulu repository tersebut lalu salin/merge isi project ke folder hasil clone sebelum `git add`, `git commit`, dan `git push`. Pastikan `.env` tidak ikut di-commit; `.gitignore` sudah mengecualikannya. Commit juga `composer.lock` setelah berhasil dibuat. Jangan menyatakan project telah diunggah sebelum push berhasil.

## 8. Known limitations

- Tidak ada payment gateway, accounting penuh, multi-warehouse, multi-currency, forecasting, atau integrasi ERP karena berada di luar scope UTS.
- Belum ada email password reset; Admin dapat memperbarui password pengguna lewat halaman edit User.
- Koreksi stok awal/current stock tersedia pada form produk Admin untuk keperluan setup/demo. Perubahan stok operasional sebaiknya lewat Stock-in/sales agar jejak transaksi terjaga.
- Belum tersedia soft delete; data master/produk yang sudah direferensikan transaksi dilindungi dengan larangan delete.
- Screenshot aplikasi nyata harus dibuat setelah aplikasi berjalan di environment lokal.
- `composer.lock` belum tersedia; generate dengan `composer install` pada komputer dengan akses Composer, lalu commit bersama source.
- Pengujian integrasi Laravel perlu dijalankan dengan dependency dan driver database terpasang. Laporan paket ini membedakan lint statis dari feature test yang belum dapat dieksekusi pada lingkungan build.

## Struktur direktori utama

```text
app/
  Http/Controllers/       # Request handling and validation
  Http/Middleware/        # Role-based access
  Models/                 # Eloquent models and relationships
  Services/               # Purchase/sale business logic
bootstrap/                # Laravel application bootstrap
config/                   # Application and database configuration
database/
  migrations/              # Versioned relational schema
  seeders/                 # Dummy demo data
resources/views/           # Blade templates
public/css/                # Responsive UI
public/js/                 # Form interactions
routes/web.php             # Named routes and middleware
tests/Feature/             # Workflow and authorization tests
docs/                      # ERD, test cases and evidence guidance
```
