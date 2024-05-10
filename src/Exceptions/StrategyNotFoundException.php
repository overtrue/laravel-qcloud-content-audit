<?php

namespace Overtrue\LaravelQcloudContentAudit\Exceptions;

use Throwable;

class StrategyNotFoundException extends Exception
{
    public string $strategy;

    public function __construct(string $strategy, ?Throwable $previous = null)
    {
        $this->strategy = $strategy;

        parent::__construct(\sprintf('Strategy "%s" not found', $strategy), 404, $previous);
    }
}
