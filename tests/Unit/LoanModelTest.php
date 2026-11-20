<?php

namespace Tests\Unit;

use App\Models\Loan;
use App\Models\ReturnLog;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class LoanModelTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    public function test_mark_overdue_only_changes_late_active_loans(): void
    {
        $user = $this->makeUser();
        $item = $this->makeItem(['stock' => 20]);

        $late = $this->makeLoan($user, $item, ['due_date' => now()->subDays(2)->toDateString()]);
        $notYet = $this->makeLoan($user, $item, ['due_date' => now()->addDays(2)->toDateString()]);
        $dueToday = $this->makeLoan($user, $item, ['due_date' => now()->toDateString()]);
        $returned = $this->makeLoan($user, $item, [
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'dikembalikan',
        ]);

        Loan::markOverdue();

        $this->assertSame('terlambat', $late->fresh()->status);
        $this->assertSame('dipinjam', $notYet->fresh()->status);
        $this->assertSame('dipinjam', $dueToday->fresh()->status); // jatuh tempo hari ini belum terlambat
        $this->assertSame('dikembalikan', $returned->fresh()->status);
    }

    public function test_active_scope_returns_borrowed_and_overdue_only(): void
    {
        $user = $this->makeUser();
        $item = $this->makeItem(['stock' => 20]);

        $this->makeLoan($user, $item, ['status' => 'dipinjam']);
        $this->makeLoan($user, $item, ['status' => 'terlambat']);
        $this->makeLoan($user, $item, ['status' => 'dikembalikan']);

        $this->assertSame(2, Loan::active()->count());
    }

    public function test_item_relation_still_loads_a_soft_deleted_item(): void
    {
        $loan = $this->makeLoan($this->makeUser(), $this->makeItem());
        $itemId = $loan->item_id;

        $loan->item->delete();

        $this->assertNotNull($loan->fresh()->item);
        $this->assertSame($itemId, $loan->fresh()->item->id);
    }

    public function test_return_log_relation(): void
    {
        $loan = $this->makeLoan($this->makeUser(), $this->makeItem());

        $this->assertNull($loan->returnLog);

        ReturnLog::create([
            'loan_id' => $loan->id,
            'returned_at' => now()->toDateString(),
            'condition_on_return' => 'baik',
        ]);

        $returnLog = $loan->fresh()->returnLog;

        $this->assertNotNull($returnLog);
        $this->assertInstanceOf(CarbonInterface::class, $returnLog->returned_at);
    }

    public function test_dates_are_cast_to_carbon(): void
    {
        $loan = $this->makeLoan($this->makeUser(), $this->makeItem());

        $this->assertInstanceOf(CarbonInterface::class, $loan->loan_date);
        $this->assertInstanceOf(CarbonInterface::class, $loan->due_date);
    }
}