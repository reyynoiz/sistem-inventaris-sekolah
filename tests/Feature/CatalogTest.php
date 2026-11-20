<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    private function payload(int $categoryId, array $overrides = []): array
    {
        return array_merge([
            'category_id' => $categoryId,
            'name' => 'Proyektor Uji',
            'code' => 'PRJ-100',
            'stock' => 3,
            'condition' => 'baik',
        ], $overrides);
    }

    // ---------- Kategori ----------

    public function test_admin_creates_category_and_it_is_logged(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/categories', ['name' => 'Alat Musik'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Alat Musik']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id, 'action' => 'create_category']);
    }

    public function test_category_name_is_required_and_unique(): void
    {
        $this->makeCategory('Elektronik');
        $this->actingAs($this->makeUser('admin'));

        $this->post('/categories', ['name' => ''])->assertSessionHasErrors('name');
        $this->post('/categories', ['name' => 'Elektronik'])->assertSessionHasErrors('name');
    }

    public function test_admin_updates_category_and_may_keep_its_own_name(): void
    {
        $category = $this->makeCategory('Lama');
        $this->actingAs($this->makeUser('admin'));

        $this->put('/categories/'.$category->id, ['name' => 'Baru'])->assertSessionHasNoErrors();
        $this->assertSame('Baru', $category->fresh()->name);

        $this->put('/categories/'.$category->id, ['name' => 'Baru'])->assertSessionHasNoErrors();
    }

    public function test_category_in_use_cannot_be_deleted_but_empty_one_can(): void
    {
        $used = $this->makeCategory('Terpakai');
        $item = $this->makeItem(['category_id' => $used->id]);
        $empty = $this->makeCategory('Kosong');
        $this->actingAs($this->makeUser('admin'));

        $this->delete('/categories/'.$used->id)->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $used->id]);

        $item->delete(); // barang yang di-soft-delete tetap menahan penghapusan kategori
        $this->delete('/categories/'.$used->id)->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $used->id]);

        $this->delete('/categories/'.$empty->id)->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $empty->id]);
    }

    public function test_category_search_filters_the_list(): void
    {
        $this->makeCategory('Alat Lab Uji');
        $this->makeCategory('Furnitur Uji');

        $this->actingAs($this->makeUser('admin'))->get('/categories?q=Lab')
            ->assertSee('Alat Lab Uji')
            ->assertDontSee('Furnitur Uji');
    }

    // ---------- Barang ----------

    public function test_admin_creates_item_without_photo(): void
    {
        $admin = $this->makeUser('admin');
        $category = $this->makeCategory();

        $this->actingAs($admin)->post('/items', $this->payload($category->id))
            ->assertRedirect(route('items.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('items', ['code' => 'PRJ-100', 'stock' => 3, 'photo' => null]);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id, 'action' => 'create_item']);
    }

    public function test_item_validation_rejects_bad_input(): void
    {
        $category = $this->makeCategory();
        $this->makeItem(['code' => 'DUP-1', 'category_id' => $category->id]);
        $this->actingAs($this->makeUser('admin'));

        $this->post('/items', [])->assertSessionHasErrors(['category_id', 'name', 'code', 'stock', 'condition']);
        $this->post('/items', $this->payload($category->id, ['code' => 'DUP-1']))->assertSessionHasErrors('code');
        $this->post('/items', $this->payload($category->id, ['stock' => -1]))->assertSessionHasErrors('stock');
        $this->post('/items', $this->payload($category->id, ['condition' => 'hancur']))->assertSessionHasErrors('condition');
    }

    public function test_item_photo_is_stored_replaced_and_removed(): void
    {
        Storage::fake('public');
        $category = $this->makeCategory();
        $this->actingAs($this->makeUser('admin'));

        $this->post('/items', $this->payload($category->id) + [
            'photo' => UploadedFile::fake()->image('a.jpg', 300, 300),
        ])->assertSessionHasNoErrors();

        $item = Item::where('code', 'PRJ-100')->firstOrFail();
        $first = $item->photo;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);

        $update = [
            'category_id' => $category->id,
            'name' => $item->name,
            'code' => $item->code,
            'stock' => $item->stock,
            'condition' => $item->condition,
        ];

        $this->put('/items/'.$item->id, $update + [
            'photo' => UploadedFile::fake()->image('b.jpg', 300, 300),
        ])->assertSessionHasNoErrors();

        $second = $item->fresh()->photo;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->put('/items/'.$item->id, $update + ['remove_photo' => '1'])->assertSessionHasNoErrors();

        $this->assertNull($item->fresh()->photo);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_item_photo_must_be_a_valid_image(): void
    {
        Storage::fake('public');
        $category = $this->makeCategory();
        $this->actingAs($this->makeUser('admin'));

        $files = [
            UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('kecil.jpg', 50, 50),
            UploadedFile::fake()->image('besar.jpg', 300, 300)->size(3000),
        ];

        foreach ($files as $index => $file) {
            $this->post('/items', $this->payload($category->id, ['code' => 'FOTO-'.$index]) + ['photo' => $file])
                ->assertSessionHasErrors('photo');
        }

        $this->assertDatabaseCount('items', 0);
    }

    public function test_item_with_active_loan_cannot_be_deleted_but_free_item_can(): void
    {
        $busy = $this->makeItem();
        $this->makeLoan($this->makeUser(), $busy);
        $free = $this->makeItem();
        $this->actingAs($this->makeUser('admin'));

        $this->delete('/items/'.$busy->id)->assertSessionHas('error');
        $this->assertNotSoftDeleted($busy);

        $this->delete('/items/'.$free->id)->assertSessionHas('success');
        $this->assertSoftDeleted($free);
        $this->assertDatabaseHas('activity_logs', ['action' => 'delete_item']);
    }

    public function test_user_sees_items_without_admin_buttons_and_can_search(): void
    {
        $this->makeItem(['name' => 'Laptop Uji']);
        $this->makeItem(['name' => 'Proyektor Uji']);

        $this->actingAs($this->makeUser());
        $this->get('/items')->assertOk()->assertDontSee('Tambah barang');
        $this->get('/items?q=Laptop')->assertSee('Laptop Uji')->assertDontSee('Proyektor Uji');

        $this->actingAs($this->makeUser('admin'))->get('/items')->assertSee('Tambah barang');
    }
}
