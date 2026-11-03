<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        // Aktivitas otentikasi dicatat ke activity_logs
        Event::listen(Login::class, function (Login $event) {
            ActivityLog::record('login', "{$event->user->name} masuk ke aplikasi", $event->user->id);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLog::record('logout', "{$event->user->name} keluar dari aplikasi", $event->user->id);
            }
        });

        Event::listen(Registered::class, function (Registered $event) {
            ActivityLog::record('register', "{$event->user->name} mendaftar akun baru", $event->user->id);
        });
    }
}