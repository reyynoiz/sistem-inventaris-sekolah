<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnLogRequest;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Loan;
use App\Models\ReturnLog;
use Illuminate\Support\Facades\DB;

class ReturnLogController extends Controller
{
    public function create(Loan $loan)
    {
        if (! $this->canReturn($loan)) {
            return redirect()->route('loans.index')->with('error', 'Peminjaman ini sudah dikembalikan atau tidak valid.');
        }

        return view('returns.create', ['loan' => $loan->load('user', 'item')]);
    }

    public function store(ReturnLogRequest $request, Loan $loan)
    {
        $done = DB::transaction(function () use ($request, $loan) {
            // Kunci baris peminjaman: klik ganda tidak bisa mencatat dua pengembalian
            $locked = Loan::lockForUpdate()->findOrFail($loan->id);

            if (! $this->canReturn($locked)) {
                return false;
            }

            ReturnLog::create($request->validated() + ['loan_id' => $locked->id]);
            $locked->update(['status' => 'dikembalikan']);
            Item::withTrashed()->whereKey($locked->item_id)->increment('stock', $locked->quantity);

            return true;
        });

        if (! $done) {
            return redirect()->route('loans.index')->with('error', 'Peminjaman ini sudah dikembalikan atau tidak valid.');
        }

        $loan->load('user', 'item');
        ActivityLog::record(
            'return_loan',
            "Mencatat pengembalian {$loan->item->name} dari {$loan->user->name} (kondisi: {$request->condition_on_return})"
        );

        return redirect()->route('loans.index')->with('success', 'Pengembalian dicatat dan stok bertambah.');
    }

    private function canReturn(Loan $loan): bool
    {
        return in_array($loan->status, ['dipinjam', 'terlambat'], true)
            && ! $loan->returnLog()->exists();
    }
}