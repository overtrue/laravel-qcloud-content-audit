<?php

namespace Overtrue\LaravelQcloudContentAudit\Traits;

use Overtrue\LaravelQcloudContentAudit\Exceptions\StrategyNotFoundException;

trait HasStrategies
{
    protected array $strategies = [];

    /**
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\StrategyNotFoundException
     */
    public function satisfiesStrategy(array $result, string $strategy): bool
    {
        return (bool) $this->getStrategy($strategy)($result);
    }

    public function setStrategy(string $name, callable $callback): self
    {
        $this->strategies[$name] = $callback;

        return $this;
    }

    /**
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\StrategyNotFoundException
     */
    public function getStrategy(string $name): callable
    {
        if (! $this->hasStrategy($name)) {
            throw new StrategyNotFoundException($name);
        }

        return $this->strategies[$name];
    }

    public function hasStrategy(string $name): bool
    {
        return \array_key_exists($name, $this->strategies);
    }
}
