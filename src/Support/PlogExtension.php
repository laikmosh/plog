<?php

namespace Laikmosh\Plog\Support;

use Illuminate\Support\Facades\Log;
use Laikmosh\Plog\Loggers\PlogHandler;

class PlogExtension
{
    public static function registerExtensions()
    {
        // Override the default log methods to return a TaggableLog instance
        $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

        foreach ($levels as $level) {
            Log::macro($level, function ($message, array $context = []) use ($level) {
                // Call the original log method
                Log::log($level, $message, $context);

                // Return a TaggableLog instance for potential chaining
                return new class($level, $message, $context) {
                    protected $level;
                    protected $message;
                    protected $context;

                    public function __construct($level, $message, $context)
                    {
                        $this->level = $level;
                        $this->message = $message;
                        $this->context = $context;
                    }

                    public function tags(array $tags)
                    {
                        // Set tags for the last log entry
                        PlogHandler::setNextLogTags($tags);

                        // Log again with the tags
                        Log::log($this->level, $this->message, $this->context);

                        return $this;
                    }
                };
            });
        }
    }
}