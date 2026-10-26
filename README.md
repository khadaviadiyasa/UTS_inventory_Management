UTS Inventory Sales Cakrawala
Project ini dibuat untuk memenuhi tugas UTS mata kuliah pemrograman web. Aplikasi Inventory Sales Cakrawala dibuat untuk membantu proses pengelolaan data barang dan transaksi penjualan supaya lebih rapi dan mudah dipantau.

Project ini menggunakan framework Laravel dan database MySQL.
Fitur Aplikasi
Beberapa fitur yang direncanakan/tersedia pada aplikasi ini antara lain:
- Login dan logout pengguna.
- Dashboard untuk melihat ringkasan data.
- Mengelola data kategori barang.
- Mengelola data produk/barang.
- Mengelola data supplier.
- Mengelola data pembelian.
- Mengelola transaksi penjualan.
- Mengelola data pengguna.
Catatan: fitur yang bisa digunakan menyesuaikan dengan implementasi source code project.

Teknologi yang Digunakan
- PHP
- Laravel
- MySQL
- HTML, CSS, dan JavaScript
- Composer
- XAMPP sebagai server lokal (opsional)
Persiapan Sebelum Menjalankan Project
Pastikan perangkat sudah memiliki:
1. PHP yang sesuai dengan versi Laravel pada project.
2. Composer.
3. MySQL atau XAMPP.
4. Git (opsional).
5. Browser seperti Google Chrome.
Cara Menjalankan Project
1. Ekstrak project
Ekstrak file project ke folder kerja. Jika menggunakan XAMPP, project bisa diletakkan di folder C:\xampp\htdocs.
2. Buka terminal pada folder project
Masuk ke folder utama Laravel, yaitu folder yang memiliki file artisan dan composer.json.
cd uts_inventory_sales
3. Install dependency
composer install
4. Buat file environment
Salin file .env.example menjadi .env. Di Windows, bisa menggunakan:
copy .env.example .env
Jika file .env.example tidak tersedia, buat file .env sesuai kebutuhan konfigurasi Laravel.
5. Buat application key
php artisan key:generate
6. Buat database
Jalankan MySQL melalui XAMPP atau aplikasi database yang digunakan. Buat database dengan nama:
CREATE DATABASE uts_inventory_sales;
7. Atur koneksi database
Buka file .env, lalu sesuaikan pengaturan database. Contoh untuk MySQL lokal:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uts_inventory_sales
DB_USERNAME=root
DB_PASSWORD=
Sesuaikan DB_USERNAME dan DB_PASSWORD dengan konfigurasi MySQL di komputer masing-masing.
8. Jalankan migration
php artisan migrate
Jika project menyediakan seeder dan ingin memasukkan data awal, jalankan perintah berikut setelah memastikan isi seeder memang sesuai:
php artisan db:seed
9. Jalankan aplikasi
php artisan serve
Buka alamat yang ditampilkan di terminal, biasanya:

http://127.0.0.1:8000
Struktur Folder Laravel
Berikut beberapa folder utama yang umum digunakan dalam project Laravel ini:
Folder	Kegunaan
app/Models	Menyimpan model yang berhubungan dengan data dan database.
app/Http/Controllers	Mengatur alur request dan proses aplikasi.
resources/views	Menyimpan tampilan halaman menggunakan Blade.
routes	Menyimpan definisi route/URL aplikasi.
database/migrations	Menyimpan struktur tabel database.
database/seeders	Menyimpan data awal untuk database.
public	Menyimpan aset publik seperti CSS, JavaScript, dan gambar.
config	Menyimpan konfigurasi aplikasi.


Pengujian
Setelah aplikasi dijalankan, lakukan pengecekan secara manual:
- [ ] Halaman aplikasi bisa dibuka.
- [ ] Login dan logout berjalan sesuai implementasi.
- [ ] Data kategori bisa ditampilkan dan dikelola.
- [ ] Data produk bisa ditampilkan dan dikelola.
- [ ] Data supplier bisa ditampilkan dan dikelola.
- [ ] Proses pembelian dan penjualan berjalan sesuai implementasi.
- [ ] Data tersimpan di database.
- [ ] Tidak ada error saat berpindah halaman.
Checklist ini perlu diisi berdasarkan pengujian yang benar-benar dilakukan. README ini tidak menyatakan bahwa semua pengujian sudah berhasil dijalankan.
Kendala yang Mungkin Terjadi
1. Error Unknown database 'uts_inventory_sales'

Buat database dengan nama yang sama seperti di .env, lalu jalankan migration.

2. Perintah php artisan tidak dikenali

Pastikan terminal berada di folder utama Laravel yang berisi file artisan.

3. Error dependency atau folder vendor belum ada

Jalankan:
composer install
4. Perubahan konfigurasi database belum terbaca

Jalankan:
php artisan config:clear
Penutup
Project UTS Inventory Sales Cakrawala dibuat sebagai latihan penerapan framework Laravel untuk membangun aplikasi pengelolaan inventori dan penjualan. Dengan aplikasi ini, pengelololaan data diharapkan menjadi lebih terstruktur dan mudah digunakan.
Catatan: README ini ditulis dengan gaya dokumentasi mahasiswa dan bisa disesuaikan lagi dengan fitur, identitas, serta hasil pengujian yang benar-benar ada di source code.
