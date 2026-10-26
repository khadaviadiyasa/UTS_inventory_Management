<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $admin;
    private Supplier $supplier;
    private Product $product;
    private Product $secondProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::create(['name' => 'Staff Test', 'email' => 'staff@test.local', 'password' => Hash::make('password123'), 'role' => 'staff']);
        $this->admin = User::create(['name' => 'Admin Test', 'email' => 'admin@test.local', 'password' => Hash::make('password123'), 'role' => 'admin']);
        $category = Category::create(['name' => 'Test Category']);
        $this->supplier = Supplier::create(['name' => 'Supplier Test']);
        $this->product = Product::create(['category_id' => $category->id, 'sku' => 'TEST-001', 'name' => 'Produk Test', 'purchase_price' => 800, 'selling_price' => 1000, 'stock' => 5, 'minimum_stock' => 1]);
        $this->secondProduct = Product::create(['category_id' => $category->id, 'sku' => 'TEST-002', 'name' => 'Produk Kedua', 'purchase_price' => 400, 'selling_price' => 600, 'stock' => 1, 'minimum_stock' => 1]);
    }

    public function test_purchase_success_adds_stock_and_saves_total(): void
    {
        $this->actingAs($this->staff)->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'items' => [['product_id' => $this->product->id, 'quantity' => 4, 'unit_cost' => 750]],
        ])->assertRedirect();

        $this->assertSame(9, $this->product->fresh()->stock);
        $this->assertDatabaseHas('purchases', ['user_id' => $this->staff->id, 'total' => 3000, 'status' => 'completed']);
        $this->assertDatabaseHas('purchase_items', ['product_id' => $this->product->id, 'quantity' => 4, 'unit_cost' => 750, 'subtotal' => 3000]);
    }

    public function test_sale_success_reduces_stock_and_uses_server_price(): void
    {
        $this->actingAs($this->staff)->post('/sales', [
            'items' => [['product_id' => $this->product->id, 'quantity' => 3]],
            'total' => 1,
            'items.0.unit_price' => 1,
        ])->assertRedirect();

        $this->assertSame(2, $this->product->fresh()->stock);
        $this->assertDatabaseHas('sales', ['user_id' => $this->staff->id, 'total' => 3000, 'status' => 'completed']);
        $this->assertDatabaseHas('sale_items', ['product_id' => $this->product->id, 'quantity' => 3, 'unit_price' => 1000, 'subtotal' => 3000]);
    }

    public function test_insufficient_stock_sale_is_rejected_without_changes(): void
    {
        $this->actingAs($this->staff)->post('/sales', [
            'items' => [['product_id' => $this->product->id, 'quantity' => 6]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(5, $this->product->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, \App\Models\SaleItem::count());
    }

    public function test_sale_rolls_back_all_items_if_a_later_item_has_insufficient_stock(): void
    {
        $this->actingAs($this->staff)->post('/sales', [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
                ['product_id' => $this->secondProduct->id, 'quantity' => 3],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(5, $this->product->fresh()->stock);
        $this->assertSame(1, $this->secondProduct->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, \App\Models\SaleItem::count());
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $this->actingAs($this->admin)->post('/products', [
            'category_id' => $this->product->category_id, 'sku' => $this->product->sku, 'name' => 'Duplikat SKU',
            'purchase_price' => 100, 'selling_price' => 200, 'stock' => 0, 'minimum_stock' => 0,
        ])->assertSessionHasErrors('sku');
    }


    public function test_purchase_cancel_is_rejected_if_stock_cannot_be_reversed(): void
    {
        $purchase = Purchase::create(['supplier_id' => $this->supplier->id, 'user_id' => $this->staff->id, 'purchase_number' => 'PCH-TEST-CANCEL', 'total' => 2400, 'status' => 'completed', 'purchased_at' => now()]);
        $purchase->items()->create(['product_id' => $this->product->id, 'quantity' => 3, 'unit_cost' => 800, 'subtotal' => 2400]);
        $this->product->update(['stock' => 1]);

        $this->actingAs($this->admin)->patch('/purchases/'.$purchase->id.'/cancel')->assertSessionHasErrors('status');
        $this->assertSame(1, $this->product->fresh()->stock);
        $this->assertDatabaseHas('purchases', ['id' => $purchase->id, 'status' => 'completed']);
    }

    public function test_admin_cancels_sale_and_restores_stock_once(): void
    {
        $sale = Sale::create(['user_id' => $this->staff->id, 'sale_number' => 'SAL-TEST-001', 'total' => 2000, 'status' => 'completed', 'sold_at' => now()]);
        $sale->items()->create(['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 1000, 'subtotal' => 2000]);
        $this->product->decrement('stock', 2);

        $this->actingAs($this->admin)->patch('/sales/'.$sale->id.'/cancel')->assertRedirect();
        $this->assertSame(5, $this->product->fresh()->stock);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'status' => 'cancelled']);
    }
}