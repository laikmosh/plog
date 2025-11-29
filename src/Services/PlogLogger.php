<?php

namespace Laikmosh\Plog\Services;

use Laikmosh\Plog\Support\TaggableLog;

class PlogLogger
{
    public function tags(array $tags)
    {
        return new TaggableLog(null, null, [], $tags);
    }

    public function emergency($message, array $context = [])
    {
        return (new TaggableLog())->emergency($message, $context);
    }

    public function alert($message, array $context = [])
    {
        return (new TaggableLog())->alert($message, $context);
    }

    public function critical($message, array $context = [])
    {
        return (new TaggableLog())->critical($message, $context);
    }

    public function error($message, array $context = [])
    {
        return (new TaggableLog())->error($message, $context);
    }

    public function warning($message, array $context = [])
    {
        return (new TaggableLog())->warning($message, $context);
    }

    public function notice($message, array $context = [])
    {
        return (new TaggableLog())->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        return (new TaggableLog())->info($message, $context);
    }

    public function debug($message, array $context = [])
    {
        return (new TaggableLog())->debug($message, $context);
    }
}