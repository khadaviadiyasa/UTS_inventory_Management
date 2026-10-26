<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(private readonly NumberGenerator $numbers) {}

    /** Prices are read from the database; the client never determines sale totals. */
    public function create(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user): Sale {
            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => $this->numbers->sale(),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $total = 0.0;
            foreach ($data['items'] as $line) {
                $product = Product::query()->lockForUpdate()->findOrFail($line['product_id']);
                $quantity = (int) $line['quantity'];

                if ($quantity < 1) {
                    throw ValidationException::withMessages(['items' => 'Kuantitas penjualan harus minimal 1.']);
                }
                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup. Stok tersedia: {$product->stock}, diminta: {$quantity}.",
                    ]);
                }

                $unitPrice = (float) $product->selling_price;
                $subtotal = round($unitPrice * $quantity, 2);
                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
                $product->decrement('stock', $quantity);
                $total += $subtotal;
            }

            $sale->update(['total' => round($total, 2)]);
            return $sale->load(['user', 'items.product']);
        }, attempts: 3);
    }
}