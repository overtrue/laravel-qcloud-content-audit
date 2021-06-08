<?php

namespace Overtrue\LaravelQcs;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array check(string $contents)
 * @method static bool validate(string $contents, string $strategy = 'strict')
 * @method static string mask(string $contents, string $strategy = 'strict', string $char = '*')
 * @method static \Overtrue\LaravelQcs\Moderators\Tms setStrategy(string $name, callable $fn)
 */
class Tms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tms';
    }
}
