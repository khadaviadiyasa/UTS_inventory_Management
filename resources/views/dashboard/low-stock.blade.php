@extends('layouts.app')
@section('title', 'Stok Menipis')
@section('page-title', 'Stok Menipis')
@section('content')
<div class="page-intro"><div><h2>Daftar stok rendah</h2><p>Produk yang stoknya sama dengan atau di bawah minimum stock.</p></div><a class="btn btn-outline" href="{{ route('products.index', ['low_stock' => 1]) }}">Buka di produk →</a></div>
<section class="panel"><form class="filter-row" method="GET"><div class="search-field"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="Cari nama atau SKU..."></div><button class="btn btn-secondary" type="submit">Cari</button><a class="btn btn-ghost" href="{{ route('low-stock.index') }}">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>SKU</th><th>Nama produk</th><th>Kategori</th><th>Stok sekarang</th><th>Minimum</th><th>Selisih</th><th></th></tr></thead><tbody>
@forelse($products as $product)<tr><td><span class="sku-chip">{{ $product->sku }}</span></td><td><a class="table-strong" href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td><td>{{ $product->category->name }}</td><td><strong class="text-danger">{{ $product->stock }}</strong></td><td>{{ $product->minimum_stock }}</td><td><span class="status-badge warning">{{ max(0, $product->minimum_stock - $product->stock) }} di bawah minimum</span></td><td><a class="icon-link" href="{{ route('products.show', $product) }}">Detail →</a></td></tr>@empty<tr><td colspan="7"><div class="empty-state"><span>✓</span><strong>Tidak ada stok rendah</strong><p>Coba ubah pencarian atau cek kembali nanti.</p></div></td></tr>@endforelse
</tbody></table></div><div class="table-bottom">@include('partials.pagination-note', ['paginator' => $products]){{ $products->links() }}</div></section>
@endsection