<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $sales = Sale::with('user')->withCount('items')
            ->when(!$request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->filled('q'), fn ($query) => $query->where('sale_number', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('sold_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('sold_at', '<=', $request->input('to')))
            ->latest('sold_at')->paginate(10)->withQueryString();
        return view('sales.index', compact('sales'));
    }

    public function create(): View
    {
        return view('sales.create', ['products' => Product::orderBy('name')->get()]);
    }

    public function store(Request $request, SaleService $service): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'items.required' => 'Tambahkan minimal satu produk pada penjualan.',
            'items.min' => 'Tambahkan minimal satu produk pada penjualan.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dimasukkan dua kali. Gabungkan kuantitasnya.',
            'items.*.quantity.min' => 'Kuantitas harus minimal 1.',
        ]);

        $sale = $service->create($data, $request->user());
        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan berhasil disimpan dan stok sudah dikurangi.');
    }

    public function show(Request $request, Sale $sale): View
    {
        abort_unless($request->user()->isAdmin() || $sale->user_id === $request->user()->id, 403, 'Anda tidak dapat melihat transaksi milik pengguna lain.');
        $sale->load(['user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($sale): void {
            $locked = Sale::query()->lockForUpdate()->with('items')->findOrFail($sale->id);
            if ($locked->status !== 'completed') {
                throw ValidationException::withMessages(['status' => 'Transaksi ini sudah dibatalkan sebelumnya.']);
            }
            foreach ($locked->items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);
            }
            $locked->update(['status' => 'cancelled']);
        }, attempts: 3);

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan dibatalkan dan stok dikembalikan.');
    }
}