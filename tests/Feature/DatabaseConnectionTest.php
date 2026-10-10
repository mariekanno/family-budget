<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_uses_testing_database(): void
    {
        $this->assertEquals('testing', config('app.env'));

        $this->assertEquals('mysql', config('database.default'));

        $this->assertEquals(
            'testing',
            DB::connection()->getDatabaseName()
        );
    }
}
