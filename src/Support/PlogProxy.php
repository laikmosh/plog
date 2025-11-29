<?php

namespace Laikmosh\Plog\Support;

use Illuminate\Support\Facades\Log;
use Laikmosh\Plog\Loggers\PlogHandler;

class PlogProxy
{
    protected $tags = [];

    public static function __callStatic($method, $args)
    {
        $instance = new static;
        return $instance->$method(...$args);
    }

    public function emergency($message, array $context = [])
    {
        return $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = [])
    {
        return $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = [])
    {
        return $this->log('critical', $message, $context);
    }

    public function error($message, array $context = [])
    {
        return $this->log('error', $message, $context);
    }

    public function warning($message, array $context = [])
    {
        return $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = [])
    {
        return $this->log('notice', $message, $context);
    }

    public function info($message, array $context = [])
    {
        return $this->log('info', $message, $context);
    }

    public function debug($message, array $context = [])
    {
        return $this->log('debug', $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        if (!empty($this->tags)) {
            PlogHandler::setNextLogTags($this->tags);
        }

        Log::log($level, $message, $context);

        return $this;
    }

    public function tags(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);
        return $this;
    }
}