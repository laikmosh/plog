<?php

namespace Laikmosh\Plog\Facades;

use Illuminate\Support\Facades\Facade;

class Plog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'plog.logger';
    }
}