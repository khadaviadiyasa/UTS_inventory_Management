# ERD — Inventory & Sales Management System

ERD berikut merangkum skema yang dibuat melalui Laravel migrations.

```mermaid
erDiagram
    USERS ||--o{ PURCHASES : creates
    USERS ||--o{ SALES : creates
    CATEGORIES ||--o{ PRODUCTS : contains
    SUPPLIERS ||--o{ PURCHASES : supplies
    PURCHASES ||--|{ PURCHASE_ITEMS : has
    PRODUCTS ||--o{ PURCHASE_ITEMS : purchased_as
    SALES ||--|{ SALE_ITEMS : has
    PRODUCTS ||--o{ SALE_ITEMS : sold_as

    USERS {
      bigint id PK
      string name
      string email UK
      string password
      string role
    }
    CATEGORIES {
      bigint id PK
      string name UK
      string description
    }
    SUPPLIERS {
      bigint id PK
      string name
      string phone
      string email
      text address
    }
    PRODUCTS {
      bigint id PK
      bigint category_id FK
      string sku UK
      string name
      decimal purchase_price
      decimal selling_price
      unsigned integer stock
      unsigned integer minimum_stock
    }
    PURCHASES {
      bigint id PK
      bigint supplier_id FK
      bigint user_id FK
      string purchase_number UK
      decimal total
      string status
      timestamp purchased_at
    }
    PURCHASE_ITEMS {
      bigint id PK
      bigint purchase_id FK
      bigint product_id FK
      unsigned integer quantity
      decimal unit_cost
      decimal subtotal
    }
    SALES {
      bigint id PK
      bigint user_id FK
      string sale_number UK
      decimal total
      string status
      timestamp sold_at
    }
    SALE_ITEMS {
      bigint id PK
      bigint sale_id FK
      bigint product_id FK
      unsigned integer quantity
      decimal unit_price
      decimal subtotal
    }
```

## Penjelasan relasi Eloquent

- `Category hasMany Product`, sedangkan `Product belongsTo Category`. Foreign key `products.category_id` menunjukkan kategori suatu produk.
- `Supplier hasMany Purchase`, sedangkan `Purchase belongsTo Supplier`. Header pembelian merujuk ke pemasok.
- `User hasMany Purchase` dan `User hasMany Sale`; masing-masing header transaksi menyimpan pembuatnya melalui `user_id`.
- `Purchase hasMany PurchaseItem`; setiap `PurchaseItem belongsTo Purchase` dan `belongsTo Product`. `Sale`/`SaleItem` menggunakan pola yang sama.
- Product memiliki banyak `PurchaseItem` dan `SaleItem`. Tabel item mencatat kuantitas serta harga yang berlaku saat transaksi, sehingga histori tidak berubah ketika harga produk diperbarui.

## Integritas data

- Unique index: `users.email`, `categories.name`, `products.sku`, `purchases.purchase_number`, `sales.sale_number`.
- Foreign key master/produk memakai `restrictOnDelete` agar data yang direferensikan transaksi tidak dihapus tanpa sengaja. Foreign key item ke header menggunakan `cascadeOnDelete` untuk menjaga ketergantungan detail-header; aplikasi tidak menyediakan penghapusan transaksi langsung.
- Pembelian dan penjualan dilakukan dalam `DB::transaction()`. `lockForUpdate()` dipakai pada produk yang diproses agar transaksi bersamaan tidak menjual stok yang sama melebihi persediaan.
- Total dihitung pada server dari detail item, bukan dari nilai total kiriman browser.
