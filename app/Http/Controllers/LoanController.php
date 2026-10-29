<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Auth::user()->role === 'admin' 
            ? Loan::with(['user', 'item'])->latest()->get()
            : Loan::where('user_id', Auth::id())->with('item')->latest()->get();

        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $items = Item::all(); 
        return view('loans.create', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $item = Item::findOrFail($request->item_id);

        if ($item->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi.'])->withInput();
        }

        Loan::create([
            'user_id' => Auth::id(),
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'loan_date' => now()->toDateString(),
            'due_date' => $request->due_date,
            'status' => 'dipinjam',
        ]);

        $item->decrement('stock', $request->quantity);

        return redirect()->route('loans.index')->with('success', 'Peminjaman berhasil.');
    }

    public function returnItem($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'dipinjam') {
            return back()->with('error', 'Status peminjaman tidak valid.');
        }

        // Kembalikan stok barang
        $loan->item->increment('stock', $loan->quantity);

        // Ubah status peminjaman
        $loan->update([
            'status' => 'dikembalikan'
        ]);

        return back()->with('success', 'Barang berhasil dikembalikan dan stok bertambah otomatis.');
    }
}