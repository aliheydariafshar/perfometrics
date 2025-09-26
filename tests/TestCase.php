<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $dbPath = base_path('database/database.sqlite');
        if (! file_exists($dbPath)) {
            @mkdir(dirname($dbPath), 0777, true);
            touch($dbPath);
        }

        // Force tests to use sqlite file connection consistently
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => $dbPath,
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        try {
            \DB::connection('sqlite')->getPdo();
        } catch (\Throwable $e) {
            // If sqlite driver is missing, let the test surface the extension error
            return;
        }

        // Ensure schema exists
        try {
            \Schema::hasTable('migrations');
        } catch (\Throwable $e) {
            Artisan::call('migrate', ['--force' => true]);
        }
    }
}
