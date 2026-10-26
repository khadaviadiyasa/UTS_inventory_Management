@extends('layouts.guest')
@section('title', 'Login')
@section('content')
<div class="login-card">
    <div class="login-icon">↗</div>
    <p class="eyebrow">SELAMAT DATANG DI CAKRAWALA</p>
    <h1>Login ke akunmu</h1>
    <p class="muted">Satu ruang untuk melihat stok, mengatur pembelian, dan mencatat penjualan.</p>
    <form method="POST" action="{{ route('login.store') }}" class="form-stack">
        @csrf
        <div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@contoh.test" autocomplete="username" required autofocus><x-field-error name="email" /></div>
        <div class="form-field"><label for="password">Password</label><input id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required><x-field-error name="password" /></div>
        <label class="check-row"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>
        <button class="btn btn-primary btn-block" type="submit">Masuk ke Dashboard <span>→</span></button>
    </form>
    <div class="login-security"><span>⌑</span> Akses dilindungi dengan autentikasi dan role.</div>
</div>
@endsection