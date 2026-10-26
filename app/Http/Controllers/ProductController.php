<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'low_stock' => ['sometimes', 'boolean'],
        ]);
        $products = Product::with('category')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', '%'.$request->string('q').'%')->orWhere('sku', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereColumn('stock', '<=', 'minimum_stock'))
            ->orderBy('name')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();
        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View { return view('products.form', ['product' => new Product(), 'categories' => Category::orderBy('name')->get()]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Product::create($data);
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load('category');
        $purchaseItems = $product->purchaseItems()->with('purchase')->when(!auth()->user()->isAdmin(), fn ($query) => $query->whereHas('purchase', fn ($purchaseQuery) => $purchaseQuery->where('user_id', auth()->id())))->latest()->get();
        $saleItems = $product->saleItems()->with('sale')->when(!auth()->user()->isAdmin(), fn ($query) => $query->whereHas('sale', fn ($saleQuery) => $saleQuery->where('user_id', auth()->id())))->latest()->get();
        $product->setRelation('purchaseItems', $purchaseItems);
        $product->setRelation('saleItems', $saleItems);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->purchaseItems()->exists() || $product->saleItems()->exists()) {
            return back()->with('error', 'Produk memiliki riwayat transaksi, sehingga tidak dapat dihapus. Ubah datanya saja.');
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $skuRule = 'unique:products,sku'.($product ? ','.$product->id : '');
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', $skuRule],
            'name' => ['required', 'string', 'max:150'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'sku.unique' => 'SKU sudah digunakan produk lain.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
        ]);
    }
}