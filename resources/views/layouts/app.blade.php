<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">CA</span>
            <span><strong>Cakrawala</strong><small>Inventory & Sales</small></span>
        </a>
        <div class="sidebar-label">MENU UTAMA</div>
        <nav class="nav-menu">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon">▦</span>Dashboard</a>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><span class="nav-icon">▣</span>Produk</a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><span class="nav-icon">◫</span>Kategori</a>
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="nav-icon">♧</span>Supplier</a>
            <a href="{{ route('low-stock.index') }}" class="nav-link {{ request()->routeIs('low-stock.*') ? 'active' : '' }}"><span class="nav-icon">⚠</span>Stok Menipis</a>
            <div class="sidebar-label nav-gap">TRANSAKSI</div>
            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}"><span class="nav-icon">↙</span>Pembelian / Stock-in</a>
            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}"><span class="nav-icon">↗</span>Penjualan / Stock-out</a>
            @if(auth()->user()->isAdmin())
                <div class="sidebar-label nav-gap">ADMINISTRASI</div>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="nav-icon">♙</span>Pengguna & Role</a>
            @endif
        </nav>
        <div class="sidebar-footer">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-mini"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role === 'admin' ? 'Admin / Owner' : 'Staff' }}</small></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-icon" type="submit" title="Logout" aria-label="Logout">↪</button></form>
        </div>
    </aside>
    <main class="main-area">
        <header class="topbar">
            <div><span class="eyebrow">CAKRAWALA · INVENTORY SYSTEM</span><h1>@yield('page-title', 'Dashboard')</h1></div>
            <div class="topbar-right"><span class="today-pill">{{ now()->translatedFormat('D, d M Y') }}</span><span class="role-pill {{ auth()->user()->isAdmin() ? 'admin' : 'staff' }}">{{ auth()->user()->isAdmin() ? 'ADMIN' : 'STAFF' }}</span></div>
        </header>
        <section class="content-area">
            @include('partials.flash')
            @yield('content')
        </section>
        <footer class="app-footer">Cakrawala · Inventory & Sales <span>Data transaksi tercatat otomatis</span></footer>
    </main>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>