<?php

namespace Laikmosh\Plog\Loggers;

use Monolog\Logger;
use Laikmosh\Plog\Support\TaggedLogger;

class PlogMonologLogger extends Logger
{
    /**
     * Add tags to the next log entry
     *
     * @param array $tags
     * @return TaggedLogger
     */
    public function tags(array $tags)
    {
        return new TaggedLogger($tags);
    }
}