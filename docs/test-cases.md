# Test cases — UTS Inventory & Sales

Jalankan setelah `composer install`, environment database siap, dan migration tersedia:

```powershell
php artisan test
```

| ID | Area | Skenario | Hasil yang diharapkan | Automated test |
|---|---|---|---|---|
| AUTH-01 | Authentication | Guest membuka `/dashboard` | Redirect ke `/login` | `AuthenticationTest::test_guest_is_redirected_from_dashboard` |
| AUTH-02 | Authentication | Email/password salah | Login ditolak dan error muncul | `AuthenticationTest::test_login_rejects_wrong_password` |
| AUTH-03 | Authorization | Staff membuka `/users` | HTTP 403 | `AuthenticationTest::test_staff_cannot_open_admin_user_management` |
| AUTH-04 | Authorization | Admin membuka `/users` | HTTP 200 | `AuthenticationTest::test_admin_can_open_user_management` |
| PROD-01 | Product | Membuat produk dengan SKU yang sudah ada | Validasi SKU gagal | `InventoryTransactionTest::test_duplicate_sku_is_rejected` |
| PUR-01 | Purchase | Pembelian 4 unit dengan cost Rp750, stok awal 5 | Stok menjadi 9, total Rp3.000, header dan item tersimpan | `InventoryTransactionTest::test_purchase_success_adds_stock_and_saves_total` |
| SALE-01 | Sales | Menjual 3 unit dari stok 5, harga DB Rp1.000 | Stok menjadi 2, total Rp3.000 walau request menyertakan total/harga manipulatif | `InventoryTransactionTest::test_sale_success_reduces_stock_and_uses_server_price` |
| SALE-02 | Stock validation | Jual 6 unit saat stok 5 | Ditolak, stok tetap 5, tidak ada header/item sale | `InventoryTransactionTest::test_insufficient_stock_sale_is_rejected_without_changes` |
| SALE-03 | Rollback | Item pertama valid, item kedua melebihi stok | Semua perubahan di-rollback; stok kedua produk tidak berubah dan header/detail tidak tersimpan | `InventoryTransactionTest::test_sale_rolls_back_all_items_if_a_later_item_has_insufficient_stock` |
| CANCEL-01 | Cancel | Admin membatalkan penjualan yang sudah selesai | Status menjadi cancelled dan stok dipulihkan satu kali | `InventoryTransactionTest::test_admin_cancels_sale_and_restores_stock_once` |
| UI-01 | Search & pagination | Cari/filter daftar master/transaksi | Query tetap diterapkan saat pindah halaman | Pemeriksaan manual saat demo |
| DASH-01 | Dashboard | Buat transaksi baru | Angka penjualan/stok rendah berasal dari database dan berubah sesuai transaksi | Pemeriksaan manual saat demo |

## Catatan hasil

Test otomatis pada tabel di atas **sudah ditulis dalam source code**, tetapi status eksekusi test framework bergantung pada environment. Lihat `test-run-report.md`; tidak boleh menandai feature test sebagai pass sebelum `php artisan test` benar-benar dijalankan pada mesin dengan dependency dan driver database terpasang.
