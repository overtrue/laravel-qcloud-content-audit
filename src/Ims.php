<?php

namespace Overtrue\LaravelQcs;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array check(string $contents)
 * @method static bool validate(string $contents, string $strategy = 'strict')
 * @method static \Overtrue\LaravelQcs\Moderators\Ims setStrategy(string $name, callable $fn)
 */
class Ims extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ims';
    }
}
