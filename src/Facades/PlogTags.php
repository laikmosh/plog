<?php

namespace Laikmosh\Plog\Facades;

use Illuminate\Support\Facades\Log;
use Laikmosh\Plog\Loggers\PlogHandler;

class PlogTags
{
    /**
     * Set tags for the next log entry using Laravel's withContext
     *
     * @param array $tags
     * @return self
     */
    public static function tags(array $tags)
    {
        // Store tags for the next log entry
        PlogHandler::setNextLogTags($tags);

        // Also add to Laravel's context for compatibility
        Log::withContext(['_plog_tags' => $tags]);

        return new static();
    }

    // Proxy all log methods to Laravel's Log facade
    public function emergency($message, array $context = [])
    {
        Log::emergency($message, $context);
        return $this;
    }

    public function alert($message, array $context = [])
    {
        Log::alert($message, $context);
        return $this;
    }

    public function critical($message, array $context = [])
    {
        Log::critical($message, $context);
        return $this;
    }

    public function error($message, array $context = [])
    {
        Log::error($message, $context);
        return $this;
    }

    public function warning($message, array $context = [])
    {
        Log::warning($message, $context);
        return $this;
    }

    public function notice($message, array $context = [])
    {
        Log::notice($message, $context);
        return $this;
    }

    public function info($message, array $context = [])
    {
        Log::info($message, $context);
        return $this;
    }

    public function debug($message, array $context = [])
    {
        Log::debug($message, $context);
        return $this;
    }

    public function log($level, $message, array $context = [])
    {
        Log::log($level, $message, $context);
        return $this;
    }
}