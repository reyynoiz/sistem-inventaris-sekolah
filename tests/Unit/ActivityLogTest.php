<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function log_dapat_dicatat_dengan_memberikan_user_id_secara_eksplisit()
    {
        $user = User::factory()->create();
        
        // Memanggil tanpa ada user yang login, tapi ID dikirim manual
        $log = ActivityLog::record('create_item', 'Menambah barang baru', $user->id);

        $this->assertEquals('create_item', $log->action);
        $this->assertEquals('Menambah barang baru', $log->description);
        $this->assertEquals($user->id, $log->user_id);
    }

    /** @test */
    public function log_otomatis_mengambil_id_user_yang_sedang_login()
    {
        // Simulasi user login
        $user = User::factory()->create();
        $this->actingAs($user); 

        // Parameter $userId tidak diisi
        $log = ActivityLog::record('update_profile', 'User mengubah profilnya');

        // Pastikan logika `auth()->id()` di model kamu berjalan
        $this->assertEquals($user->id, $log->user_id);
    }
}