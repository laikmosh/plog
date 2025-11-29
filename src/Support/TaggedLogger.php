<?php

namespace Laikmosh\Plog\Support;

use Illuminate\Support\Facades\Log;
use Laikmosh\Plog\Loggers\PlogHandler;

class TaggedLogger
{
    protected $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function emergency($message, array $context = [])
    {
        $this->setTags();
        Log::emergency($message, $context);
        return $this;
    }

    public function alert($message, array $context = [])
    {
        $this->setTags();
        Log::alert($message, $context);
        return $this;
    }

    public function critical($message, array $context = [])
    {
        $this->setTags();
        Log::critical($message, $context);
        return $this;
    }

    public function error($message, array $context = [])
    {
        $this->setTags();
        Log::error($message, $context);
        return $this;
    }

    public function warning($message, array $context = [])
    {
        $this->setTags();
        Log::warning($message, $context);
        return $this;
    }

    public function notice($message, array $context = [])
    {
        $this->setTags();
        Log::notice($message, $context);
        return $this;
    }

    public function info($message, array $context = [])
    {
        $this->setTags();
        Log::info($message, $context);
        return $this;
    }

    public function debug($message, array $context = [])
    {
        $this->setTags();
        Log::debug($message, $context);
        return $this;
    }

    public function log($level, $message, array $context = [])
    {
        $this->setTags();
        Log::log($level, $message, $context);
        return $this;
    }

    protected function setTags()
    {
        if (!empty($this->tags)) {
            PlogHandler::setNextLogTags($this->tags);
        }
    }
}