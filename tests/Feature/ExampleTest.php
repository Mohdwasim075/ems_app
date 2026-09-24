<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
     public function test_testing_environment(): void
{
    dump([
        'environment' => app()->environment(),
        'connection' => config('database.default'),
        'database' => config('database.connections.mysql.database'),
    ]);

    $this->assertEquals('testing', app()->environment());
    $this->assertEquals('mysql', config('database.default'));
    $this->assertEquals(
        'ems_app_testing',
        config('database.connections.mysql.database')
    );
}
}
