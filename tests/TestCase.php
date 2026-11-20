<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function refreshApplication()
    {
        parent::refreshApplication();

        // Pengaman: tes yang memakai RefreshDatabase menghapus seluruh tabel.
        // Tes dihentikan sebelum itu terjadi bila database pengujian bukan SQLite di memori.
        $connection = $this->app['config']->get('database.default');
        $database = $this->app['config']->get('database.connections.sqlite.database');

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                'Tes dihentikan agar database pengembangan tidak terhapus. '
                .'Buka komentar DB_CONNECTION dan DB_DATABASE di phpunit.xml '
                .'(sqlite dan :memory:), lalu jalankan ulang.'
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Tampilan memakai @vite; tes tidak perlu aset hasil build
        $this->withoutVite();
    }
}