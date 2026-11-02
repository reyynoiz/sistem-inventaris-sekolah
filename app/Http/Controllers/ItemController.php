<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with('category')
            ->when($request->q, fn ($query, $search) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
            ))
            ->when($request->category, fn ($query, $id) => $query->where('category_id', $id))
            ->when($request->condition, fn ($query, $condition) => $query->where('condition', $condition))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('items.index', [
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('items.create', [
            'item' => new Item,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ItemRequest $request)
    {
        $item = Item::create($request->validated());
        ActivityLog::record('create_item', "Menambah barang {$item->name} ({$item->code})");

        return redirect()->route('items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        return view('items.edit', [
            'item' => $item,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ItemRequest $request, Item $item)
    {
        $item->update($request->validated());
        ActivityLog::record('update_item', "Mengubah barang {$item->name} ({$item->code})");

        return redirect()->route('items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        // Barang yang masih dipinjam tidak boleh dihapus
        if ($item->loans()->active()->exists()) {
            return back()->with('error', 'Barang masih dipinjam. Catat pengembaliannya dulu sebelum menghapus.');
        }

        $item->delete(); // soft delete
        ActivityLog::record('delete_item', "Menghapus barang {$item->name} ({$item->code})");

        return redirect()->route('items.index')->with('success', 'Barang berhasil dihapus.');
    }
}