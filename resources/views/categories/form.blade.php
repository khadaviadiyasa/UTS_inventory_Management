@extends('layouts.app')
@section('title', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')
@section('page-title', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')
@section('content')
<div class="form-page-head"><a class="back-link" href="{{ route('categories.index') }}">← Kembali ke kategori</a><h2>{{ $category->exists ? 'Perbarui kategori' : 'Buat kategori baru' }}</h2><p>Nama kategori harus unik untuk menjaga konsistensi data.</p></div>
<section class="panel form-panel"><form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">@csrf @if($category->exists)@method('PUT')@endif
<div class="form-field"><label for="name">Nama kategori <span class="required">*</span></label><input id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="100" required placeholder="Contoh: Elektronik"><x-field-error name="name" /></div>
<div class="form-field"><label for="description">Deskripsi</label><textarea id="description" name="description" rows="3" maxlength="255" placeholder="Keterangan singkat kategori...">{{ old('description', $category->description) }}</textarea><x-field-error name="description" /></div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('categories.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $category->exists ? 'Simpan perubahan' : 'Simpan kategori' }}</button></div>
</form></section>
@endsection