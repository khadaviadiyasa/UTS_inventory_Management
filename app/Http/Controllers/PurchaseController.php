<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $purchases = Purchase::with(['supplier', 'user'])->withCount('items')
            ->when(!$request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->filled('q'), fn ($query) => $query->where('purchase_number', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('purchased_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('purchased_at', '<=', $request->input('to')))
            ->latest('purchased_at')->paginate(10)->withQueryString();
        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, PurchaseService $service): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ], [
            'items.required' => 'Tambahkan minimal satu produk pada pembelian.',
            'items.min' => 'Tambahkan minimal satu produk pada pembelian.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dimasukkan dua kali. Gabungkan kuantitasnya.',
            'items.*.quantity.min' => 'Kuantitas harus minimal 1.',
            'items.*.unit_cost.min' => 'Harga modal tidak boleh negatif.',
        ]);

        $purchase = $service->create($data, $request->user());
        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil disimpan dan stok sudah ditambahkan.');
    }

    public function show(Request $request, Purchase $purchase): View
    {
        abort_unless($request->user()->isAdmin() || $purchase->user_id === $request->user()->id, 403, 'Anda tidak dapat melihat transaksi milik pengguna lain.');
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($purchase): void {
            $locked = Purchase::query()->lockForUpdate()->with('items')->findOrFail($purchase->id);
            if ($locked->status !== 'completed') {
                throw ValidationException::withMessages(['status' => 'Transaksi ini sudah dibatalkan sebelumnya.']);
            }
            foreach ($locked->items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages(['status' => "Pembelian tidak dapat dibatalkan karena stok {$product->name} saat ini {$product->stock}, lebih kecil dari kuantitas yang perlu dikembalikan."]);
                }
                $product->decrement('stock', $item->quantity);
            }
            $locked->update(['status' => 'cancelled']);
        }, attempts: 3);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian dibatalkan dan stok disesuaikan.');
    }
}