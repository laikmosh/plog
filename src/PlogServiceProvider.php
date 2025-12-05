<?php

namespace Laikmosh\Plog;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\Events\JobProcessing;
use Laikmosh\Plog\Http\Middleware\RequestIdMiddleware;
use Laikmosh\Plog\Loggers\PlogHandler;
use Laikmosh\Plog\Services\RequestIdService;
use Monolog\Logger;
use Livewire\Livewire;
use Laikmosh\Plog\Macros\LogMacros;

class PlogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/plog.php', 'plog');

        $this->app->singleton(RequestIdService::class, function ($app) {
            return new RequestIdService();
        });

        $this->app->singleton('plog.handler', function ($app) {
            return new PlogHandler($app);
        });

        $this->app->singleton('plog.logger', function ($app) {
            return new \Laikmosh\Plog\Services\PlogLogger();
        });

        // Override the default logging configuration to use our custom logger
        $this->overrideLoggingConfig();
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'plog');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/plog.php' => config_path('plog.php'),
            ], 'plog-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'plog-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/plog'),
            ], 'plog-views');

            $this->publishes([
                __DIR__.'/../resources/assets/css' => public_path('vendor/plog/css'),
            ], 'plog-assets');

            $this->commands([
                \Laikmosh\Plog\Console\BackfillMetadataCommand::class,
            ]);
        }

        $this->configureDatabase();
        $this->configurePlogHandler();
        $this->configureMiddleware();
        $this->configureAuthorization();
        $this->configureQueueListeners();
        $this->configureLivewireComponents();
    }

    protected function configureDatabase()
    {
        $connections = config('database.connections');
        $dbPath = database_path('/sqlite');
        $dbFile = database_path('/sqlite/plogs.sqlite');
        if (!isset($connections['plog'])) {
            config([
                'database.connections.plog' => [
                    'driver' => 'sqlite',
                    'database' => $dbFile,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]
            ]);

            if (!file_exists($dbPath)) {
                mkdir($dbPath, 0755, true);
            }

            if (!file_exists($dbFile)) {
                touch($dbFile);
            }
            // if ($this->confirm("Create SQLite database?", true)) {
            // }
        }
    }

    protected function configurePlogHandler()
    {
        if (!config('plog.enabled', true)) {
            return;
        }

        // Intercept all log events
        Log::listen(function ($event) {
            // Handle MessageLogged event
            if (is_object($event)) {
                $level = $event->level ?? null;
                $message = $event->message ?? null;
                $context = $event->context ?? [];
            } else {
                // Fallback for different event structures
                return;
            }

            if ($level && $message !== null) {
                app('plog.handler')->handle($level, $message, $context);
            }
        });
    }

    protected function configureMiddleware()
    {
        $this->app['router']->aliasMiddleware('plog.request-id', RequestIdMiddleware::class);

        if (config('plog.auto_add_middleware', true)) {
            $this->app['router']->pushMiddlewareToGroup('web', RequestIdMiddleware::class);
            $this->app['router']->pushMiddlewareToGroup('api', RequestIdMiddleware::class);
        }
    }

    protected function configureAuthorization()
    {
        Gate::define('viewPlog', function ($user = null) {
            $authorizedEmails = config('plog.authorized_emails', []);

            if (empty($authorizedEmails)) {
                return true;
            }

            // Support wildcard '*' to allow all authenticated users
            if (in_array('*', $authorizedEmails)) {
                return true;
            }

            return $user && in_array($user->email, $authorizedEmails);
        });
    }

    protected function configureQueueListeners()
    {
        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            $payload = $event->job->payload();

            if (isset($payload['plog_request_id'])) {
                app(RequestIdService::class)->setRequestId($payload['plog_request_id']);
            }
        });

        Event::listen('queue.before', function ($event) {
            $requestId = app(RequestIdService::class)->getRequestId();
            if ($requestId && isset($event['job'])) {
                $event['job']->plog_request_id = $requestId;
            }
        });
    }

    protected function configureLivewireComponents()
    {
        Livewire::component('plog-viewer', \Laikmosh\Plog\Http\Livewire\PlogViewer::class);
        Livewire::component('plog-smart-dropdown', \Laikmosh\Plog\Http\Livewire\SmartDropdown::class);
    }

    protected function overrideLoggingConfig()
    {
        // Override the default 'single' and 'daily' channels to use our custom logger
        config([
            'logging.channels.single.driver' => 'custom',
            'logging.channels.single.via' => \Laikmosh\Plog\Loggers\PlogCustomLogger::class,

            'logging.channels.daily.driver' => 'custom',
            'logging.channels.daily.via' => \Laikmosh\Plog\Loggers\PlogCustomLogger::class,
        ]);

        // If the default channel is 'stack', override its channels
        if (config('logging.default') === 'stack') {
            $channels = config('logging.channels.stack.channels', ['single']);
            foreach ($channels as $channel) {
                config([
                    "logging.channels.{$channel}.driver" => 'custom',
                    "logging.channels.{$channel}.via" => \Laikmosh\Plog\Loggers\PlogCustomLogger::class,
                ]);
            }
        }
    }
}