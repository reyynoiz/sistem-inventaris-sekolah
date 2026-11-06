<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // Pastikan status "terlambat" sudah diperbarui sebelum angka dihitung
        Loan::markOverdue();
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('dashboard.admin', [
                'totalItems' => Item::count(),
                'totalStock' => (int) Item::sum('stock'),
                'borrowed' => Loan::active()->count(),
                'overdue' => Loan::where('status', 'terlambat')->count(),
                'topItems' => Item::withCount('loans')
                    ->having('loans_count', '>', 0)
                    ->orderByDesc('loans_count')
                    ->take(5)
                    ->get(),
                'topUsers' => User::withCount('loans')
                    ->having('loans_count', '>', 0)
                    ->orderByDesc('loans_count')
                    ->take(5)
                    ->get(),
                'overdueLoans' => Loan::with(['user', 'item'])
                    ->where('status', 'terlambat')
                    ->orderBy('due_date')
                    ->take(5)
                    ->get(),
            ]);
        }

        return view('dashboard.user', [
            'borrowed' => $user->loans()->active()->count(),
            'overdue' => $user->loans()->where('status', 'terlambat')->count(),
            'total' => $user->loans()->count(),
            'latestLoans' => $user->loans()->with('item')->latest('loan_date')->latest('id')->take(5)->get(),
        ]);
    }
}