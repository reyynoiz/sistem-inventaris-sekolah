<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->q, fn ($query, $search) => $query->where(
                fn ($w) => $w->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
            ))
            ->when($request->action, fn ($query, $action) => $query->where('action', $action))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}