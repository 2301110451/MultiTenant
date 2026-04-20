<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $connection = (string) (env('DB_CONNECTION') ?: 'sqlite');
        $hasSqlitePdo = in_array('sqlite', \PDO::getAvailableDrivers(), true);

        if ($connection === 'sqlite' && ! $hasSqlitePdo) {
            $this->markTestSkipped('pdo_sqlite is not installed in this PHP environment. Install it or switch phpunit DB settings.');
        }

        parent::setUp();
    }
}
