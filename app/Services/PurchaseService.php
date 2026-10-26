<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(private readonly NumberGenerator $numbers) {}

    /** Persist header, item rows and stock changes atomically. */
    public function create(array $data, User $user): Purchase
    {
        return DB::transaction(function () use ($data, $user): Purchase {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $user->id,
                'purchase_number' => $this->numbers->purchase(),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            $total = 0.0;
            foreach ($data['items'] as $line) {
                $product = Product::query()->lockForUpdate()->findOrFail($line['product_id']);
                $quantity = (int) $line['quantity'];
                $unitCost = round((float) $line['unit_cost'], 2);
                if ($quantity < 1 || $unitCost < 0) {
                    throw ValidationException::withMessages(['items' => 'Kuantitas harus lebih dari 0 dan harga tidak boleh negatif.']);
                }

                $subtotal = round($unitCost * $quantity, 2);
                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);
                $product->increment('stock', $quantity);
                $total += $subtotal;
            }

            $purchase->update(['total' => round($total, 2)]);
            return $purchase->load(['supplier', 'user', 'items.product']);
        }, attempts: 3);
    }
}