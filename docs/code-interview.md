# Catatan singkat untuk code ownership interview

Berikut poin belajar untuk membantu menjelaskan kode. Jelaskan dengan kata-kata sendiri ketika demo.

### Mengapa `Sale` punya relasi `hasMany(SaleItem)`?
Satu transaksi penjualan dapat memiliki lebih dari satu produk. Header `sales` menyimpan nomor, pembuat, tanggal, status, dan total; item disimpan per baris pada `sale_items`.

### Di mana sistem mencegah stok negatif?
`SaleService::create()` memuat produk dengan `lockForUpdate()`, memeriksa kuantitas dan stok sebelum mengurangi jumlah. Ketika stok kurang, `ValidationException` membuat transaksi gagal, sedangkan `DB::transaction()` membatalkan semua perubahan sebelumnya.

### Mengapa total dihitung oleh server?
Nilai dari browser dapat diubah. Total harus dihitung dari harga yang tersimpan di database dan kuantitas yang telah divalidasi agar transaksi konsisten.

### Apa manfaat `DB::transaction()`?
Header, detail item, dan update stok harus berhasil sebagai satu unit. Jika satu item gagal, database mengembalikan perubahan dalam transaksi tersebut agar tidak ada header setengah jadi atau stok yang berubah sebagian.

### Bagaimana mencegah Staff membuka fungsi Admin lewat URL langsung?
Route mutasi master data dan user dibungkus middleware `role:admin`. Pemeriksaan berjalan di server, sehingga menyembunyikan tombol di Blade bukan satu-satunya proteksi.

### Bagaimana menangani pembatalan transaksi?
Pembatalan dilakukan Admin, hanya untuk status `completed`, dan ditulis dalam DB transaction. Pembatalan sale mengembalikan jumlah ke stok. Pembatalan purchase mengurangi stok kembali hanya jika stok cukup; jika tidak, pembatalan ditolak dengan pesan validasi.

### Apa tujuan unique SKU dan foreign key?
Validasi aplikasi memberikan pesan yang mudah dipahami; constraint database tetap melindungi integritas data walaupun terjadi request bersamaan atau data diakses di luar form.
