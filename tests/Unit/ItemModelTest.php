<?php

namespace Tests\Unit;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class ItemModelTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    public function test_photo_url_is_null_without_photo(): void
    {
        $this->assertNull($this->makeItem()->photo_url);
    }

    public function test_photo_url_points_to_public_storage(): void
    {
        $item = $this->makeItem(['photo' => 'item-photos/contoh.jpg']);

        $this->assertStringEndsWith('/storage/item-photos/contoh.jpg', $item->photo_url);
    }

    public function test_soft_delete_hides_item_but_keeps_the_row(): void
    {
        $item = $this->makeItem();

        $item->delete();

        $this->assertSoftDeleted($item);
        $this->assertSame(0, Item::count());
        $this->assertSame(1, Item::withTrashed()->count());
    }

    public function test_item_belongs_to_category_and_has_loans(): void
    {
        $category = $this->makeCategory('Alat Lab Uji');
        $item = $this->makeItem(['category_id' => $category->id]);
        $this->makeLoan($this->makeUser(), $item);

        $this->assertSame('Alat Lab Uji', $item->category->name);
        $this->assertCount(1, $item->loans);
        $this->assertCount(1, $category->items);
    }
}