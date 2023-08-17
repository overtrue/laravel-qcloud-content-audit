<?php

namespace Overtrue\LaravelQcloudContentAudit\Traits;

trait HasDry
{
    protected static bool $dry = false;

    public static function dry(bool $dry = null): bool
    {
        if (! is_null($dry)) {
            self::$dry = $dry;
        }

        return self::$dry;
    }
}
