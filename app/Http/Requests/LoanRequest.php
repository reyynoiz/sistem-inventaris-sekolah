<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAdmin = $this->user()->isAdmin();

        return [
            // Admin memilih peminjam dan tanggal pinjam; User selalu atas nama sendiri dan hari ini
            'user_id' => $isAdmin ? ['required', 'exists:users,id'] : ['prohibited'],
            'loan_date' => $isAdmin ? ['required', 'date', 'before_or_equal:today'] : ['prohibited'],
            'item_id' => ['required', Rule::exists('items', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date', 'after_or_equal:'.($isAdmin ? 'loan_date' : 'today')],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'peminjam',
            'loan_date' => 'tanggal pinjam',
            'item_id' => 'barang',
            'quantity' => 'jumlah',
            'due_date' => 'tenggat pengembalian',
        ];
    }
}