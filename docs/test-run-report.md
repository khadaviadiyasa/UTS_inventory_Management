# Test run report

Tanggal pemeriksaan statis: 26 Oktober 2026.

## Pemeriksaan yang dilakukan saat paket dibuat

| Pemeriksaan | Hasil | Catatan |
|---|---|---|
| PHP syntax lint (`php -l`) untuk 54 file PHP non-Blade (52 file di direktori aplikasi/config/migration/route/test, ditambah `artisan` dan `public/index.php`) | **PASS** | Pemeriksaan sintaks statis, bukan bukti aplikasi berjalan end-to-end. Blade template tidak dikompilasi karena framework dependency belum terpasang. |
| JavaScript syntax (`node --check`) | **PASS** untuk `public/js/app.js` dan `public/js/transactions.js` | Memeriksa syntax, bukan interaksi browser end-to-end. |
| `composer.json` dan `phpunit.xml` parse | **PASS** | JSON dan XML valid. |
| Laravel feature tests (`php artisan test`) | **Blocked / belum dijalankan** | Composer tidak tersedia di lingkungan build, resolusi DNS ke `getcomposer.org` gagal saat dicoba, dan PHP build environment tidak memiliki ekstensi `pdo_mysql` maupun `pdo_sqlite`. Percobaan `php artisan test` berhenti dengan pesan `Failed opening required .../vendor/autoload.php` karena dependency belum ada. Dependency Laravel tidak dapat di-install atau aplikasi tidak dapat boot di lingkungan ini. |
| Browser / end-to-end test | **Belum dijalankan** | Memerlukan Laravel yang telah di-install dan database lokal aktif. |

## Cara merekam hasil runtime pada komputer sendiri

1. Install dependency dengan `composer install`.
2. Siapkan `.env`, buat database, lalu jalankan `php artisan migrate --seed`.
3. Jalankan `php artisan test`.
4. Ganti kolom hasil pada laporan ini sesuai output command yang benar-benar terlihat. Simpan output terminal sebagai `docs/screenshots/test-run.png` atau lampirkan file teks hasil terminal.
5. Uji alur pembelian dan penjualan melalui browser, lalu simpan screenshot aktual sesuai checklist `docs/screenshots/README.md`.

**Penting:** tidak ada klaim bahwa feature test Laravel lulus sebelum perintah di atas berjalan tanpa error.
