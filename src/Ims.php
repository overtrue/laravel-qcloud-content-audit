<?php

namespace Overtrue\LaravelQcloudContentAudit;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array check(string $contents)
 * @method static bool validate(string $contents, string $strategy = 'strict')
 * @method static \Overtrue\LaravelQcloudContentAudit\Moderators\Ims setStrategy(string $name, callable $fn)
 */
class Ims extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ims';
    }
}
