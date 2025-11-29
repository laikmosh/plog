<?php

namespace Laikmosh\Plog\Services;

use Laikmosh\Plog\Support\TaggableLog;

class LoggerWrapper
{
    protected $originalLogger;

    public function __construct($originalLogger)
    {
        $this->originalLogger = $originalLogger;
    }

    public function emergency($message, array $context = [])
    {
        $this->originalLogger->emergency($message, $context);
        return new TaggableLog('emergency', $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->originalLogger->alert($message, $context);
        return new TaggableLog('alert', $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->originalLogger->critical($message, $context);
        return new TaggableLog('critical', $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->originalLogger->error($message, $context);
        return new TaggableLog('error', $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->originalLogger->warning($message, $context);
        return new TaggableLog('warning', $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->originalLogger->notice($message, $context);
        return new TaggableLog('notice', $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->originalLogger->info($message, $context);
        return new TaggableLog('info', $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->originalLogger->debug($message, $context);
        return new TaggableLog('debug', $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $this->originalLogger->log($level, $message, $context);
        return new TaggableLog($level, $message, $context);
    }

    // Delegate all other method calls to the original logger
    public function __call($method, $arguments)
    {
        return $this->originalLogger->$method(...$arguments);
    }
}