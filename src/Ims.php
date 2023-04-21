<?php

namespace Overtrue\LaravelQcloudContentAudit;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Overtrue\LaravelQcloudContentAudit\Traits\HasDry;

/**
 * @method static array check(string $contents)
 * @method static bool validate(string $contents, string $strategy = 'strict')
 * @method static \Overtrue\LaravelQcloudContentAudit\Moderators\Ims setStrategy(string $name, callable $fn)
 */
class Ims extends Facade
{
    use HasDry;

    protected static function getFacadeAccessor(): string
    {
        return 'ims';
    }

    public static function fake(array $methods = ['check', 'validate']): void
    {
        foreach ($methods as $method) {
            call_user_func([self::class, \sprintf('fake%s', Str::ucfirst($method))]);
        }
    }

    public static function fakeValidate(): void
    {
        self::shouldReceive('validate')->withAnyArgs()->andReturnTrue();
    }

    public static function fakeCheck(): void
    {
        self::shouldReceive('check')->withAnyArgs()->andReturn([
            'Response' => [
                'RequestId' => 'a61237dd-c2a0-43e7-a3da-d27022d39ba7',
                'DataId' => 'a61237dd-c2a0-43e7-a3da-d27022d39ba7',
                'BizType' => 'test_1001',
                'FileMD5' => '',
                'Label' => 'Normal',
                'Suggestion' => 'Pass',
                'Keywords' => [],
                'Score' => 0,
                'LabelResults' => [],
                'ObjectResults' => [],
                'OcrResults' => [],
                'LibResults' => [],
                'Extra' => '',
            ],
        ]);
    }
}
