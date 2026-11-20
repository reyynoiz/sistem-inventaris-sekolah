<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class DashboardProfileTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    // ---------- Dashboard ----------

    public function test_dashboards_differ_by_role_and_count_correctly(): void
    {
        $admin = $this->makeUser('admin');
        $user = $this->makeUser();
        $other = $this->makeUser();
        $first = $this->makeItem(['stock' => 10]);
        $second = $this->makeItem(['stock' => 6]);

        $this->makeLoan($user, $first);                                                        // aktif, stok 9
        $this->makeLoan($user, $first, ['due_date' => now()->subDay()->toDateString()]);       // jadi terlambat, stok 8
        $this->makeLoan($user, $second, ['status' => 'dikembalikan']);                         // sudah kembali
        $this->makeLoan($other, $second);                                                      // milik orang lain, stok 5

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertViewIs('dashboard.admin')
            ->assertViewHas('totalItems', 2)
            ->assertViewHas('totalStock', 13)
            ->assertViewHas('borrowed', 3)
            ->assertViewHas('overdue', 1);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertViewIs('dashboard.user')
            ->assertViewHas('borrowed', 2)
            ->assertViewHas('overdue', 1)
            ->assertViewHas('total', 3);
    }

    // ---------- Profil ----------

    public function test_profile_photo_upload_replace_and_remove(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();
        $this->actingAs($user);

        $this->post('/profile/photo', ['photo' => UploadedFile::fake()->image('a.png', 300, 300)])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $first = $user->fresh()->photo;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);

        $this->post('/profile/photo', ['photo' => UploadedFile::fake()->image('b.png', 300, 300)]);

        $second = $user->fresh()->photo;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->delete('/profile/photo')->assertRedirect(route('profile.edit'));

        $this->assertNull($user->fresh()->photo);
        Storage::disk('public')->assertMissing($second);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'update_photo']);
    }

    public function test_profile_photo_validation(): void
    {
        Storage::fake('public');
        $this->actingAs($this->makeUser());

        $this->post('/profile/photo', [])->assertSessionHasErrors('photo');
        $this->post('/profile/photo', ['photo' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')])->assertSessionHasErrors('photo');
        $this->post('/profile/photo', ['photo' => UploadedFile::fake()->image('kecil.png', 50, 50)])->assertSessionHasErrors('photo');
        $this->post('/profile/photo', ['photo' => UploadedFile::fake()->image('besar.png', 300, 300)->size(3000)])->assertSessionHasErrors('photo');
    }

    public function test_account_deletion_rules(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrors('password', null, 'userDeletion');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $borrower = $this->makeUser();
        $this->makeLoan($borrower, $this->makeItem());
        $this->actingAs($borrower)->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrors('password', null, 'userDeletion');
        $this->assertDatabaseHas('users', ['id' => $borrower->id]);

        $free = $this->makeUser();
        $this->actingAs($free)->delete('/profile', ['password' => 'password'])->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $free->id]);
    }
}
