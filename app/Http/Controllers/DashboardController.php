<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();
        $isAdmin = auth()->user()->isAdmin();
        $sales = Sale::query()->where('status', 'completed')
            ->when(!$isAdmin, fn ($query) => $query->where('user_id', auth()->id()));

        return view('dashboard.index', [
            'productCount' => Product::count(),
            'purchaseCount' => Purchase::where('status', 'completed')->count(),
            'salesCount' => (clone $sales)->count(),
            'todaySales' => (clone $sales)->whereDate('sold_at', $today)->sum('total'),
            'monthSales' => (clone $sales)->where('sold_at', '>=', $monthStart)->sum('total'),
            'lowStockCount' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
            'lowStockProducts' => Product::with('category')->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->limit(6)->get(),
            'recentSales' => Sale::with('user')->when(!$isAdmin, fn ($query) => $query->where('user_id', auth()->id()))->latest('sold_at')->limit(5)->get(),
            'recentPurchases' => Purchase::with(['supplier', 'user'])->when(!$isAdmin, fn ($query) => $query->where('user_id', auth()->id()))->latest('purchased_at')->limit(5)->get(),
        ]);
    }

    public function lowStock(): View
    {
        request()->validate(['q' => ['nullable', 'string', 'max:100']]);
        $products = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->when(request('q'), fn ($query, $q) => $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")))
            ->orderBy('stock')->paginate(10)->withQueryString();

        return view('dashboard.low-stock', compact('products'));
    }
}