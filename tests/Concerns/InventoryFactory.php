<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;

/**
 * Pembuat data uji. Model proyek ini belum punya factory,
 * jadi data dibuat langsung lewat Model::create().
 */
trait InventoryFactory
{
    private static int $sequence = 0;

    protected function makeUser(string $role = 'user', array $overrides = []): User
    {
        $number = ++self::$sequence;

        return User::create(array_merge([
            'name' => ucfirst($role).' Uji '.$number,
            'email' => $role.$number.'@uji.test',
            'password' => 'password',
            'role' => $role,
        ], $overrides));
    }

    protected function makeCategory(?string $name = null): Category
    {
        return Category::create(['name' => $name ?? 'Kategori Uji '.++self::$sequence]);
    }

    protected function makeItem(array $overrides = []): Item
    {
        $number = ++self::$sequence;

        $attributes = array_merge([
            'name' => 'Barang Uji '.$number,
            'code' => sprintf('TST-%04d', $number),
            'stock' => 5,
            'condition' => 'baik',
        ], $overrides);

        $attributes['category_id'] ??= $this->makeCategory()->id;

        return Item::create($attributes);
    }

    /**
     * Membuat peminjaman. Peminjaman yang belum kembali ikut mengurangi stok barang,
     * sama seperti yang dilakukan aplikasi.
     */
    protected function makeLoan(User $user, Item $item, array $overrides = []): Loan
    {
        $loan = Loan::create(array_merge([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'loan_date' => now()->subDays(2)->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'dipinjam',
        ], $overrides));

        if (in_array($loan->status, ['dipinjam', 'terlambat'], true)) {
            $item->decrement('stock', $loan->quantity);
        }

        return $loan;
    }
}
