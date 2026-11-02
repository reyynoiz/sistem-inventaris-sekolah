<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('items')
            ->when($request->q, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create', ['category' => new Category]);
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());
        ActivityLog::record('create_category', "Menambah kategori {$category->name}");

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        ActivityLog::record('update_category', "Mengubah kategori {$category->name}");

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        // Barang yang sudah di soft delete tetap menahan penghapusan kategori
        if ($category->items()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori masih dipakai barang, jadi tidak bisa dihapus.');
        }

        $category->delete();
        ActivityLog::record('delete_category', "Menghapus kategori {$category->name}");

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}