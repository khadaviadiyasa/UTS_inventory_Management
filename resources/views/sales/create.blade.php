@extends('layouts.app')
@section('title', 'Buat Penjualan')
@section('page-title', 'Penjualan / Stock-out')
@section('content')
<div class="form-page-head"><a class="back-link" href="{{ route('sales.index') }}">← Kembali ke penjualan</a><h2>Catat penjualan baru</h2><p>Harga diambil dari database. Sistem menolak penjualan jika stok tidak mencukupi.</p></div>
@if($products->isEmpty())<div class="alert alert-danger"><span class="alert-symbol">!</span><span>Belum ada produk terdaftar. Minta Admin menambahkan data produk terlebih dahulu.</span></div>@endif
<section class="panel form-panel"><form method="POST" action="{{ route('sales.store') }}" data-transaction-form data-mode="sale">@csrf
<div class="section-separator"><div><h3>Item penjualan</h3><p>Pilih produk dan kuantitas yang dibeli.</p></div><button class="btn btn-outline btn-small" type="button" data-add-row>＋ Tambah item</button></div>
<div class="transaction-lines" data-lines></div><div class="line-empty" data-line-empty>Belum ada item. Klik “Tambah item” untuk mulai.</div><x-field-error name="items" />
<div class="stock-note"><span>ℹ</span><span>Validasi stok dilakukan ulang di server ketika tombol simpan ditekan. Angka total di sini hanya pratinjau.</span></div>
<div class="total-bar"><span>Total penjualan <small>Harga unit mengikuti harga jual produk yang tersimpan.</small></span><strong data-total>Rp 0</strong></div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('sales.index') }}">Batal</a><button class="btn btn-primary" type="submit" {{ $products->isEmpty() ? 'disabled' : '' }}>Simpan penjualan →</button></div></form></section>
@push('scripts')<script>window.inventoryProducts = {!! json_encode($products->map(fn ($p) => ['id'=>$p->id,'name'=>$p->name,'sku'=>$p->sku,'stock'=>$p->stock,'purchase_price'=>(float)$p->purchase_price,'selling_price'=>(float)$p->selling_price])->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};</script><script src="{{ asset('js/transactions.js') }}"></script>@endpush
@endsection