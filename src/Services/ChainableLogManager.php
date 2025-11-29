<?php

namespace Laikmosh\Plog\Services;

use Laikmosh\Plog\Support\TaggableLog;

class ChainableLogManager
{
    protected $originalLogger;

    public function __construct($originalLogger)
    {
        $this->originalLogger = $originalLogger;
    }

    // Implement all log level methods directly
    public function emergency($message, array $context = [])
    {
        try {
            $this->originalLogger->emergency($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('emergency', $message, $context);
    }

    public function alert($message, array $context = [])
    {
        try {
            $this->originalLogger->alert($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('alert', $message, $context);
    }

    public function critical($message, array $context = [])
    {
        try {
            $this->originalLogger->critical($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('critical', $message, $context);
    }

    public function error($message, array $context = [])
    {
        try {
            $this->originalLogger->error($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('error', $message, $context);
    }

    public function warning($message, array $context = [])
    {
        try {
            $this->originalLogger->warning($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('warning', $message, $context);
    }

    public function notice($message, array $context = [])
    {
        try {
            $this->originalLogger->notice($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('notice', $message, $context);
    }

    public function info($message, array $context = [])
    {
        try {
            $this->originalLogger->info($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('info', $message, $context);
    }

    public function debug($message, array $context = [])
    {
        try {
            $this->originalLogger->debug($message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog('debug', $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        try {
            $this->originalLogger->log($level, $message, $context);
        } catch (\Exception $e) {
            // Continue even if logging fails
        }
        return new TaggableLog($level, $message, $context);
    }

    // Delegate all other method calls to the original logger
    public function __call($method, $arguments)
    {
        return $this->originalLogger->$method(...$arguments);
    }

    // Handle direct property access and other non-method calls
    public function __get($property)
    {
        return $this->originalLogger->$property;
    }

    public function __set($property, $value)
    {
        $this->originalLogger->$property = $value;
    }

    public function __isset($property)
    {
        return isset($this->originalLogger->$property);
    }

    // Make sure we implement all common LogManager methods explicitly
    public function channel($channel = null)
    {
        return $this->originalLogger->channel($channel);
    }

    public function stack(array $channels, $channel = null)
    {
        return $this->originalLogger->stack($channels, $channel);
    }

    public function build(array $config)
    {
        return $this->originalLogger->build($config);
    }

    public function getDefaultDriver()
    {
        return $this->originalLogger->getDefaultDriver();
    }

    public function setDefaultDriver($name)
    {
        return $this->originalLogger->setDefaultDriver($name);
    }
}