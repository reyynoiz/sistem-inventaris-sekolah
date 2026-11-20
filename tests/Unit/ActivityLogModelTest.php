<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InventoryFactory;
use Tests\TestCase;

class ActivityLogModelTest extends TestCase
{
    use InventoryFactory, RefreshDatabase;

    public function test_record_uses_the_logged_in_user(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);

        $log = ActivityLog::record('create_item', 'Menambah barang uji');

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('create_item', $log->action);
        $this->assertSame('Menambah barang uji', $log->description);
    }

    public function test_record_accepts_an_explicit_user_id(): void
    {
        $user = $this->makeUser();

        $log = ActivityLog::record('login', 'Masuk ke aplikasi', $user->id);

        $this->assertSame($user->id, $log->user_id);
    }

    public function test_record_without_any_user_leaves_user_empty(): void
    {
        $log = ActivityLog::record('system', 'Tanpa pengguna');

        $this->assertNull($log->user_id);
    }

    public function test_user_relation(): void
    {
        $user = $this->makeUser();
        $log = ActivityLog::record('login', 'Masuk', $user->id);

        $this->assertTrue($log->user->is($user));
    }
}