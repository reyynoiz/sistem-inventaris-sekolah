<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class LoanFlowTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    // ---------- Peminjaman ----------

    public function test_user_borrows_and_stock_decreases(): void
    {
        $user = $this->makeUser();
        $item = $this->makeItem(['stock' => 5]);

        $this->actingAs($user)->post('/loans', [
            'item_id' => $item->id,
            'quantity' => 2,
            'due_date' => now()->addDays(3)->toDateString(),
        ])->assertRedirect(route('loans.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('loans', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'status' => 'dipinjam',
        ]);
        $this->assertSame(3, $item->fresh()->stock);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'create_loan']);
    }

    public function test_borrow_validation_and_stock_guard(): void
    {
        $item = $this->makeItem(['stock' => 5]);
        $gone = $this->makeItem();
        $gone->delete();
        $due = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeUser());

        $this->post('/loans', ['item_id' => $item->id, 'quantity' => 99, 'due_date' => $due])->assertSessionHasErrors('quantity');
        $this->post('/loans', ['item_id' => $item->id, 'quantity' => 0, 'due_date' => $due])->assertSessionHasErrors('quantity');
        $this->post('/loans', ['item_id' => $item->id, 'quantity' => 1, 'due_date' => now()->subDay()->toDateString()])->assertSessionHasErrors('due_date');
        $this->post('/loans', ['item_id' => $item->id, 'quantity' => 1, 'due_date' => $due, 'user_id' => $this->makeUser()->id])->assertSessionHasErrors('user_id');
        $this->post('/loans', ['item_id' => $gone->id, 'quantity' => 1, 'due_date' => $due])->assertSessionHasErrors('item_id');

        $this->assertDatabaseCount('loans', 0);
        $this->assertSame(5, $item->fresh()->stock);
    }

    public function test_admin_adds_a_loan_for_a_user(): void
    {
        $borrower = $this->makeUser();
        $item = $this->makeItem();
        $this->actingAs($this->makeUser('admin'));

        $this->post('/loans', [
            'user_id' => $borrower->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(4)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('loans', ['user_id' => $borrower->id, 'item_id' => $item->id]);

        $this->post('/loans', [
            'user_id' => $borrower->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'loan_date' => now()->addDay()->toDateString(),
            'due_date' => now()->addDays(4)->toDateString(),
        ])->assertSessionHasErrors('loan_date');
    }

    public function test_user_sees_only_own_loans_and_overdue_is_marked_on_index(): void
    {
        $a = $this->makeUser();
        $b = $this->makeUser();
        $loanA = $this->makeLoan($a, $this->makeItem(['name' => 'Barang Milik A']), [
            'due_date' => now()->subDay()->toDateString(),
        ]);
        $this->makeLoan($b, $this->makeItem(['name' => 'Barang Milik B']));

        $this->actingAs($a)->get('/loans')->assertSee('Barang Milik A')->assertDontSee('Barang Milik B');
        $this->assertSame('terlambat', $loanA->fresh()->status);

        $this->actingAs($this->makeUser('admin'))->get('/loans')
            ->assertSee('Barang Milik A')
            ->assertSee('Barang Milik B');
    }

    public function test_loan_list_survives_a_deleted_item(): void
    {
        $item = $this->makeItem(['name' => 'Barang Dihapus']);
        $this->makeLoan($this->makeUser(), $item, ['status' => 'dikembalikan']);
        $item->delete();

        $this->actingAs($this->makeUser('admin'))->get('/loans')->assertOk()->assertSee('Barang Dihapus');
    }

    // ---------- Pengembalian ----------

    public function test_admin_records_a_return_and_stock_comes_back(): void
    {
        $admin = $this->makeUser('admin');
        $item = $this->makeItem(['stock' => 5]);
        $loan = $this->makeLoan($this->makeUser(), $item, ['quantity' => 2]); // stok jadi 3

        $this->actingAs($admin);
        $this->get('/loans/'.$loan->id.'/return')->assertOk();

        $this->post('/loans/'.$loan->id.'/return', [
            'returned_at' => now()->toDateString(),
            'condition_on_return' => 'rusak ringan',
            'notes' => 'Pentil bocor',
        ])->assertRedirect(route('loans.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('return_logs', [
            'loan_id' => $loan->id,
            'condition_on_return' => 'rusak ringan',
            'notes' => 'Pentil bocor',
        ]);
        $this->assertSame('dikembalikan', $loan->fresh()->status);
        $this->assertSame(5, $item->fresh()->stock);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id, 'action' => 'return_loan']);
    }

    public function test_return_rejects_bad_input_and_cannot_be_recorded_twice(): void
    {
        $item = $this->makeItem(['stock' => 5]);
        $loan = $this->makeLoan($this->makeUser(), $item); // stok jadi 4
        $url = '/loans/'.$loan->id.'/return';
        $valid = ['returned_at' => now()->toDateString(), 'condition_on_return' => 'baik'];

        $this->actingAs($this->makeUser('admin'));

        $this->post($url, ['returned_at' => now()->subDays(5)->toDateString()] + $valid)->assertSessionHasErrors('returned_at');
        $this->post($url, ['returned_at' => now()->addDay()->toDateString()] + $valid)->assertSessionHasErrors('returned_at');
        $this->post($url, ['condition_on_return' => 'hancur'] + $valid)->assertSessionHasErrors('condition_on_return');
        $this->assertDatabaseCount('return_logs', 0);

        $this->post($url, $valid)->assertSessionHas('success');
        $this->post($url, $valid)->assertSessionHas('error');

        $this->assertDatabaseCount('return_logs', 1);
        $this->assertSame(5, $item->fresh()->stock);
    }

    public function test_overdue_loan_can_be_returned(): void
    {
        $loan = $this->makeLoan($this->makeUser(), $this->makeItem(), [
            'status' => 'terlambat',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($this->makeUser('admin'))->post('/loans/'.$loan->id.'/return', [
            'returned_at' => now()->toDateString(),
            'condition_on_return' => 'baik',
        ])->assertSessionHas('success');

        $this->assertSame('dikembalikan', $loan->fresh()->status);
    }

    public function test_return_form_for_a_returned_loan_redirects_back(): void
    {
        $loan = $this->makeLoan($this->makeUser(), $this->makeItem(), ['status' => 'dikembalikan']);

        $this->actingAs($this->makeUser('admin'))->get('/loans/'.$loan->id.'/return')
            ->assertRedirect(route('loans.index'))
            ->assertSessionHas('error');
    }
}
