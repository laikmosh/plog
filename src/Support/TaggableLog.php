<?php

namespace Laikmosh\Plog\Support;

use Laikmosh\Plog\Loggers\PlogHandler;

class TaggableLog
{
    protected $level;
    protected $message;
    protected $context;
    protected $tags = [];

    public function __construct($level, $message, $context = [])
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }

    public function tags(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);

        // Set tags for the next log entry that matches this one
        if (!empty($this->tags)) {
            PlogHandler::setNextLogTags($this->tags);
        }

        return $this;
    }
}