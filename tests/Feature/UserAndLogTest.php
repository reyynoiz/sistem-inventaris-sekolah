<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class UserAndLogTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    // ---------- Manajemen user ----------

    public function test_admin_creates_user_and_password_is_hashed(): void
    {
        $this->actingAs($this->makeUser('admin'))->post('/users', [
            'name' => 'Dewi',
            'email' => 'dewi@uji.test',
            'role' => 'user',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('users.index'))->assertSessionHas('success');

        $dewi = User::where('email', 'dewi@uji.test')->firstOrFail();

        $this->assertSame('user', $dewi->role);
        $this->assertTrue(Hash::check('rahasia123', $dewi->password));
        $this->assertDatabaseHas('activity_logs', ['action' => 'create_user']);
    }

    public function test_user_validation_rejects_bad_input(): void
    {
        $taken = $this->makeUser();
        $this->actingAs($this->makeUser('admin'));

        $this->post('/users', [])->assertSessionHasErrors(['name', 'email', 'role', 'password']);

        $valid = ['name' => 'Baru', 'email' => 'baru@uji.test', 'role' => 'user', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123'];

        $this->post('/users', ['email' => $taken->email] + $valid)->assertSessionHasErrors('email');
        $this->post('/users', ['role' => 'superadmin'] + $valid)->assertSessionHasErrors('role');
        $this->post('/users', ['password_confirmation' => 'beda12345'] + $valid)->assertSessionHasErrors('password');
        $this->post('/users', ['password' => 'pendek', 'password_confirmation' => 'pendek'] + $valid)->assertSessionHasErrors('password');
    }

    public function test_update_without_password_keeps_old_password_and_can_change_role(): void
    {
        $target = $this->makeUser('user', ['password' => 'lama12345']);

        $this->actingAs($this->makeUser('admin'))->put('/users/'.$target->id, [
            'name' => 'Nama Baru',
            'email' => $target->email,
            'role' => 'admin',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasNoErrors();

        $target->refresh();

        $this->assertSame('Nama Baru', $target->name);
        $this->assertSame('admin', $target->role);
        $this->assertTrue(Hash::check('lama12345', $target->password));
    }

    public function test_user_deletion_and_role_safeguards(): void
    {
        $admin = $this->makeUser('admin');
        $withLoan = $this->makeUser();
        $this->makeLoan($withLoan, $this->makeItem());
        $free = $this->makeUser();

        $this->actingAs($admin);

        $this->delete('/users/'.$admin->id)->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->delete('/users/'.$withLoan->id)->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $withLoan->id]);

        $this->put('/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'user'])
            ->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);

        $this->delete('/users/'.$free->id)->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $free->id]);
    }

    // ---------- Activity log ----------

    public function test_activity_log_page_filters_and_is_admin_only(): void
    {
        $admin = $this->makeUser('admin');
        ActivityLog::record('create_item', 'Menambah barang Laptop Uji', $admin->id);
        ActivityLog::record('delete_category', 'Menghapus kategori Furnitur Uji', $admin->id);

        $this->actingAs($admin);

        $this->get('/activity-logs?action=create_item')->assertOk()
            ->assertSee('Menambah barang Laptop Uji')
            ->assertDontSee('Menghapus kategori Furnitur Uji');

        $this->get('/activity-logs?q=Furnitur')
            ->assertSee('Menghapus kategori Furnitur Uji')
            ->assertDontSee('Laptop Uji');

        $this->actingAs($this->makeUser())->get('/activity-logs')->assertForbidden();
    }

    public function test_login_logout_and_registration_are_logged(): void
    {
        $user = $this->makeUser();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'login']);

        $this->post('/logout');
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'action' => 'logout']);

        // login gagal tidak dicatat
        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertSame(1, ActivityLog::where('action', 'login')->count());

        $this->post('/register', [
            'name' => 'Pendaftar',
            'email' => 'pendaftar@uji.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', // harus diabaikan
        ]);

        $baru = User::where('email', 'pendaftar@uji.test')->firstOrFail();

        $this->assertSame('user', $baru->role);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $baru->id, 'action' => 'register']);
    }
}
