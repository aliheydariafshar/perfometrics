<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        // Ensure sqlite testing database file exists
        $dbPath = __DIR__.'/../database/database.sqlite';
        if (! file_exists($dbPath)) {
            @mkdir(dirname($dbPath), 0777, true);
            touch($dbPath);
        }

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
