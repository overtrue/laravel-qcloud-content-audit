<?php

namespace Tests\Traits;

use Overtrue\LaravelQcloudContentAudit\Exceptions\StrategyNotFoundException;
use Overtrue\LaravelQcloudContentAudit\Traits\HasStrategies;
use PHPUnit\Framework\TestCase;

class HasStrategiesTest extends TestCase
{
    public function test_has_strategies()
    {
        /* @var HasStrategies $target */
        $target = \Mockery::mock(HasStrategies::class)->makePartial();

        $this->assertFalse($target->hasStrategy('strict'));

        $target->setStrategy('strict', fn ($result) => $result['Suggestion'] === 'Pass');

        $this->assertTrue($target->hasStrategy('strict'));

        $this->assertIsCallable($target->getStrategy('strict'));

        $this->expectException(StrategyNotFoundException::class);
        $target->getStrategy('foo');
    }
}
