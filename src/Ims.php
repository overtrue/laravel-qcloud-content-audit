<?php

namespace Overtrue\LaravelQcloudContentAudit;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;

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
                'Suggestion' => 'Block',
                'FileMD5' => '',
                'Label' => 'Porn',
                'SubLabel' => 'SexBehavior',
                'Score' => 90,
                'LabelResults' => [
                    [
                        'Scene' => 'Porn',
                        'Suggestion' => 'Block',
                        'Label' => 'Porn',
                        'SubLabel' => 'SexBehavior',
                        'Score' => 90,
                        'Details' => [],
                    ],
                ],
                'ObjectResults' => [
                    [
                        'Scene' => 'QrCode',
                        'Suggestion' => 'Block',
                        'Label' => 'Ad',
                        'SubLabel' => '',
                        'Score' => 100,
                        'Names' => [
                            'QRCODE',
                        ],
                        'Details' => [
                            [
                                'Id' => 0,
                                'Name' => 'QRCODE',
                                'Score' => 100,
                                'Location' => [
                                    'X' => 155.01746,
                                    'Y' => 396.01746,
                                    'Width' => 769.9824,
                                    'Height' => 769.98254,
                                    'Rotate' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
                'OcrResults' => [],
                'LibResults' => [],
                'Extra' => '',
            ],
        ]);
    }
}
