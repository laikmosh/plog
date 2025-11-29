<?php

namespace Laikmosh\Plog\Macros;

use Laikmosh\Plog\Support\TaggableLog;
use Illuminate\Support\Facades\Log;

class LogMacros
{
    public static function register()
    {
        // Add the 'tags' method to the Log facade
        Log::macro('tags', function (array $tags) {
            return new TaggableLog(null, null, [], $tags);
        });
    }
}