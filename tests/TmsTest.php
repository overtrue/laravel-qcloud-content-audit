<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Mockery\MockInterface;
use Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidTextException;
use Overtrue\LaravelQcloudContentAudit\Tms;
use TencentCloud\Tms\V20201229\Models\TextModerationRequest;
use TencentCloud\Tms\V20201229\Models\TextModerationResponse;

class TmsTest extends TestCase
{
    use InteractsWithContainer;

    public function test_is_can_check_string_contents()
    {
        $response = new TextModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Pass',
            ]
        );
        $this->instance(
            'tms-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('TextModeration')->with(
                        \Mockery::on(
                            function (TextModerationRequest $request) {
                                $this->assertSame(\base64_encode('文本内容'), $request->getContent());

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        $this->assertSame(
            [
                'Suggestion' => 'Pass',
            ],
            Tms::check('文本内容')
        );
    }

    public function test_it_can_validate_string_contents()
    {
        $response = new TextModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Review',
            ]
        );
        $this->instance(
            'tms-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('TextModeration')->with(
                        \Mockery::on(
                            function (TextModerationRequest $request) {
                                $this->assertSame(\base64_encode('敏感内容'), $request->getContent());

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        $this->expectException(InvalidTextException::class);

        Tms::validate('敏感内容');
    }

    public function test_it_can_validate_string_contents_with_custom_strategy()
    {
        $response = new TextModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Review',
            ]
        );
        $this->instance(
            'tms-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('TextModeration')->with(
                        \Mockery::on(
                            function (TextModerationRequest $request) {
                                $this->assertSame(\base64_encode('敏感内容'), $request->getContent());

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        Tms::setStrategy('review', fn ($result) => $result['Suggestion'] === 'Review');

        $this->assertTrue(Tms::validate('敏感内容', 'review'));
    }
}
