@extends('layouts.app')
@section('title', 'Buat Pembelian')
@section('page-title', 'Pembelian / Stock-in')
@section('content')
<div class="form-page-head"><a class="back-link" href="{{ route('purchases.index') }}">← Kembali ke pembelian</a><h2>Catat barang masuk</h2><p>Pilih supplier, tambahkan produk, lalu simpan. Stok bertambah otomatis setelah transaksi berhasil.</p></div>
@if($suppliers->isEmpty() || $products->isEmpty())<div class="alert alert-danger"><span class="alert-symbol">!</span><span>Pastikan minimal ada satu supplier dan satu produk sebelum membuat pembelian. Hubungi Admin untuk melengkapi master data.</span></div>@endif
<section class="panel form-panel"><form method="POST" action="{{ route('purchases.store') }}" data-transaction-form data-mode="purchase">@csrf
<div class="form-field"><label for="supplier_id">Supplier <span class="required">*</span></label><select id="supplier_id" name="supplier_id" required><option value="">Pilih supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select><x-field-error name="supplier_id" /></div>
<div class="section-separator"><div><h3>Item pembelian</h3><p>Masukkan minimal satu produk. Harga modal dapat disesuaikan per transaksi.</p></div><button class="btn btn-outline btn-small" type="button" data-add-row>＋ Tambah item</button></div>
<div class="transaction-lines" data-lines></div><div class="line-empty" data-line-empty>Belum ada item. Klik “Tambah item” untuk mulai.</div><x-field-error name="items" />
<div class="total-bar"><span>Total pembelian <small>Nilai dihitung dari kuantitas × harga modal.</small></span><strong data-total>Rp 0</strong></div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('purchases.index') }}">Batal</a><button class="btn btn-primary" type="submit" {{ $suppliers->isEmpty() || $products->isEmpty() ? 'disabled' : '' }}>Simpan pembelian →</button></div></form></section>
@push('scripts')<script>window.inventoryProducts = {!! json_encode($products->map(fn ($p) => ['id'=>$p->id,'name'=>$p->name,'sku'=>$p->sku,'stock'=>$p->stock,'purchase_price'=>(float)$p->purchase_price,'selling_price'=>(float)$p->selling_price])->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};</script><script src="{{ asset('js/transactions.js') }}"></script>@endpush
@endsection