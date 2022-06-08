<?php

namespace Tests\Rules;

use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Overtrue\LaravelQcloudContentAudit\Rules\Tms;
use Tests\TestCase;

class TmsTest extends TestCase
{
    use InteractsWithContainer;

    public function test_it_can_check_input_image_files()
    {
        $rule = new Tms();

        \Overtrue\LaravelQcloudContentAudit\Tms::shouldReceive('validate')
            ->with('敏感内容', \Overtrue\LaravelQcloudContentAudit\Moderators\Tms::DEFAULT_STRATEGY)
            ->andReturn(true);

        $this->assertTrue($rule->passes('name', '敏感内容'));
    }
}
