<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanRequest;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        Loan::markOverdue();
        $user = $request->user();

        $loans = Loan::with(['user', 'item', 'returnLog'])
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($request->q, function ($query, $search) use ($user) {
                $query->where(function ($w) use ($search, $user) {
                    $w->whereHas('item', fn ($i) => $i->withTrashed()->where('name', 'like', "%{$search}%"));

                    if ($user->isAdmin()) {
                        $w->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                    }
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest('loan_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        return view('loans.create', [
            'items' => Item::where('stock', '>', 0)->orderBy('name')->get(),
            'users' => User::where('role', 'user')->orderBy('name')->get(),
        ]);
    }

    public function store(LoanRequest $request)
    {
        $isAdmin = $request->user()->isAdmin();

        $loan = DB::transaction(function () use ($request, $isAdmin) {
            // lockForUpdate: dua peminjaman bersamaan tidak bisa menembus stok
            $item = Item::lockForUpdate()->findOrFail($request->item_id);

            if ($item->stock < $request->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$item->name} hanya tersisa {$item->stock}.",
                ]);
            }

            $loan = Loan::create([
                'user_id' => $isAdmin ? $request->user_id : $request->user()->id,
                'item_id' => $item->id,
                'quantity' => $request->quantity,
                'loan_date' => $isAdmin ? $request->loan_date : now()->toDateString(),
                'due_date' => $request->due_date,
                'status' => 'dipinjam',
            ]);

            $item->decrement('stock', $request->quantity);

            return $loan;
        });

        $loan->load('item', 'user');
        ActivityLog::record(
            'create_loan',
            $isAdmin
                ? "Menambah peminjaman {$loan->item->name} x{$loan->quantity} untuk {$loan->user->name}"
                : "Meminjam {$loan->item->name} x{$loan->quantity}"
        );

        return redirect()->route('loans.index')->with('success', 'Peminjaman berhasil dicatat dan stok berkurang.');
    }
}