<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $suppliers = Supplier::query()->withCount('purchases')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($sub) => $sub->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')->orWhere('phone', 'like', '%'.$request->string('q').'%')))
            ->orderBy('name')->paginate(10)->withQueryString();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View { return view('suppliers.form', ['supplier' => new Supplier()]); }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validated($request));
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View { return view('suppliers.form', compact('supplier')); }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validated($request));
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki riwayat pembelian.');
        }
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}