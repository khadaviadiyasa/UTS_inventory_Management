<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@inventory.test'],
            ['name' => 'Demo Admin', 'password' => 'password', 'role' => 'admin'],
        );
        $staff = User::updateOrCreate(
            ['email' => 'staff@inventory.test'],
            ['name' => 'Demo Staff', 'password' => 'password', 'role' => 'staff'],
        );

        $accessories = Category::firstOrCreate(['name' => 'Aksesoris Komputer'], ['description' => 'Perangkat pendukung komputer']);
        $peripherals = Category::firstOrCreate(['name' => 'Periferal'], ['description' => 'Perangkat input dan output']);
        $supplier = Supplier::firstOrCreate(
            ['name' => 'PT Sumber Teknologi'],
            ['phone' => '0812-0000-1234', 'email' => 'sales@sumber-teknologi.test', 'address' => 'Jl. Industri No. 12, Indonesia'],
        );

        $keyboard = Product::firstOrCreate(['sku' => 'KEY-MEC-001'], [
            'category_id' => $peripherals->id, 'name' => 'Keyboard Mechanical', 'purchase_price' => 230000,
            'selling_price' => 299000, 'stock' => 0, 'minimum_stock' => 5, 'description' => 'Keyboard mekanis untuk kebutuhan kerja dan belajar.',
        ]);
        $mouse = Product::firstOrCreate(['sku' => 'MOU-WRL-002'], [
            'category_id' => $peripherals->id, 'name' => 'Mouse Wireless', 'purchase_price' => 85000,
            'selling_price' => 129000, 'stock' => 0, 'minimum_stock' => 3, 'description' => 'Mouse nirkabel dengan koneksi USB receiver.',
        ]);
        $hub = Product::firstOrCreate(['sku' => 'USB-HUB-003'], [
            'category_id' => $accessories->id, 'name' => 'USB Hub 4 Port', 'purchase_price' => 50000,
            'selling_price' => 79000, 'stock' => 0, 'minimum_stock' => 4, 'description' => 'Hub USB empat port untuk menambah konektivitas.',
        ]);

        // Seed example transactions only once so running `db:seed` again does not inflate stock.
        if (!\App\Models\Purchase::exists() && !\App\Models\Sale::exists()) {
            app(PurchaseService::class)->create([
                'supplier_id' => $supplier->id,
                'items' => [
                    ['product_id' => $keyboard->id, 'quantity' => 18, 'unit_cost' => 230000],
                    ['product_id' => $mouse->id, 'quantity' => 10, 'unit_cost' => 85000],
                    ['product_id' => $hub->id, 'quantity' => 25, 'unit_cost' => 50000],
                ],
            ], $staff);

            app(SaleService::class)->create([
                'items' => [
                    ['product_id' => $keyboard->id, 'quantity' => 13],
                    ['product_id' => $mouse->id, 'quantity' => 7],
                    ['product_id' => $hub->id, 'quantity' => 11],
                ],
            ], $staff);
        }
    }
}