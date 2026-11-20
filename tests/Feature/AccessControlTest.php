<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        foreach (['/dashboard', '/items', '/loans', '/loans/create', '/profile', '/categories', '/users', '/activity-logs'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_user_cannot_open_admin_pages(): void
    {
        $user = $this->makeUser();
        $category = $this->makeCategory();
        $item = $this->makeItem(['category_id' => $category->id]);
        $loan = $this->makeLoan($user, $item);

        $this->actingAs($user);

        foreach ([
            '/categories',
            '/categories/create',
            '/categories/'.$category->id.'/edit',
            '/items/create',
            '/items/'.$item->id.'/edit',
            '/users',
            '/users/create',
            '/users/'.$user->id.'/edit',
            '/activity-logs',
            '/loans/'.$loan->id.'/return',
        ] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_user_cannot_change_data_through_admin_routes(): void
    {
        $user = $this->makeUser();
        $category = $this->makeCategory();
        $item = $this->makeItem(['category_id' => $category->id]);
        $loan = $this->makeLoan($user, $item);

        $this->actingAs($user);

        $this->post('/categories', ['name' => 'Tidak Boleh'])->assertForbidden();
        $this->put('/categories/'.$category->id, ['name' => 'Diubah'])->assertForbidden();
        $this->delete('/categories/'.$category->id)->assertForbidden();
        $this->post('/items', [])->assertForbidden();
        $this->delete('/items/'.$item->id)->assertForbidden();
        $this->post('/users', [])->assertForbidden();
        $this->delete('/users/'.$user->id)->assertForbidden();
        $this->post('/loans/'.$loan->id.'/return', [])->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $category->name]);
        $this->assertDatabaseHas('items', ['id' => $item->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseHas('loans', ['id' => $loan->id, 'status' => 'dipinjam']);
        $this->assertDatabaseMissing('categories', ['name' => 'Tidak Boleh']);
    }

    public function test_admin_can_open_every_page(): void
    {
        $admin = $this->makeUser('admin');
        $borrower = $this->makeUser();
        $category = $this->makeCategory();
        $item = $this->makeItem(['category_id' => $category->id]);
        $loan = $this->makeLoan($borrower, $item);

        $this->actingAs($admin);

        foreach ([
            '/dashboard',
            '/profile',
            '/items',
            '/items/create',
            '/items/'.$item->id.'/edit',
            '/categories',
            '/categories/create',
            '/categories/'.$category->id.'/edit',
            '/users',
            '/users/create',
            '/users/'.$borrower->id.'/edit',
            '/loans',
            '/loans/create',
            '/loans/'.$loan->id.'/return',
            '/activity-logs',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_user_can_open_user_pages(): void
    {
        $user = $this->makeUser();
        $this->makeItem();

        $this->actingAs($user);

        foreach (['/dashboard', '/profile', '/items', '/loans', '/loans/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
