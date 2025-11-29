<?php

namespace Laikmosh\Plog\Facades;

use Illuminate\Support\Facades\Log as LaravelLog;
use Laikmosh\Plog\Support\TaggableLog;

class Log extends LaravelLog
{
    public static function emergency($message, array $context = [])
    {
        // Call Laravel's logging first, but ignore return value
        try {
            parent::emergency($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        // Always return TaggableLog for chaining
        return new TaggableLog('emergency', $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        try {
            parent::alert($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        return new TaggableLog('alert', $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        try {
            parent::critical($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        return new TaggableLog('critical', $message, $context);
    }

    public static function error($message, array $context = [])
    {
        try {
            parent::error($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        return new TaggableLog('error', $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        try {
            parent::warning($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        return new TaggableLog('warning', $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        try {
            parent::notice($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        return new TaggableLog('notice', $message, $context);
    }

    public static function info($message, array $context = [])
    {
        try {
            parent::info($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        return new TaggableLog('info', $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        try {
            parent::debug($message, $context);
        } catch (\Exception $e) {
            // If logging fails, still continue
        }

        return new TaggableLog('debug', $message, $context);
    }

    public static function tags(array $tags)
    {
        // Do nothing for now - just prove this method can be called
        return null;
    }
}