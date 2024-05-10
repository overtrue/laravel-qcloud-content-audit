<?php

namespace Overtrue\LaravelQcloudContentAudit;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Overtrue\LaravelQcloudContentAudit\Traits\HasDry;

use function sprintf;

/**
 * @method static array check(string $contents)
 * @method static bool validate(string $contents, string $strategy = 'strict')
 * @method static string|array mask(string|array $contents, string $strategy = 'strict', string $char = '*')
 * @method static Moderators\Tms setStrategy(string $name, callable $fn)
 */
class Tms extends Facade
{
    use HasDry;

    protected static function getFacadeAccessor(): string
    {
        return Moderators\Tms::class;
    }

    public static function fake(array $methods = ['check', 'validate', 'mask']): void
    {
        foreach ($methods as $method) {
            call_user_func([self::class, sprintf('fake%s', Str::ucfirst($method))]);
        }
    }

    public static function fakeMask(): void
    {
        self::shouldReceive('mask')->withAnyArgs()->andReturnArg(0);
    }

    public static function fakeValidate(): void
    {
        self::shouldReceive('validate')->withAnyArgs()->andReturnTrue();
    }

    public static function fakeCheck(): void
    {
        self::shouldReceive('check')->withAnyArgs()->andReturn([
            'BizType' => '0',
            'Label' => 'Normal',
            'Suggestion' => 'Pass',
            'Keywords' => [],
            'Score' => 0,
            'DetailResults' => [
                [
                    'Label' => 'Porn',
                    'Suggestion' => 'Pass',
                    'Score' => 0,
                    'LibType' => 0,
                    'LibId' => '',
                    'LibName' => '',
                    'SubLabel' => '',
                ],
                [
                    'Label' => 'Abuse',
                    'Suggestion' => 'Pass',
                    'Score' => 0,
                    'LibType' => 0,
                    'LibId' => '',
                    'LibName' => '',
                    'SubLabel' => '',
                ],
                [
                    'Label' => 'Ad',
                    'Suggestion' => 'Pass',
                    'Score' => 0,
                    'LibType' => 0,
                    'LibId' => '',
                    'LibName' => '',
                    'SubLabel' => '',
                ],
            ],
            'DataId' => '',
            'SubLabel' => '',
            'RequestId' => '230a8a5b-6d35-4d2d-8650-f8067725e8ca',
        ]);
    }
}
