<?php

namespace Tests\Rules;

use Illuminate\Http\UploadedFile;
use Overtrue\LaravelQcloudContentAudit\Rules\Ims;
use Tests\TestCase;

class ImsTest extends TestCase
{
    public function test_it_can_check_input_image_files()
    {
        $rule = new Ims();

        \Overtrue\LaravelQcloudContentAudit\Ims::shouldReceive('validate')->andReturnTrue();

        $this->assertTrue(
            $rule->passes('logo', UploadedFile::fake()->createWithContent('logo.png', \file_get_contents(__DIR__ . '/../images/500x500.png')))
        );
    }
}
