<?php

namespace Laikmosh\Plog\Services;

use Illuminate\Log\LogManager;
use Laikmosh\Plog\Support\TaggedLogger;

class PlogLogManager extends LogManager
{
    public function tags(array $tags)
    {
        return new TaggedLogger($tags);
    }

    // Keep all original LogManager functionality by extending it
    // The tags() method is the only addition
}