<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'item_id', 'quantity', 'loan_date', 'due_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // withTrashed: menampilkan riwayat meskipun sudah di soft delete
    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function returnLog()
    {
        return $this->hasOne(ReturnLog::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['dipinjam', 'terlambat']);
    }

    /** Tandai peminjaman yang lewat tenggat sebagai "terlambat". */
    public static function markOverdue(): void
    {
        static::where('status', 'dipinjam')
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => 'terlambat']);
    }
}