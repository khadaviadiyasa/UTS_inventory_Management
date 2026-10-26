<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $categories = Category::query()
            ->withCount('products')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')->paginate(10)->withQueryString();
        return view('categories.index', compact('categories'));
    }

    public function create(): View { return view('categories.form', ['category' => new Category()]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:categories,name'], 'description' => ['nullable', 'string', 'max:255']]);
        Category::create($data);
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View { return view('categories.form', compact('category')); }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$category->id], 'description' => ['nullable', 'string', 'max:255']]);
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}