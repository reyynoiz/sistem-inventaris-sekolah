<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('items', 'code')->ignore($this->route('item')),
            ],
            'stock' => ['required', 'integer', 'min:0'],
            'condition' => ['required', Rule::in(['baik', 'rusak ringan', 'rusak berat'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'name' => 'nama barang',
            'code' => 'kode barang',
            'stock' => 'stok',
            'condition' => 'kondisi',
        ];
    }
}