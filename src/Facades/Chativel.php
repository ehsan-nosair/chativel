<?php

namespace EhsanNosair\Chativel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \EhsanNosair\Chativel\Chativel
 */
class Chativel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \EhsanNosair\Chativel\Chativel::class;
    }
}
