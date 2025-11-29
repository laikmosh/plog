<?php

namespace Laikmosh\Plog\Loggers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class PlogCustomLogger
{
    /**
     * Create a custom Monolog instance with our extended logger
     */
    public function __invoke(array $config): Logger
    {
        // Create our custom logger that extends Monolog\Logger
        $logger = new PlogMonologLogger($config['name'] ?? 'laravel');

        // Add the default handler (or use config to determine handler)
        if (isset($config['path'])) {
            $logger->pushHandler(new StreamHandler($config['path'], $config['level'] ?? 'debug'));
        }

        // Return our custom logger that has the tags() method
        return $logger;
    }
}