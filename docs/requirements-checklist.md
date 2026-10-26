# Requirement traceability checklist

Berdasarkan brief UTS pada PPT terlampir.

| Requirement | Implementasi utama | Bukti uji / catatan |
|---|---|---|
| Login/logout dan password hashing | `Auth/LoginController`, `User` hashed cast, login Blade | `AuthenticationTest` |
| Admin vs Staff server-side | `RoleMiddleware`, `routes/web.php`, ownership checks di controller | Test role dan ownership |
| Category CRUD | `CategoryController`, migration `categories` | Validasi nama unik; blok hapus jika dipakai produk |
| Product CRUD/detail | `ProductController`, migration `products` | SKU unik; harga dan stok divalidasi; riwayat transaksi |
| Supplier CRUD | `SupplierController`, migration `suppliers` | Blok hapus jika sudah punya purchase |
| User / role management | `UserController`, middleware `role:admin` | Admin tidak bisa menghapus akun sendiri atau admin terakhir |
| Purchase / stock-in | `PurchaseController`, `PurchaseService` | Simpan header/detail, total dan increment stok dalam `DB::transaction()` |
| Sales / stock-out | `SaleController`, `SaleService` | Harga server-side, `lockForUpdate`, validasi stok dan atomic rollback |
| Transaction number, user, timestamp | `purchases` / `sales`, `NumberGenerator` | Unique transaction number; user dan timestamp tercatat |
| Cancel transaction | `PurchaseController::cancel`, `SaleController::cancel` | Stok dipulihkan/dikurangi secara konsisten; Admin-only |
| Search/filter/pagination | index controllers dan Blade filter forms | `paginate(10)->withQueryString()`; query parameter divalidasi |
| Dashboard dinamis | `DashboardController` | Hitung agregat dari query database, data transaksi Staff di-scope ke miliknya |
| Migrations, FK, seeder, Eloquent | `database/migrations`, `DatabaseSeeder`, `app/Models` | `docs/ERD.md`; jalankan `php artisan migrate --seed` |
| Responsive layout dan validation/error states | `public/css/app.css`, partial flash, Blade forms | Periksa manual di desktop/mobile setelah runtime tersedia |
| Test cases | `tests/Feature`, `tests/Unit`, `docs/test-cases.md` | Status eksekusi runtime tercatat di `docs/test-run-report.md` |
| README dan demo account | `README.md`, `DatabaseSeeder` | Akun dummy lokal, tidak menggunakan kredensial pribadi |
| Screenshots dan test evidence | `docs/screenshots/README.md` | **Belum diambil** karena aplikasi tidak dapat dijalankan pada environment build; ambil screenshot aktual setelah setup lokal. |
