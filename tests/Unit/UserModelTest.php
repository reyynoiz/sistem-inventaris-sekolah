<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    public function test_is_admin_depends_on_role(): void
    {
        $this->assertTrue($this->makeUser('admin')->isAdmin());
        $this->assertFalse($this->makeUser('user')->isAdmin());
    }

    public function test_role_defaults_to_user_when_not_given(): void
    {
        User::create(['name' => 'Tanpa Role', 'email' => 'tanparole@uji.test', 'password' => 'password']);

        $this->assertSame('user', User::where('email', 'tanparole@uji.test')->value('role'));
    }

    public function test_password_is_hashed(): void
    {
        $user = $this->makeUser('user', ['password' => 'rahasia123']);

        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_photo_url_is_built_from_photo_path(): void
    {
        $user = $this->makeUser();

        $this->assertNull($user->photo_url);

        // photo sengaja tidak masuk $fillable, jadi diisi lewat forceFill
        $user->forceFill(['photo' => 'profile-photos/contoh.jpg'])->save();

        $this->assertStringEndsWith('/storage/profile-photos/contoh.jpg', $user->fresh()->photo_url);
    }

    public function test_user_has_loans_and_activity_logs(): void
    {
        $user = $this->makeUser();
        $this->makeLoan($user, $this->makeItem());
        ActivityLog::create(['user_id' => $user->id, 'action' => 'login', 'description' => 'uji']);

        $this->assertCount(1, $user->loans);
        $this->assertCount(1, $user->activityLogs);
    }
}