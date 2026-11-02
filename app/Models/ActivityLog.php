<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Catat aktivitas oleh pengguna yang sedang login
    public static function record(string $action, string $description): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}