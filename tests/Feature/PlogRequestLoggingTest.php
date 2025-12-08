<?php

namespace Laikmosh\Plog\Tests\Feature;

use Laikmosh\Plog\Http\Middleware\PlogRequestLoggingMiddleware;
use Laikmosh\Plog\Models\PlogEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Laikmosh\Plog\PlogServiceProvider;
use Orchestra\Testbench\TestCase;

class PlogRequestLoggingTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [PlogServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('plog.database.connection', 'sqlite');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function test_it_logs_error_requests_with_response_time()
    {
        // Route causing 500 error
        Route::get('/error-test', function () {
            return response('Error', 500);
        })->middleware(PlogRequestLoggingMiddleware::class);

        $response = $this->get('/error-test');
        $response->assertStatus(500);

        $this->assertDatabaseHas('plog_entries', [
            'level' => 'Request',
            'message' => 'http://localhost/error-test',
        ]);
        
        $entry = PlogEntry::where('level', 'Request')->first();
        
        $this->assertNotNull($entry->response_time);
        $this->assertGreaterThan(0, $entry->response_time);
        $this->assertTrue(in_array('response_code:500', $entry->tags));
    }
    
    public function test_it_does_not_log_successful_requests()
    {
        // Route causing 200 OK
        Route::get('/success-test', function () {
            return response('OK', 200);
        })->middleware(PlogRequestLoggingMiddleware::class);

        $response = $this->get('/success-test');
        $response->assertStatus(200);

        $this->assertDatabaseMissing('plog_entries', [
            'message' => 'http://localhost/success-test',
        ]);
    }
}
